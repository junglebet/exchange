<?php

namespace App\Models\Currency;

use App\Casts\CryptoCurrencyDecimalCast;
use App\Casts\PercentageDecimalCast;
use App\Models\Currency\Traits\Relations\CurrencyRelation;
use App\Models\Currency\Traits\Scopes\CurrencyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    public $table = 'currencies';

    use HasFactory, SoftDeletes, CurrencyRelation, CurrencyScope;

    public $fillable = [
        'name',
        'symbol',
        'alt_symbol',
        'type',
        'decimals',
        'status',
        'bank_account',
        'deposit_status',
        'withdraw_status',
        'file_id',

        //fee
        'deposit_fee',
        'deposit_fee_fixed',

        'deposit_fee_bep_fixed',
        'deposit_fee_erc_fixed',
        'deposit_fee_trc_fixed',
        'deposit_fee_matic_fixed',

        'deposit_fee_bep',
        'deposit_fee_erc',
        'deposit_fee_trc',
        'deposit_fee_matic',

        'withdraw_fee',
        'withdraw_fee_fixed',

        'withdraw_fee_bep_fixed',
        'withdraw_fee_erc_fixed',
        'withdraw_fee_trc_fixed',
        'withdraw_fee_matic_fixed',

        'withdraw_fee_bep',
        'withdraw_fee_erc',
        'withdraw_fee_trc',
        'withdraw_fee_matic',

        'min_deposit',
        'max_deposit',
        'min_withdraw',
        'max_withdraw',
        'min_deposit_confirmation',
        'contract',
        'bep_contract',
        'trc_contract',
        'matic_contract',
        'coinpayments_description',
        'bank_status',
        'cc_status',
        'cc_exchange_rate',
        'has_payment_id',
        'txn_explorer',
        'cold_min_balance_amount',
        'cold_transfer_amount',
        'cold_storage_id',
        'enable_cold',
        'inusd'
    ];

    protected $casts = [
        'status' => 'boolean',
        'deposit_status' => 'boolean',
        'withdraw_status' => 'boolean',
        'bank_status' => 'boolean',
        'cc_status' => 'boolean',
        'enable_cold' => 'boolean',

        'deposit_fee'=> PercentageDecimalCast::class,
        'deposit_fee_fixed'=> PercentageDecimalCast::class,

        'deposit_fee_bep'=> CryptoCurrencyDecimalCast::class,
        'deposit_fee_erc'=> CryptoCurrencyDecimalCast::class,
        'deposit_fee_trc'=> CryptoCurrencyDecimalCast::class,
        'deposit_fee_matic'=> CryptoCurrencyDecimalCast::class,

        'deposit_fee_bep_fixed'=> CryptoCurrencyDecimalCast::class,
        'deposit_fee_erc_fixed'=> CryptoCurrencyDecimalCast::class,
        'deposit_fee_trc_fixed'=> CryptoCurrencyDecimalCast::class,
        'deposit_fee_matic_fixed'=> CryptoCurrencyDecimalCast::class,

        'withdraw_fee'=> CryptoCurrencyDecimalCast::class,
        'withdraw_fee_bep'=> CryptoCurrencyDecimalCast::class,
        'withdraw_fee_erc'=> CryptoCurrencyDecimalCast::class,
        'withdraw_fee_trc'=> CryptoCurrencyDecimalCast::class,
        'withdraw_fee_matic'=> CryptoCurrencyDecimalCast::class,

        'withdraw_fee_fixed'=> CryptoCurrencyDecimalCast::class,
        'withdraw_fee_bep_fixed'=> CryptoCurrencyDecimalCast::class,
        'withdraw_fee_erc_fixed'=> CryptoCurrencyDecimalCast::class,
        'withdraw_fee_trc_fixed'=> CryptoCurrencyDecimalCast::class,
        'withdraw_fee_matic_fixed'=> CryptoCurrencyDecimalCast::class,

        'min_deposit'=> CryptoCurrencyDecimalCast::class,
        'min_withdraw'=> CryptoCurrencyDecimalCast::class,
        'max_withdraw'=> CryptoCurrencyDecimalCast::class,
        'wallet_balance' => CryptoCurrencyDecimalCast::class,
        'wallet_balance_erc' => CryptoCurrencyDecimalCast::class,
        'wallet_balance_trc' => CryptoCurrencyDecimalCast::class,
        'wallet_balance_bep' => CryptoCurrencyDecimalCast::class,
        'wallet_balance_matic' => CryptoCurrencyDecimalCast::class,
        'locked_balance' => CryptoCurrencyDecimalCast::class,
    ];
}
