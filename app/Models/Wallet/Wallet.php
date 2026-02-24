<?php

namespace App\Models\Wallet;

use App\Casts\CryptoCurrencyDecimalCast;
use App\Models\Wallet\Traits\Relations\WalletRelation;
use App\Models\Wallet\Traits\Scopes\WalletScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory, WalletRelation, WalletScope, Compoships;

    protected $casts = [
        'balance_in_wallet' => CryptoCurrencyDecimalCast::class,
        'balance_in_order' => CryptoCurrencyDecimalCast::class,
        'balance_in_withdraw' => CryptoCurrencyDecimalCast::class,
    ];
}
