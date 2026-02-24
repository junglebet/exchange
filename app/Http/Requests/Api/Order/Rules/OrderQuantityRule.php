<?php

namespace App\Http\Requests\Api\Order\Rules;

use App\Repositories\Market\MarketRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Wallet\WalletRepository;
use Illuminate\Contracts\Validation\Rule;
use Auth;
use Illuminate\Support\Facades\Cache;
use Setting;

class OrderQuantityRule implements Rule
{
    /**
     * @var MarketRepository
     * @var WalletRepository
     * @var OrderRepository
     */
    private $marketRepository, $orderRepository, $walletRepository, $error, $errorExtra;

    public function __construct()
    {
        $this->marketRepository = new MarketRepository();
        $this->walletRepository = new WalletRepository();
        $this->orderRepository = new OrderRepository();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  numeric $quantity
     * @return bool
     */
    public function passes($attribute, $quantity)
    {
        // Request variables
        $market = request()->get('market');
        $type = request()->get('type');
        $side = request()->get('side');
        $price = request()->get('price');

        // Skip quantity checking rule if order is buy market order since it should check quote_quantity field instead
        if(order_is_buy_market($type, $side)) {
            return true;
        }

        if(!$quantity || !is_numeric($quantity) || (mb_strpos('e', (string)$quantity) !== false) || (mb_strpos('E', (string)$quantity) !== false) || math_compare($quantity, 0) < 1) {
            $this->error = 'Invalid order quantity';
            return false;
        }

        // User id
        $userModel = Auth::user();
        $user = $userModel->id;

        // Get order side
        $buySide = order_is_buy($side);

        // Get order type
        $isMarket = order_is_market($type);

        // Get market by name
        $market = $this->marketRepository->get($market);

        // Check if order quantity follows min trade size rule
        if($market->min_trade_size > 0 && math_compare($quantity, $market->min_trade_size) < 0) {
            $this->errorExtra = ' ' . $market->min_trade_size;
            $this->error = "Minimum allowed trade size is";
            return false;
        }

        // Check if order quantity follows max trade size rule
        if($market->max_trade_size > 0 && math_compare($market->max_trade_size, $quantity) < 0) {
            $this->errorExtra = ' ' . $market->max_trade_size;
            $this->error = "Maximum allowed trade size is";
            return false;
        }

        // Allowed decimal rule
        //if(!math_decimal_validation($quantity, $market->base_precision)) {
            //$this->error = "Invalid format of the amount";
            //return false;
        //}

        // Get currency side
        $currencySide = $buySide ? $market->quote_currency_id : $market->base_currency_id;

        // Invalidate if there is no order to fill quick order
        if($isMarket) {

            $matchedOrder = $this->orderRepository->getMatchedOrder($type, $side, $market->id, false);

            if(!$matchedOrder) {


                $liquidityType = $side == "buy" ? 'asks' : 'bids';
                $liquidity = Cache::get("markets_liquidity.{$market->name}.{$liquidityType}");

                if($liquidity && count($liquidity)) {

                    //


                } else {
                    $this->error = 'No Matched Order';
                    return false;
                }
            }
        }

        if($userModel->active) {
            return true;
        }

        // Get user wallet and its balance
        $wallet = $this->walletRepository->getWalletByCurrency($user, $currencySide);

        // Calculate total required quantity in balance
        if($buySide) {
            $buyTotalAmount = math_multiply($price, $quantity); // 27000
            $buyTotalFee = math_percentage($quantity, Setting::get('trade.taker_fee', INITIAL_TRADE_TAKER_FEE)); // 0.09
            $feeInAmount = math_multiply($buyTotalFee, $price);

            $totalRequiredAmount = math_sum($buyTotalAmount, $feeInAmount);
        } else {
            $totalRequiredAmount = $quantity;
        }

        if(!$wallet || math_sub($wallet->balance_in_wallet, $totalRequiredAmount) < 0) {

            if($buySide) {
                $this->error = 'Insufficient balance. Your balance must be greater or equal to Wallet Balance + Taker Fee';
            } else {
                $this->error = 'Insufficient balance.';
            }

            return true;
            //return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __($this->error) . $this->errorExtra;
    }
}
