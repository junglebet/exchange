<?php

namespace App\Models\Withdrawal;

use App\Casts\CryptoCurrencyDecimalCast;
use App\Models\Withdrawal\Traits\Relations\WithdrawalRelation;
use App\Models\Withdrawal\Traits\Scopes\WithdrawalScope;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use WithdrawalRelation, WithdrawalScope;

    protected $hidden = [
        'initial_raw'
    ];

    public $fillable = [
        'withdrawal_id',
        'txn',
        'source_id',
        'currency_id',
        'type',
        'network_id',
        'amount',
        'fee',
        'address',
        'payment_id',
        'user_id',
        'confirms',
        'status',
        'extra_status',
        'rejected_reason',
        'initial_raw',
        'raw',
        'inusd',
        'internal_id'
    ];

    public $appends = [
        'txn_link'
    ];

    protected $casts = [
        'amount' => CryptoCurrencyDecimalCast::class,
        'fee' => CryptoCurrencyDecimalCast::class,
        'created_at' => "datetime:Y-m-d H:i:s",
    ];

    public function getTxnLinkAttribute()
    {
        if (!$this->txn) return null;

        if($this->network_id == NETWORK_ERC) {
            return 'https://etherscan.io/tx/' . $this->txn;
        } elseif($this->network_id == NETWORK_BEP) {
            return 'https://bscscan.com/tx/' . $this->txn;
        } elseif($this->network_id == NETWORK_TRC) {
            return 'https://tronscan.org/#/transaction/' . $this->txn;
        }

        return str_replace('%txid%', $this->txn, $this->currency->txn_explorer);
    }
}
