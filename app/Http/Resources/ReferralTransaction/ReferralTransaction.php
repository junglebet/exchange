<?php

namespace App\Http\Resources\ReferralTransaction;

use Illuminate\Http\Resources\Json\JsonResource;

class ReferralTransaction extends JsonResource
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
            'id' => $this->id,
            'currency' => $this->currency->symbol,
            'amount' => $this->amount,
            'is_credited' => $this->is_credited,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
