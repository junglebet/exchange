<?php

namespace App\Http\Resources\Wallet;

use Illuminate\Http\Resources\Json\JsonResource;

class Wallet extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'currency' => $this->currency->name,
            'symbol' => $this->currency->symbol,
            'logo' => url($this->currency->file->path),
            'type' => $this->currency->type,
            'address' => $this->address ? $this->address->address : null,
            'payment_id' => $this->address ? $this->address->payment_id : null,
            'balance_in_wallet' => math_formatter($this->balance_in_wallet, $this->currency->decimals),
            'balance_in_order' => math_formatter($this->balance_in_order, $this->currency->decimals),
            'balance_in_withdraw' => math_formatter($this->balance_in_withdraw, $this->currency->decimals),
        ];
    }
}
