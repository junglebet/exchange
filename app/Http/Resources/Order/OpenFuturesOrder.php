<?php

namespace App\Http\Resources\Order;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OpenFuturesOrder extends JsonResource
{
    public $custom_fields = [];

    public function __construct($resource, $custom_fields = [])
    {
        $this->custom_fields = $custom_fields;

        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if($this->created_at instanceof Carbon) {
            $date = $this->created_at->format('Y-m-d H:i:s');
        } else {
            $date = $this->created_at;
        }

        $marketPrice = math_formatter(market_get_stats($this->market->id, 'last'), $this->quote_precision);

        $pnl = futures_pnl_calculate($this->quantity, $this->price, $marketPrice, $this->leverage, $this->is_long);

        $pnlAmount = math_percentage($this->balance, $pnl);

        $order = [
            'id' => $this->id,
            'is_long' => $this->is_long,
            'market' => $this->market->name,
            'created_at' => $date,
            'quantity' => $this->quantity,
            'balance' => $this->balance,
            'released_amount' => $this->released_amount,
            'price' => $this->price,
            'leverage' => intval($this->leverage),
            'liquidation_price' => $this->liquidation_price,
            'type' => $this->is_long ? 'long' : 'short',
            'pnl' => $pnl,
            'pnlAmount' => $pnlAmount,
            'pnl_profitable' => $pnl >= 0,
            'status' => $this->status
        ];

        if($this->custom_fields && is_array($this->custom_fields)) {
            $order = array_merge($order, $this->custom_fields);
        }

        return $order;
    }
}
