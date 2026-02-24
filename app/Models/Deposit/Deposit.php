<?php

namespace App\Models\Deposit;

use App\Casts\CryptoCurrencyDecimalCast;
use App\Models\Deposit\Traits\Relations\DepositRelation;
use App\Models\Deposit\Traits\Scopes\DepositScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use DepositRelation, DepositScope;

    protected $hidden = [
        'initial_raw'
    ];

    public $fillable = [
        'deposit_id',
        'txn',
        'source_id',
        'currency_id',
        'type',
        'network_id',
        'amount',
        'full_amount',
        'network_fee',
        'system_fee',
        'address',
        'user_id',
        'confirms',
        'status',
        'initial_raw',
        'raw',
        'wallet_transfer_status',
        'internal_id'
    ];

    public $appends = [
        'txn_link',
        'wallet_transfer_ago'
    ];

    protected $casts = [
        'amount' => CryptoCurrencyDecimalCast::class,
        'network_fee' => CryptoCurrencyDecimalCast::class,
        'system_fee' => CryptoCurrencyDecimalCast::class,
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

    public function getWalletTransferAgoAttribute()
    {
        return Carbon::createFromDate($this->updated_at)->diffInMinutes(Carbon::now());
    }
}
