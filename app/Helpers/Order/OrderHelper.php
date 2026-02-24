<?php

// Plain Order Helper Functions

use App\Models\Order\Order;

const INITIAL_TRADE_MAKER_FEE = 0.1;
const INITIAL_TRADE_TAKER_FEE = 0.25;

const INITIAL_REFERRAL_FEE = 10;

const ORDER_STATUS_ACTIVE = 'active';
const ORDER_STATUS_FILLED = 'filled';
const ORDER_STATUS_PARTIALLY_FILLED = 'partially_filled';
const ORDER_STATUS_CANCELLED = 'cancelled';

/*
 * Check if order is limit
 */
if (!function_exists('order_is_limit')) {
    function order_is_limit($type)
    {
        return $type === Order::TYPE_LIMIT;
    }
}

/*
 * Check if buy order
 */
if (!function_exists('order_is_buy')) {
    function order_is_buy($type)
    {
        return $type === Order::SIDE_BUY;
    }
}

/*
 * Check if order is market
 */
if (!function_exists('order_is_market')) {
    function order_is_market($type)
    {
        return $type === Order::TYPE_MARKET;
    }
}

/*
 * Check if order is buy market
 */
if (!function_exists('order_is_buy_market')) {
    function order_is_buy_market($type, $side)
    {
        return $type === Order::TYPE_MARKET && $side === Order::SIDE_BUY;
    }
}

/*
 * Check if order is sell market
 */
if (!function_exists('order_is_sell_market')) {
    function order_is_sell_market($type, $side)
    {
        return $type === Order::TYPE_MARKET && $side === Order::SIDE_SELL;
    }
}

/*
 * Check if order type is supported by exchange
 */
if (!function_exists('order_allowed_types')) {
    function order_allowed_types($type)
    {
        return $type === Order::TYPE_MARKET
            || $type === Order::TYPE_LIMIT
            || $type === Order::TYPE_STOP_LIMIT;
    }
}

/*
 * Check if order type is stop limit
 */
if (!function_exists('order_is_stop_limit')) {
    function order_is_stop_limit($type)
    {
        return $type === Order::TYPE_STOP_LIMIT;
    }
}

/*
 * Check if order type is stop limit
 */
if (!function_exists('order_limit_should_be_processed')) {
    function order_limit_should_be_processed($order, $market, $price, $condition)
    {
        $market_price = market_get_stats($market, 'last');

        $shouldBeProcessed = false;

        // Check if price drops condition
        if($condition == Order::STOP_LIMIT_CONDITION_DOWN && $market_price <= $price) {
            $shouldBeProcessed = true;
        }

        if($condition == Order::STOP_LIMIT_CONDITION_UP && $market_price >= $price) {
            $shouldBeProcessed = true;
        }

        if(!$shouldBeProcessed) return false;

        // Load order if only uuid provided
        if(is_string($order)) {
            $order = Order::find($order);
        }

        // Change stop limit type to limit
        $order->update([
           'type' => Order::TYPE_LIMIT
        ]);

        return true;
    }
}

/*
 * Futures Liquidation Price Calculation
 */
if (!function_exists('liquidation_price_calculate')) {
    function liquidation_price_calculate($price, $leverage, $isLong = true)
    {
        $maxLeveragePrice = math_multiply($price, $leverage);

        if($isLong) {
            $leverageRatio = math_sum($leverage, 1);
        } else {
            $leverageRatio = math_sub($leverage, 1);
        }

        $leverageMarginRate = math_multiply(0.005, $leverage);

        if($isLong) {
            $ratioFormula = math_sub($leverageRatio, $leverageMarginRate);
        } else {
            $ratioFormula = math_sum($leverageRatio, $leverageMarginRate);
        }

        return math_formatter(math_divide($maxLeveragePrice, $ratioFormula), 8);
    }
}

/*
 * Futures PNL Calculation
 */
if (!function_exists('futures_pnl_calculate')) {
    function futures_pnl_calculate($quantity, $entryPrice, $marketPrice, $leverage, $isLong)
    {
        if($marketPrice == 0 || !$marketPrice) return 0;

        if($isLong) {
            $PNL = math_multiply($quantity, (math_sub(math_divide(1, $entryPrice), math_divide(1, $marketPrice))));
        } else {
            $PNL = math_multiply($quantity, (math_sub(math_divide(1, $marketPrice), math_divide(1, $entryPrice))));
        }

        $margin = math_divide($quantity, (math_multiply($entryPrice, $leverage)));

        $percentange = math_formatter(math_multiply(math_divide($PNL, $margin), 100), 5);

        if($percentange < -100) {
            return -100;
        }

        return $percentange;
    }
}
