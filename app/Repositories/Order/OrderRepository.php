<?php

namespace App\Repositories\Order;

use App\Events\OrderBookUpdated;
use App\Events\WalletUpdated;
use App\Interfaces\Order\OrderRepositoryInterface;
use App\Jobs\Market\MarketCapCalculationJob;
use App\Jobs\Order\CreateOrderJob;
use App\Models\Market\Market;
use App\Models\Order\FuturesContract;
use App\Models\Order\Order;
use App\Repositories\Market\MarketRepository;
use App\Repositories\Wallet\WalletRepository;
use App\Services\Order\OrderService;
use App\Services\Wallet\WalletService;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Setting;

class OrderRepository implements OrderRepositoryInterface
{
    public
        $order,
        $market,
        $trigger_price,
        $trigger_condition,
        $walletService,
        $walletRepository,
        $orderService,
        $marketRepository,
        $orderHistoryRepository,
        $user,
        $uuid,
        $type;

    public function __construct()
    {
        $this->walletService = new WalletService();

        $this->marketRepository = new MarketRepository();
        $this->walletRepository = new WalletRepository();

        $this->orderHistoryRepository = new OrderHistoryRepository();
    }

    public function get($market, $type, $depth = 1000) {

        $maxDepth = 1000;

        if($depth > $maxDepth) {
            $depth = $maxDepth;
        }

        $market = Market::whereName($market)->value('id');

        $orders = Order::groupPrice()->whereMarketId($market);

        if($type == Order::SIDE_BUY)
            $orders->buyLimit();
        else
            $orders->sellLimit();

        $orders->limit($depth);

        $orders->groupBy('price');

        return $orders->get();
    }

    public function findById($uuid) {
        return Order::find($uuid);
    }

    public function store($futures = false)
    {
        $isSwap = request()->get('swap', false);

        if($isSwap) {
            sleep(120);
        }

        // Set uuid
        $this->setUuid();

        // Set user
        $this->user = Auth::user();

        // Get market by name
        $this->market = $this->marketRepository->get(request()->get('market'));

        // Spot Market Orders
        try {

            return DB::transaction(function() {

                // Create order service instance
                $this->orderService = new OrderService();

                // Get order type
                $this->type = request()->get('type');

                // Get order side
                $side = request()->get('side');

                // Get order stop trigger price and condition // only for stop limit orders
                $this->trigger_price = request()->get('trigger_price', null);
                $this->trigger_condition = Order::STOP_LIMIT_CONDITION_DOWN; //request()->get('trigger_condition', null);

                if(!order_is_stop_limit($this->type)) {
                    $this->trigger_price = null;
                    $this->trigger_condition = null;
                }

                // Get order side
                $buySide = order_is_buy($side);

                // Is sell market order
                $isSellMarket = order_is_sell_market($this->type, $side);

                // Is buy market order
                $isBuyMarket = order_is_buy_market($this->type, $side);

                // Is market order
                $isMarket = order_is_market($this->type);

                // Get quantity
                $quantity = $isBuyMarket
                    ? 0
                    : request()->get('quantity');

                // Get quoteQuantity if order is buy market
                $quoteQuantity = $isBuyMarket
                    ? request()->get('quoteQuantity')
                    : 0;

                // Get price
                $price = $isBuyMarket || $isSellMarket
                    ? 0
                    : request()->get('price');

                $fee_rate = Setting::get('trade.taker_fee', INITIAL_TRADE_TAKER_FEE);

                $fee = 0;

                // Total quantity to decrease
                if($isBuyMarket) {
                    $finalQuantity = $quoteQuantity;
                } else {
                    if($buySide) {
                        $buyTotalAmount = math_multiply($quantity, $price);
                        $fee = math_percentage($buyTotalAmount, $fee_rate);

                        $finalQuantity = math_sum($buyTotalAmount, $fee);
                    } else {
                        $finalQuantity = $quantity;
                    }
                }

                // Get currency side
                $currencySide = $buySide
                    ? $this->market->quote_currency_id
                    : $this->market->base_currency_id;

                // Get user wallet
                $wallet = $this->walletRepository->getWalletByCurrency($this->user->id, $currencySide);

                // Decrease from balance
                $this->walletService->decrease($wallet, $finalQuantity, 'wallet');

                // Increase pending balance
                $this->walletService->increase($wallet, $finalQuantity, 'order');

                // Take fee from buy market order
                if($isBuyMarket) {
                    $fee = math_percentage($finalQuantity, $fee_rate);
                    $quoteQuantity = math_sub($quoteQuantity, $fee);
                }

                // Model data
                $insert = [
                    'id' => $this->uuid,
                    'user_id' => $this->user->id,
                    'market_id' => $this->market->id,
                    'type' => $this->type,
                    'side' => $side,
                    'initial_quantity' => $quantity,
                    'quantity' => $quantity,
                    'initial_quote_quantity' => $quoteQuantity,
                    'quote_quantity' => $quoteQuantity,
                    'price' => $price,
                    'fee' => $fee,
                    'fee_rate' => $fee_rate,
                    'base_currency_id' => $this->market->base_currency_id,
                    'quote_currency_id' => $this->market->quote_currency_id,
                    'trigger_price' => $this->trigger_price,
                    'trigger_condition' => $this->trigger_condition,
                    'created_at' => Carbon::now()
                ];

                // Store order
                $this->order = $this->insert($insert);

                // Copy to order history
                $this->orderHistoryRepository->insert($insert);

                // Call wallet updated event
                event(new WalletUpdated($wallet));

                $orderShouldDispatched = true;

                // Check stop limit triggers and process if it matches criteria
                if(order_is_stop_limit($this->type) && !order_limit_should_be_processed($this->order, $this->market->id, $this->trigger_price, $this->trigger_condition)) {
                    $orderShouldDispatched = false;
                }

                // Start matching order in queue
                if($orderShouldDispatched) {

                    // Call orderbook updated
                    if(!order_is_market($this->type)) {
                        event(new OrderBookUpdated([
                            'order' => $this->order->toArray(),
                            'name' => $this->order->market->name,
                            'decimals' => $this->order->market->quote_precision
                        ], 'store'));
                    }
                    Log::info($this->order);
                    (new OrderService())->processOrder($this->order, true, false, 1);
                }

                return $this->uuid;

            }, DB_REPEAT_AFTER_DEADLOCK);

        } catch (\Exception $e) {
            // Log error and rollback transaction
            Log::error($e);

            return false;
        }
    }

    public function cancel()
    {
        DB::beginTransaction();

        try {

            // Get order
            $order = $this->findById(request()->get('uuid'));

            $market = $order->market;

            // Set user
            $this->user = $order->user;

            // Get order side
            $buySide = order_is_buy($order->side);

            // Get limit side
            $limitSide = order_is_limit($order->type);

            // Get stop limit side
            $stopLimitSide = order_is_stop_limit($order->type);

            // Get quantity
            $quantity = $order->quantity;

            // Get price
            $price = $order->price;

            // Get currency side
            $currencySide = $buySide ? $order->market->quote_currency_id : $order->market->base_currency_id;

            // Get user wallet and reflect balance
            $wallet = $this->walletRepository->getWalletByCurrency($this->user->id, $currencySide);

            // Get total quantity if order type is limit
            if(($limitSide || $stopLimitSide) && $buySide) {

                $quantity = math_multiply($quantity, $price);

                /*
                 * Revert amount with fee
                 */
                $quantityFee = math_percentage($quantity, $order->fee_rate);
                $quantity = math_sum($quantity, $quantityFee);
            }

            // Decrease pending balance
            $this->walletService->decrease($wallet, $quantity, 'order');

            // Increase from balance
            $this->walletService->increase($wallet, $quantity, 'wallet');

            // Call orderbook updated
            event(new OrderBookUpdated([
                'order' => [
                    'id' => $order->id,
                    'side' => $order->side,
                    'price' => $order->price,
                    'quantity' => $order->quantity,
                    'created_at' => '',
                ],
                'name' => $market->name,
                'decimals' => $market->quote_precision
            ], 'cancel'));

            // Wallet Updated
            event(new WalletUpdated($wallet));

            // Calculate Market Cap
            MarketCapCalculationJob::dispatchSync($order->market);

            // Delete order
            $order->removeFromQueue(ORDER_STATUS_CANCELLED);

            // Commit transaction
            DB::commit();

            return true;

        } catch (\Exception $e) {
            // Log error and rollback transaction
            Log::error($e);
            DB::rollback();

            return false;
        }
    }

    public function cancelFutures()
    {
        DB::beginTransaction();

        try {

            // Get order
            $order = FuturesContract::where('id', request()->get('uuid'))->with(['market', 'user'])->first();

            // Set user
            $this->user = $order->user;

            // Get quantity
            $quantity = $order->balance;

            // Get currency side
            $currencySide = $order->market->quote_currency_id;

            // Get user wallet and reflect balance
            $wallet = $this->walletRepository->getWalletByCurrency($this->user->id, $currencySide);

            $marketPrice = math_formatter(market_get_stats($order->market_id, 'last'), $order->market->quote_precision);

            $pnl = futures_pnl_calculate($order->quantity, $order->price, $marketPrice, $order->leverage, $order->is_long);

            if($pnl <= -100) {
                throw new \Exception("PNL is equal to -100 or more so position already should be liquidated");
            }

            $pnlAmount = abs(math_percentage($order->balance, $pnl));

            if($pnl >= 0) {
                $releasedAmount = math_sum($order->balance, $pnlAmount);
            } else {
                $releasedAmount = math_sub($order->balance, $pnlAmount);
            }

            $this->walletService->increase($wallet, $releasedAmount, 'wallet');

            // Wallet Updated
            event(new WalletUpdated($wallet));

            $order->released_amount = $releasedAmount;
            $order->status = "closed";
            $order->pnl = $pnl;
            $order->save();

            // Commit transaction
            DB::commit();

            return true;

        } catch (\Exception $e) {
            // Log error and rollback transaction
            Log::error($e);
            DB::rollback();

            return false;
        }
    }

    public function insert($insert) {

        Order::insert($insert);

        return $this->findById($this->uuid);
    }

    public function getMatchedOrder($type, $side, $market, $price) {
        return Order::matchOpposite($type, $side, $price)->whereMarketId($market)->processable()->oldest()->lockForUpdate()->first();
    }

    public function lockOrder(Order $order) {
        $order->locked = true;
        $order->update();
    }

    public function unlockOrder(Order $order) {
        $order->locked = true;
        $order->update();
    }

    public function open($market = false) {

        $orders = Order::processable()->where('user_id', auth()->id());

        if($market) {
            $market = Market::whereName($market)->value('id');
            $orders->whereMarketId($market);
        }

        $orders->has('market');

        return $orders->get();
    }

    public function openFutures($market = false) {

        $orders = FuturesContract::where('user_id', auth()->id());

        if($market) {
            $market = Market::whereName($market)->value('id');
            $orders->whereMarketId($market);
        }

        $orders->active();

        $orders->has('market');

        return $orders->get();
    }

    public function setUuid() {
        // Set order uuid
        $this->uuid = generate_uuid();
    }

    public function triggerStopLimitMatchedOrders($price) {

        $orders = Order::stopLimit()->oldest()->matchByStopLimitCondition($price)->lockForUpdate();

        return $orders->cursor();
    }
}
