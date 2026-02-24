<?php

namespace App\Jobs\Order;

use App\Models\Currency\Currency;
use App\Models\Order\Order;
use App\Services\Order\OrderService;
use App\Services\Wallet\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order, $orderService;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct($order)
    {
        $this->order = $order;
        $this->orderService = new OrderService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Start matching order
        $this->orderService->processOrder($this->order, true);
    }
}
