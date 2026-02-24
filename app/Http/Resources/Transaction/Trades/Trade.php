<?php

namespace App\Http\Resources\Transaction\Trades;

use Illuminate\Http\Resources\Json\JsonResource;

class Trade extends JsonResource
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
            'market' => $this->market->name,
            'side' => $this->order_side,
            'type' => $this->order_type,
            'price' => $this->price,
            'fee' => $this->fee,
            'base_currency' => $this->base_currency,
            'quote_currency' => $this->quote_currency,
            'quote_symbol' => $this->market->quoteCurrency->symbol,
            'base_symbol' => $this->market->baseCurrency->symbol,
            'quantity' => $this->base_currency,
            'is_maker' => $this->is_maker,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
