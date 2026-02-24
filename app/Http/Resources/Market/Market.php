<?php

namespace App\Http\Resources\Market;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Setting;

class Market extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $markets = Cache::get('markets_liquidity.active', []);

        $lastCachedPrice = market_get_stats($this->id, 'last');

        return [
            'name' => $this->name,
            's' => $markets[$this->name] ?? false,
            'sanitized_name' => market_sanitize($this->name),
            'base_currency' => $this->baseCurrency->symbol,
            'base_currency_name' => $this->baseCurrency->name,
            'base_currency_type' => $this->baseCurrency->type,
            'base_currency_logo' => url($this->baseCurrency->file->path),
            'quote_currency' => $this->quoteCurrency->symbol,
            'quote_currency_name' => $this->quoteCurrency->name,
            'quote_currency_type' => $this->quoteCurrency->type,
            'base_precision' => $this->base_precision,
            'quote_precision' => $this->quote_precision,
            'min_trade_size' => $this->min_trade_size,
            'max_trade_size' => $this->max_trade_size,
            'min_trade_value' => $this->min_trade_value,
            'max_trade_value' => $this->max_trade_value,
            'base_ticker_size' => $this->base_ticker_size,
            'quote_ticker_size' => $this->quote_ticker_size,
            'status' => $this->status,
            'ratio' => $this->discount,
            'ratio_b' => $this->discount_bid,
            'has_futures' => $this->has_futures,
            'trade_status' => $this->trade_status,
            'buy_order_status' => $this->buy_order_status,
            'sell_order_status' => $this->sell_order_status,
            'cancel_order_status' => $this->cancel_order_status,
            'chart_enabled' => $this->is_tradingview,
            'listed' => Carbon::parse($this->created_at)->unix(),
            'custom_market_path' => $this->custom_market_path,
            'last' => math_formatter(market_get_stats($this->id, 'last'), $this->quote_precision),
            'change' => !$lastCachedPrice ? 0.00 : math_percentage_between($lastCachedPrice, $this->last) ,
            'high' => math_formatter(market_get_stats($this->id, 'high'), $this->quote_precision),
            'low' => math_formatter(market_get_stats($this->id, 'low'), $this->quote_precision),
            'volume' => math_formatter(market_get_stats($this->id, 'volume'), $this->base_precision),
            "fee" => Setting::get('trade.taker_fee', INITIAL_TRADE_TAKER_FEE),
        ];
    }
}
