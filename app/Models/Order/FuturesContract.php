<?php

namespace App\Models\Order;

use App\Casts\CryptoCurrencyDecimalCast;
use App\Models\Order\Traits\Relations\FuturesContractRelation;
use App\Models\Order\Traits\Scopes\FuturesContractScope;
use Illuminate\Database\Eloquent\Model;

class FuturesContract extends Model
{
    use FuturesContractRelation, FuturesContractScope;

    protected $table = 'futures_contract';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $casts = [
        'quantity' => CryptoCurrencyDecimalCast::class,
        'price' => CryptoCurrencyDecimalCast::class,
        'liquidation_price' => CryptoCurrencyDecimalCast::class,
        'balance' => CryptoCurrencyDecimalCast::class,
        'released_amount' => CryptoCurrencyDecimalCast::class,
        'created_at' => "datetime:Y-m-d H:i:s",
        'updated_at' => "datetime:Y-m-d H:i:s",
    ];
}
