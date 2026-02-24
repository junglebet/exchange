<?php

namespace App\Http\Resources\Currency;

use App\Http\Resources\Network\NetworkCollection;
use App\Repositories\Currency\CurrencyRepository;
use App\Services\Currency\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

class Currency extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'name' => $this->name,
            'symbol' => $this->symbol,
            'fullname' => $this->name . ' (' . $this->symbol . ')',
            'logo' => $this->file->path,
            'decimals' => $this->decimals,
            'type' => $this->type,
            'status' => $this->status,
            'min_deposit_confirmation' => $this->min_deposit_confirmation,
            'deposit_status' => $this->deposit_status,
            'withdraw_status' => $this->withdraw_status,

            'deposit_fee' => $this->deposit_fee,
            'deposit_fee_fixed' => $this->deposit_fee_fixed,

            'deposit_fee_erc' => $this->deposit_fee_erc,
            'deposit_fee_bep' => $this->deposit_fee_bep,
            'deposit_fee_trc' => $this->deposit_fee_trc,
            'deposit_fee_matic' => $this->deposit_fee_matic,

            'deposit_fee_erc_fixed' => $this->deposit_fee_erc_fixed,
            'deposit_fee_bep_fixed' => $this->deposit_fee_bep_fixed,
            'deposit_fee_trc_fixed' => $this->deposit_fee_trc_fixed,
            'deposit_fee_matic_fixed' => $this->deposit_fee_matic_fixed,

            'withdraw_fee' => $this->withdraw_fee,

            'withdraw_fee_fixed' => $this->withdraw_fee_fixed,

            'withdraw_fee_erc' => $this->withdraw_fee_erc,
            'withdraw_fee_bep' => $this->withdraw_fee_bep,
            'withdraw_fee_trc' => $this->withdraw_fee_trc,
            'withdraw_fee_matic' => $this->withdraw_fee_matic,

            'withdraw_fee_erc_fixed' => $this->withdraw_fee_erc_fixed,
            'withdraw_fee_bep_fixed' => $this->withdraw_fee_bep_fixed,
            'withdraw_fee_trc_fixed' => $this->withdraw_fee_trc_fixed,
            'withdraw_fee_matic_fixed' => $this->withdraw_fee_matic_fixed,

            'min_deposit' => math_formatter($this->min_deposit, $this->decimals),
            'max_deposit' => math_formatter($this->max_deposit, $this->decimals),
            'min_withdraw' => math_formatter($this->min_withdraw, $this->decimals),
            'max_withdraw' => math_formatter($this->max_withdraw, $this->decimals),
            'has_payment_id' => $this->has_payment_id,
            'networks' => new NetworkCollection($this->networks),
        ];

        if(config('app.fees_in_usd')) {
            if ($this->withdraw_fee_fixed) {
                $data['withdraw_fee_fixed'] = (new CurrencyRepository())->calculateAmountInUsd($this, $this->withdraw_fee_fixed);
            }

            if ($this->withdraw_fee_erc_fixed) {
                $data['withdraw_fee_erc_fixed'] = (new CurrencyRepository())->calculateAmountInUsd($this, $this->withdraw_fee_erc_fixed);
            }

            if ($this->withdraw_fee_bep_fixed) {
                $data['withdraw_fee_bep_fixed'] = (new CurrencyRepository())->calculateAmountInUsd($this, $this->withdraw_fee_bep_fixed);
            }

            if ($this->withdraw_fee_trc_fixed) {
                $data['withdraw_fee_trc_fixed'] = (new CurrencyRepository())->calculateAmountInUsd($this, $this->withdraw_fee_trc_fixed);
            }

            if ($this->withdraw_fee_matic_fixed) {
                $data['withdraw_fee_matic_fixed'] = (new CurrencyRepository())->calculateAmountInUsd($this, $this->withdraw_fee_matic_fixed);
            }
        }

        return $data;
    }
}
