<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cache:prune-stale-tags')->hourly();

        // Auto withdrawals
        $schedule->command('withdrawal:automate')->everyTwoMinutes()->withoutOverlapping();

        // PerfectMoney
        $schedule->command('wallets:perfect-money-wallet-balance')->everyMinute()->withoutOverlapping();
        $schedule->command('wallets:perfect-money-wallet-withdraw-automate')->everyMinute()->withoutOverlapping();


        //Staking Rewards Calculation
        $schedule->command('staking:rewards-calculate')->dailyAt('00:00');

        //Staking Status
        $schedule->command('staking:status')->dailyAt('23:30');

        // Launchpad
        $schedule->command('launchpad:state-monitor')->everyMinute()->withoutOverlapping();

        //Referral
        $schedule->command('transaction:referral-credits')->everyFiveMinutes()->withoutOverlapping();

        // Wallets
        $schedule->command('wallets:coinpayments-wallet-balance')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('wallets:ethereum-wallet-balance')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('wallets:bnb-wallet-balance')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('wallets:trx-wallet-balance')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('wallets:btc-wallet-balance')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('wallets:brc-wallet-balance')->everyFifteenMinutes()->withoutOverlapping();

        $schedule->command('wallets:matic-wallet-balance')->everyFifteenMinutes()->withoutOverlapping();

        // Market
        $schedule->command('market-monitor:stats')->daily()->withoutOverlapping();

        // Bnb
        $schedule->command('bnb:monitor-bep-deposits')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('bnb:monitor-bnb-deposits')->everyThreeMinutes()->withoutOverlapping();

        $schedule->command('bnb:bep-transfer-pending')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('bnb:bnb-transfer-pending')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('bnb:bnb-withdrawals-pending')->everyThreeMinutes()->withoutOverlapping();

        // Ethereum
        $schedule->command('ethereum:monitor-erc-deposits')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('ethereum:monitor-ethereum-deposits')->everyThreeMinutes()->withoutOverlapping();

        $schedule->command('ethereum:erc-transfer-pending')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('ethereum:eth-transfer-pending')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('ethereum:eth-withdrawals-pending')->everyThreeMinutes()->withoutOverlapping();

        // Polygon
        $schedule->command('matic:monitor-matic-20-deposits')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('matic:monitor-matic-deposits')->everyThreeMinutes()->withoutOverlapping();

        $schedule->command('matic:matic-20-transfer-pending')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('matic:matic-transfer-pending')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('matic:matic-withdrawals-pending')->everyThreeMinutes()->withoutOverlapping();

        // Tron
        $schedule->command('tron:monitor-trx-deposits')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('tron:monitor-trc-deposits')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('tron:trx-withdrawals-pending')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('tron:trx-transfer-pending')->everyThreeMinutes()->withoutOverlapping();
        $schedule->command('tron:trc-transfer-pending')->everyThreeMinutes()->withoutOverlapping();


        // BTC
        $schedule->command('btc:brc-withdrawals-inscribing')->everyMinute()->withoutOverlapping();
        $schedule->command('btc:brc-withdrawals-inscribed')->everyMinute()->withoutOverlapping();
        $schedule->command('btc:brc-deposits-inscribed')->everyTwoMinute()->withoutOverlapping();
        $schedule->command('btc:brc-deposits-inscribing')->everyTwoMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
