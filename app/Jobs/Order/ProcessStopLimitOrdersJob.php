<?php

namespace App\Jobs\Order;

use App\Models\Order\Order;
use App\Services\Order\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStopLimitOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $market, $orderService;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct($market_id)
    {
        $this->market = $market_id;

        $this->orderService = new OrderService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Process stop limit orders
        $this->orderService->processStopLimitOrders($this->market);
    }
}
