<?php

namespace App\Http\Resources\Wallet\Deposit;

use Illuminate\Http\Resources\Json\JsonResource;

class Deposit extends JsonResource
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
            'deposit_id' => $this->deposit_id,
            'internal_id' => $this->internal_id,
            'status' => $this->status,
            'txn' => $this->txn,
            'logo' => $this->currency->file->path,
            'explorer' => $this->txn_link,
            'amount' => $this->amount,
            'fee' => $this->fee,
            'currency' => $this->currency->name,
            'symbol' => $this->currency->symbol,
            'address' => $this->address,
            'payment_id' => $this->payment_id,
            'confirms' => $this->confirms,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
