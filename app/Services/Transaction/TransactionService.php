<?php

namespace App\Services\Transaction;

use App\Events\MarketStatsUpdated;
use App\Events\MarketTradePrivateUpdated;
use App\Events\MarketTradeUpdated;
use App\Events\WalletUpdated;
use App\Jobs\Order\ProcessStopLimitOrdersJob;
use App\Models\Order\Order;
use App\Models\Transaction\Transaction;
use App\Repositories\Transaction\ReferralTransactionRepository;
use App\Services\Market\MarketService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Setting;

class TransactionService {

    protected $order, $cursorOrder, $cursorRemaining, $process_id, $initialQuantity, $filledQuantity, $cursorQuantity, $triggeredField, $isBuyMarket, $isOrderPriceGreater;

    protected $fee = 0;
    protected $cursorFee = 0;

    public $walletService;

    public function __construct()
    {
        $this->walletService = new WalletService();
    }

    /**
     * Insert order transactions.
     *
     * @param $transaction
     * @return void
     */
    public function process($transaction)
    {
        $this->process_id = $transaction['process_id'];
        $this->order = $transaction['order'];
        $this->cursorOrder = $transaction['matched_order'];

        $this->filledQuantity = $transaction['filled_quantity'];
        $this->cursorQuantity = $transaction['cursor_quantity'];
        $this->triggeredField = $transaction['triggeredField'];
        $this->initialQuantity = $transaction['initialQuantity'];
        $this->isOrderPriceGreater = $transaction['is_order_price_greater'];
        $this->cursorRemaining = $transaction['cursor_remaining'];
        $this->isBuyMarket = order_is_buy_market($this->order->type, $this->order->side);

        $filledConvertedQuantity = math_multiply($this->filledQuantity, $this->cursorOrder->price);

        $walletQuote = $this->cursorOrder->id ? $this->cursorOrder->walletQuote : null;
        $walletBase = $this->cursorOrder->id ? $this->cursorOrder->walletBase  : null;

        if(order_is_buy($this->order->side)) {

            $quoteQuantity = $this->isBuyMarket ? $this->filledQuantity : $filledConvertedQuantity;
            $baseQuantity = $this->isBuyMarket ? $this->cursorQuantity : $this->filledQuantity;

            // Take a fee from two matched orders
            $this->fee = math_percentage($quoteQuantity, $this->order->fee_rate);

            if($this->cursorOrder->id) {
                $this->cursorFee = math_percentage($quoteQuantity, $this->cursorOrder->fee_rate);
            }

            // Reflect order user wallet
            $this->reflectBalances(
                $this->order->walletQuote, math_sum($quoteQuantity, $this->fee),
                $walletQuote, math_sub($quoteQuantity, $this->cursorFee)
            );

            // Reflect matched order user wallet
            $this->reflectBalances(
                $walletBase, $baseQuantity,
                $this->order->walletBase, $baseQuantity
            );

        } else {

            // Take a fee from two matched orders
            $this->fee = math_percentage($filledConvertedQuantity, $this->order->fee_rate);
            $this->cursorFee = math_percentage($filledConvertedQuantity, $this->cursorOrder->fee_rate);

            // Reflect order user wallet
            $this->reflectBalances(
                $walletQuote, math_sum($filledConvertedQuantity, $this->cursorFee),
                $this->order->walletQuote, math_sub($filledConvertedQuantity, $this->fee)
            );

            // Reflect matched order user wallet
            $this->reflectBalances(
                $this->order->walletBase, $this->filledQuantity,
                $walletBase, $this->filledQuantity
            );
        }

        /*
         * Referral Detect and Calculate for order
         */
        $referral = $this->order->user->referral_id;
        if($referral) {
            $referralFee = math_percentage($this->fee, Setting::get('trade.referral_fee', INITIAL_REFERRAL_FEE));
        } else {
            $referralFee = 0;
        }

        /*
         * Referral Detect and Calculate for cursor order
         */
        $referralCursorFee = 0;

        if($this->cursorOrder->id && $this->cursorOrder->user->referral_id) {
            $referralCursorFee = math_percentage($this->cursorFee, Setting::get('trade.referral_fee', INITIAL_REFERRAL_FEE));
        }

        // Order Transaction
        $transactions = [
            'is_maker' => false,
            'process_id' => $this->process_id,
            'order_id' => $this->order->id,
            'user_id' => $this->order->user_id,
            'market_id' => $this->order->market->id,
            'order_type' => $this->order->type,
            'order_side' => $this->order->side,
            'fee' => $this->fee,
            'referral_fee' => $referralFee,
            'price' => $this->cursorOrder->price,
            'base_currency' => $this->cursorQuantity,
            'quote_currency' => math_multiply($this->cursorQuantity, $this->cursorOrder->price),
        ];

        // Cursor order transaction
        $cursorTransactions = [
            'is_maker' => true,
            'process_id' => $this->process_id,
            'order_id' => $this->cursorOrder->id ?? null,
            'user_id' => $this->cursorOrder->user_id ?? null,
            'market_id' => $this->order->market->id,
            'order_type' => $this->cursorOrder->type,
            'order_side' => $this->cursorOrder->side,
            'fee' => $this->cursorFee,
            'referral_fee' => $referralCursorFee,
            'price' => $this->cursorOrder->price,
            'base_currency' => $this->cursorQuantity,
            'quote_currency' => math_multiply($this->cursorQuantity, $this->cursorOrder->price),
        ];

        $transaction = (new Transaction)->create($transactions);
        $cursorTransaction = (new Transaction)->create($cursorTransactions);

        // Add referral transactions
        $referralTransactionRepository = new ReferralTransactionRepository();

        if($referral) {
            $referralTransactionRepository->store([
                'user_id' => $referral,
                'transaction_id' => $transaction->id,
                'currency_id' => $this->order->quote_currency_id,
                'amount' => $referralFee,
            ]);
        }

        // Add referral transactions for cursor order
        if($this->cursorOrder->id && $this->cursorOrder->user->referral_id) {
            $referralTransactionRepository->store([
                'user_id' => $this->cursorOrder->user->referral_id,
                'transaction_id' => $cursorTransaction->id,
                'currency_id' => $this->cursorOrder->quote_currency_id,
                'amount' => $referralCursorFee,
            ]);
        }

        // Revert pending amount if buyer entered bigger rate than seller's sell rate
        // In this case the system allow to buy from seller's rate and revert pending amount to buyer's wallet

        if($this->isOrderPriceGreater) {
            $baseQuantity = $this->isBuyMarket ? $this->cursorQuantity : $this->filledQuantity;

            $actualDeductedAmount = math_multiply($baseQuantity, $this->order->price);
            $shouldDeductedAmount = math_multiply($baseQuantity, $this->cursorOrder->price);

            $shouldDeductedRevert = math_sub($actualDeductedAmount, $shouldDeductedAmount);

            if(!$this->isBuyMarket) {
                $revertedFee = math_percentage($shouldDeductedRevert, $this->order->fee_rate);
                $shouldDeductedRevert = math_sum($shouldDeductedRevert, $revertedFee);
            }

            $this->walletService->revert($this->order->walletQuote, $shouldDeductedRevert);
        }

        // Set filled if quantity is zero
        if(($this->isBuyMarket && $this->order->quote_quantity == 0) || (!$this->isBuyMarket && $this->order->quantity == 0)) {
            $this->order->removeFromQueue(ORDER_STATUS_FILLED);
        }

        // Set filled cursor order if quantity is zero
        if($this->cursorOrder->id && $this->cursorOrder->quantity == 0) {
            $this->cursorOrder->removeFromQueue(ORDER_STATUS_FILLED);
        } elseif(!$this->cursorOrder->id && $this->cursorOrder->quantity) {

            // Remove liquidity order from the market orderbook
            try {

                $liquiditySide = order_is_buy($this->cursorOrder->side) ? 'asks' : 'bids';

                $liquidity = Cache::get("markets_liquidity.{$this->order->market->name}.{$liquiditySide}");

                $key = $liquidity->search(function ($item) {
                    return $item['price'] == $this->cursorOrder->price;
                });

                if($key !== false) {
                    $liquidity->forget($key);
                    Cache::put("markets_liquidity.{$this->order->market->name}.{$liquiditySide}", $liquidity);
                }

            } catch (\Exception $e) {
                Log::error($e);
                Log::info('Trying to remove liquidity order from the queue');
                Log::info($this->cursorOrder);
            }
        }

        // Cache Market Values
        (new MarketService())->updateStats($this->order->market->id, $this->cursorOrder->price, $this->cursorQuantity, math_multiply($this->cursorQuantity, $this->cursorOrder->price));

        // Trigger stop limit orders if any
        ProcessStopLimitOrdersJob::dispatchSync($this->order->market->id);

        event(new MarketStatsUpdated($this->order->market));

        if($this->order->user) {
            event(new MarketTradeUpdated($transaction, false));
            event(new MarketTradePrivateUpdated($transaction, false));
        }

        if(isset($this->cursorOrder->user)) {
            event(new MarketTradeUpdated($cursorTransaction, true));
            event(new MarketTradePrivateUpdated($cursorTransaction, false));
        }

        // Call wallet updated event
        event(new WalletUpdated($this->order->walletBase));
        event(new WalletUpdated($this->order->walletQuote));

        // Call wallet updated event
        if($this->cursorOrder->id) {
            event(new WalletUpdated($this->cursorOrder->walletBase));
            event(new WalletUpdated($this->cursorOrder->walletQuote));
        }
    }

    public function reflectBalances($wallet, $quantity, $walletQuote, $quoteQuantity) {

        if($wallet) {
            $this->walletService->decrease($wallet, $quantity);
        }

        if($walletQuote) {
            $this->walletService->increase($walletQuote, $quoteQuantity);
        }
    }
}
