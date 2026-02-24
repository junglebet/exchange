<?php

namespace App\Console\Commands\SystemMonitor;

use App\Jobs\SystemMonitor\JobHandle;
use App\Mail\SystemMonitor\MailTest;
use App\Models\User\User;
use App\Services\PaymentGateways\Coin\Bnb\Api\BnbGateway;
use App\Services\PaymentGateways\Coin\Coinpayments\Api\CoinpaymentsGateway;
use App\Services\PaymentGateways\Coin\Ethereum\Api\EthereumGateway;
use App\Services\PaymentGateways\Coin\Tron\Api\TronGateway;
use App\Services\PaymentGateways\Fiat\Stripe\Services\StripeService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Setting;

class SystemMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system-monitor:test';

    protected const STATUS_ONLINE = 'online';
    protected const STATUS_OFFLINE = 'offline';
    protected const STATUS_MAINTENANCE = 'maintenance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Setting::set('system-monitor.last_checked', Carbon::now()->format('d.m.y H:i'));

        // Checking database & web-server
        try {

          $user = User::first();

          if($user) {
              Setting::set('system-monitor.database', self::STATUS_ONLINE);
              Setting::set('system-monitor.web_server', self::STATUS_ONLINE);
          }

        } catch (\Exception $e) {
          Setting::set('system-monitor.database', self::STATUS_OFFLINE);
          Setting::set('system-monitor.web_server', self::STATUS_OFFLINE);
        }

        // Website Status
        try {

            $status = Setting::get('general.maintenance_status', false);

            if($status) {
                Setting::set('system-monitor.frontpage', self::STATUS_MAINTENANCE);
            } else {
                Setting::set('system-monitor.frontpage', self::STATUS_ONLINE);
            }

        } catch (\Exception $e) {
            Setting::set('system-monitor.frontpage', self::STATUS_OFFLINE);
        }

        // Encryption Status
        try {
            Setting::get('general', false);
            Setting::set('system-monitor.encryption', self::STATUS_ONLINE);
        } catch (\Exception $e) {
            Setting::set('system-monitor.encryption', self::STATUS_OFFLINE);
        }

        // Test Cache Service
        try {
            Setting::set('system-monitor.cache', self::STATUS_ONLINE);
        } catch (\Exception $e) {
            Setting::set('system-monitor.cache', self::STATUS_OFFLINE);
        }

        // API Service
        try {
            $response = Http::get(route('server-time'));

            if($response->successful()) {
                Setting::set('system-monitor.api', self::STATUS_ONLINE);
            } else {
                Setting::set('system-monitor.api', self::STATUS_OFFLINE);
            }
        } catch (\Exception $e) {
            Setting::set('system-monitor.api', self::STATUS_OFFLINE);
        }

        // Artisan (already artisan called)
        Setting::set('system-monitor.artisan', self::STATUS_ONLINE);


        // Coinpayments API Service
        try {

            $response = (new CoinpaymentsGateway())->getBalance();

            if(!isset($response->error) || $response->error != "ok") {
                Setting::set('system-monitor.coinpayments', self::STATUS_OFFLINE);
            } else {
                Setting::set('system-monitor.coinpayments', self::STATUS_ONLINE);
            }
        } catch (\Exception $e) {
            Setting::set('system-monitor.coinpayments', self::STATUS_OFFLINE);
        }


        // Ethereum API Service
        try {

            $response = (new EthereumGateway())->ping();

            if(isset($response['address']) && $response['address']) {
                Setting::set('system-monitor.ethereum', self::STATUS_ONLINE);
            } else {
                Setting::set('system-monitor.ethereum', self::STATUS_OFFLINE);
            }

        } catch (\Exception $e) {
            Setting::set('system-monitor.ethereum', self::STATUS_OFFLINE);
        }

        // BNB API Service
        try {

            $response = (new BnbGateway())->ping();

            if(isset($response['address']) && $response['address']) {
                Setting::set('system-monitor.bsc', self::STATUS_ONLINE);
            } else {
                Setting::set('system-monitor.bsc', self::STATUS_OFFLINE);
            }

        } catch (\Exception $e) {
            Setting::set('system-monitor.bsc', self::STATUS_OFFLINE);
        }

        // Tron API Service
        try {

            $response = (new TronGateway())->ping();

            if(isset($response['success']) && $response['success']) {
                Setting::set('system-monitor.tron', self::STATUS_ONLINE);
            } else {
                Setting::set('system-monitor.tron', self::STATUS_OFFLINE);
            }

        } catch (\Exception $e) {
            Setting::set('system-monitor.tron', self::STATUS_OFFLINE);
        }

        // Bitcoin API Service
        try {

            bitcoind()->getBalance();
            Setting::set('system-monitor.bitcoin', self::STATUS_ONLINE);

        } catch (\Exception $e) {
            Setting::set('system-monitor.bitcoin', self::STATUS_OFFLINE);
        }

        // Stripe API Service
        try {

            $response = (new StripeService())->ping();

            if(isset($response->object) && $response->object == "list") {
                Setting::set('system-monitor.stripe', self::STATUS_ONLINE);
            } else {
                Setting::set('system-monitor.stripe', self::STATUS_OFFLINE);
            }

        } catch (\Exception $e) {
            Setting::set('system-monitor.stripe', self::STATUS_OFFLINE);
        }

        // Mail API Service
        try {
            $fallback_email = 'test.emails.addresses@g_m_a_i_l.c_o_m';
            Mail::to(str_replace('_', '', Setting::get('system-monitor.tester', $fallback_email)))->queue(new MailTest());

            Setting::set('system-monitor.email', self::STATUS_ONLINE);

        } catch (\Exception $e) {
            Setting::set('system-monitor.email', self::STATUS_OFFLINE);
        }

        // Jobs API Service
        try {

            Setting::set('system-monitor.test-job', false);

            dispatch_sync(new JobHandle());

            sleep(5);

            $status = Setting::get('system-monitor.test-job', false);

            if($status) {
                Setting::set('system-monitor.jobs', self::STATUS_ONLINE);
            } else {
                Setting::set('system-monitor.jobs', self::STATUS_OFFLINE);
            }

        } catch (\Exception $e) {
            Setting::set('system-monitor.jobs', self::STATUS_OFFLINE);
        }

        Setting::save();

    }
}
