<?php

namespace App\Jobs\Market;

use App\Models\Market\Market;
use App\Services\Market\MarketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MarketCapCalculationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $market, $marketService;

    /**
     * Create a new job instance.
     *
     * @param Market $market
     */
    public function __construct(Market $market)
    {
        $this->market = $market;

        $this->marketService = new MarketService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Process market cap calculation
        $this->marketService->calculateMarketCapitalization($this->market);
    }
}
