<?php

namespace App\Models\Market;

use App\Casts\CryptoCurrencyDecimalCast;
use App\Models\Market\Traits\Relations\MarketRelation;
use App\Models\Market\Traits\Scopes\MarketScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    use HasFactory, SoftDeletes, MarketRelation, MarketScope;

    public $fillable = [
        'name',
        'base_currency_id',
        'quote_currency_id',
        'base_precision',
        'quote_precision',
        'min_trade_size',
        'max_trade_size',
        'min_trade_value',
        'max_trade_value',
        'base_ticker_size',
        'quote_ticker_size',
        'status',
        'switch_chart',
        'trade_status',
        'buy_order_status',
        'sell_order_status',
        'cancel_order_status',
        'is_tradingview',
        'custom_market_path',
        'last',
        'has_futures',
        'discount',
        'discount_bid'
    ];

    protected $casts = [
        'status' => 'boolean',
        'trade_status' => 'boolean',
        'buy_order_status' => 'boolean',
        'sell_order_status' => 'boolean',
        'has_futures' => 'boolean',
        'cancel_order_status' => 'boolean',
        'switch_chart' => 'boolean',
        'min_trade_size' => CryptoCurrencyDecimalCast::class,
        'max_trade_size' => CryptoCurrencyDecimalCast::class,
        'min_trade_value' => CryptoCurrencyDecimalCast::class,
        'max_trade_value' => CryptoCurrencyDecimalCast::class,
        'base_ticker_size' => CryptoCurrencyDecimalCast::class,
        'quote_ticker_size' => CryptoCurrencyDecimalCast::class,
        'bid' => CryptoCurrencyDecimalCast::class,
        'ask' => CryptoCurrencyDecimalCast::class,
        'last' => CryptoCurrencyDecimalCast::class,
        'high' => CryptoCurrencyDecimalCast::class,
        'low' => CryptoCurrencyDecimalCast::class,
        'volume' => CryptoCurrencyDecimalCast::class,
        'capitalization' => CryptoCurrencyDecimalCast::class,
        'change_amount' => CryptoCurrencyDecimalCast::class,
    ];
}
