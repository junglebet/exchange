<?php

namespace App\Services\Order;

use App\Events\OrderBookUpdated;
use App\Events\WalletUpdated;
use App\Jobs\Market\MarketCapCalculationJob;
use App\Jobs\Order\CreateOrderJob;
use App\Models\Order\Order;
use App\Repositories\Order\OrderRepository;
use App\Services\Liquidity\Binance\BinanceApi;
use App\Services\Transaction\TransactionService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Setting;

class OrderService {

    // fields properties
    protected $process_id, $market, $order, $cursorOrder, $cursorQuantity, $cursorFill, $cursorRemaining, $totalRemaining, $quickFill, $fee, $cursorFee, $stopMatching, $isBuyMarket, $initialQuantity;

    // service properties
    public $transactionService;
    public $walletService;

    // field arrays
    public $transactions = [];

    private $orderRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->walletService = new WalletService();
    }

    /**
     * Process match orders.
     *
     * @param $order
     * @return bool
     */
    public function processOrder(Order $order, $firstLoop = false, $withoutAdjust = false) {

        try {

            // order that needs to be executed
            $this->order = $order;

            // If order is buy market
            $this->isBuyMarket = order_is_buy_market($this->order->type, $this->order->side);

            // Triggered Field
            $triggeredField = $this->isBuyMarket ? 'quote_quantity' : 'quantity';

            // Initial amount
            $this->initialQuantity = $this->order->{$triggeredField};

            // define initial order quantity
            $this->cursorRemaining = $this->totalRemaining = $this->initialQuantity;

            // assign transactions service
            $this->transactionService = new TransactionService();

            // generate process id
            $this->process_id = Str::uuid();

            // Find initial order with best rate
            $this->prepareOrderCursor();

            // If order not found stop processing
            if(!$this->cursorOrder) {

                if(!order_is_limit($this->order->type)) {
                    $this->revertPending();
                } elseif($firstLoop && !$withoutAdjust) {
                    $this->adjustFee();
                }

                $this->stopMatching = true;

                return true;
            }

            // Calculate Market Cap
            MarketCapCalculationJob::dispatchSync($this->order->market);

            // Get current matched order quantity
            $this->cursorQuantity = $this->cursorOrder->quantity;

            // Check if the order will be filled immediately or partially
            if($this->isBuyMarket) {
                $this->quickFill = $this->cursorQuantity >= math_divide($this->cursorRemaining, $this->cursorOrder->price);
            } else {
                $this->quickFill = $this->cursorQuantity >= $this->cursorRemaining;
            }

            // Filled amount in quote currency
            if($this->isBuyMarket) {
                $cursorFill = $this->quickFill ? math_divide($this->cursorRemaining, $this->cursorOrder->price) : $this->cursorOrder->quantity;
            } else {
                $cursorFill = $this->quickFill ? $this->cursorRemaining : $this->cursorOrder->quantity;
            }

            $isOrderPriceGreater = false;

            // Check if order rate is bigger than matched order
            if($this->order->price > $this->cursorOrder->price) {
                $isOrderPriceGreater = true;
            }

            // Spent amount in base currency
            if($this->isBuyMarket) {
                $convertedFill = $this->quickFill ? $this->totalRemaining : math_multiply($this->cursorOrder->quantity, $this->cursorOrder->price);
            } else {
                $convertedFill = $this->quickFill ? $this->totalRemaining : $this->cursorOrder->quantity;
            }

            // Decrease filled amount
            $this->cursorRemaining = math_sub($this->cursorRemaining, $convertedFill);

            $this->totalRemaining = math_sub($this->totalRemaining, $convertedFill);

            // Decrease quantity of order
            $this->order->decrementField($triggeredField, $convertedFill);

            // Decrease fee
            if($this->isBuyMarket) {
                $fee = math_percentage($convertedFill, $this->order->fee_rate);
                $this->order->decrementField('fee', $fee);
            }

            $this->order->refresh();

            // Decrease filled quantity of matched order
            if($this->cursorOrder->id) {
                $this->cursorOrder->decrementField('quantity', $cursorFill);
                $this->cursorOrder->refresh();
            } else {
                $this->cursorOrder->quantity -= $cursorFill;
            }

            // Call orderbook updated event
            event(new OrderBookUpdated([
                'order' => $this->order->toArray(),
                'name' => $this->order->market->name,
                'decimals' => $this->order->market->quote_precision
            ], 'update', $convertedFill));

            // Call orderbook updated event
            if($this->cursorOrder->id) {
                event(new OrderBookUpdated([
                    'order' => $this->cursorOrder->toArray(),
                    'name' => $this->cursorOrder->market->name,
                    'decimals' => $this->cursorOrder->market->quote_precision
                ], 'update', $cursorFill));
            }

            // Process order transaction
            $this->transactionService->process([
                'process_id' => $this->process_id,
                'order' => $this->order,
                'matched_order' => $this->cursorOrder,
                'filled_quantity' => $convertedFill,
                'cursor_quantity' => $cursorFill,
                'triggeredField' => $triggeredField,
                'initialQuantity' => $this->initialQuantity,
                'is_order_price_greater' => $isOrderPriceGreater,
                'cursor_remaining' => $this->cursorRemaining
            ]);

        } catch (\Exception $e) {
            // Log error and rollback transaction
            Log::error($e);

            $this->revertPending();

            return false;
        }

        // Process until order is executable
        if($this->processMatching() && !$this->stopMatching) {
            $this->order->refresh();
            $this->processOrder($this->order, true);
        } elseif(!$this->processMatching() && $this->isBuyMarket) {
            $this->revertPendingFee();
        }
    }

    /**
     * Get the next found order by criteria.
     *
     * @return Order $cursorOrder
     */
    private function prepareOrderCursor() {
        $this->cursorOrder = $this->orderRepository->getMatchedOrder(
            $this->order->type,
            $this->order->side,
            $this->order->market->id,
            $this->order->price
        );
    }

    /**
     * Check if order process is executable
     *
     * @return boolean
     */
    private function processMatching() {
        return $this->cursorRemaining > 0;
    }

    /**
     * Revert pending order balance to the wallet balance
     */
    private function revertPending() {

        $fee = 0;

        $revertedWallet = order_is_buy($this->order->side) ? $this->order->walletQuote : $this->order->walletBase;

        if(order_is_buy($this->order->side)) {
            $fee = $this->order->fee;
        }

        $this->walletService->revert($revertedWallet, $this->cursorRemaining, 'order', $fee);
        $this->order->delete();

        // Wallet Updated
        event(new WalletUpdated($revertedWallet));
    }

    /**
     * Revert pending fee for buy market order
     */
    private function revertPendingFee() {

        $wallet = $this->order->walletQuote;
        $fee = $this->order->fee;
        $this->walletService->revert($wallet, $fee, 'order');

        // Wallet Updated
        event(new WalletUpdated($wallet));
    }

    /**
     * @param $market_id
     */
    public function processStopLimitOrders($market_id) {

        // Get last price
        $last_price = market_get_stats($market_id, 'last');

        // Get matched stop limit orders
        $orders = $this->orderRepository->triggerStopLimitMatchedOrders($last_price);

        // Iterate Limit Orders
        foreach ($orders as $order) {

            // Update type to limit order
            $order->update([
                'type' => Order::TYPE_LIMIT
            ]);

            // Start matching orders
            CreateOrderJob::dispatchSync($order);

            // Call orderbook updated event
            event(new OrderBookUpdated([
                'order' => $order->toArray(),
                'name' => $order->market->name,
                'decimals' => $order->market->quote_precision
            ], 'store'));
        }
    }

    /*
     * Set maker fee instead of taker fee if user shares liquidity
     */
    public function adjustFee() {

        $fee_rate = Setting::get('trade.maker_fee', INITIAL_TRADE_MAKER_FEE);
        $this->order->fee_rate = $fee_rate;
        $this->order->update();

        if(!order_is_buy($this->order->side)) {
            return true;
        }

        $wallet = $this->order->walletQuote;

        $orderMakerFee = math_percentage(math_multiply($this->order->quantity, $this->order->price), $fee_rate);

        $feeDifference = math_sub($this->order->fee, $orderMakerFee);

        $this->order->fee = $feeDifference;
        $this->order->update();

        // Increase wallet balance
        $this->walletService->increase($wallet, $feeDifference, 'wallet');

        // Increase wallet pending balance
        $this->walletService->decrease($wallet, $feeDifference, 'order');

        // Wallet Updated
        event(new WalletUpdated($wallet));
    }
}
