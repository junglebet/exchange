<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderBookUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $market;
    public $type;
    public $quantity;
    public $decimals;

    public $queue = 'orderbook';

    /**
     * Create a new event instance.
     *
     * @param $model
     * @param String $type
     */
    public function __construct($model, $type, $quantity = 0)
    {
        $this->order = $model['order'];
        $this->market = $model['name'];
        $this->decimals = $model['decimals'];
        $this->type = $type;
        $this->quantity = $quantity;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('orderbook-' . $this->market);
    }

    public function broadcastWith()
    {
        $payload = [
            'id' => $this->order['id'],
            'market' => $this->market,
            'side' => $this->order['side'],
            'price' => math_formatter($this->order['price'], $this->decimals),
            'quantity' => $this->order['quantity'],
            'created_at' => $this->order['created_at']
        ];

        if($this->type == "update") {
            $payload['updated_quantity'] = $this->quantity;
        }

        return [
            'order' => $payload,
            'type' => $this->type
        ];
    }
}
