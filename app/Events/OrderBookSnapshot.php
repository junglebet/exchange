<?php

namespace App\Events;

use App\Models\Order\Order;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderBookSnapshot implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $asks;
    public $bids;
    public $market;

    public $queue = 'orderbook';

    /**
     * Create a new event instance.
     *
     * @param $order
     * @param String $type
     */
    public function __construct($market, $bids, $asks)
    {
        $this->market = $market;
        $this->bids = $bids;
        $this->asks = $asks;
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
        return [
            'bids' => $this->bids,
            'asks' => $this->asks
        ];
    }
}
