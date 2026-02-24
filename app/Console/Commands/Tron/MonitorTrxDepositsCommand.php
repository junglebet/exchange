<?php

namespace App\Console\Commands\Tron;

use App\Mail\Deposits\AdminDepositReceived;
use App\Mail\Deposits\DepositReceived;
use App\Models\Deposit\Deposit;
use App\Models\Wallet\WalletAddress;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Deposit\DepositRepository;
use App\Repositories\Wallet\WalletRepository;
use App\Services\PaymentGateways\Coin\Tron\Services\TronService;
use App\Services\Wallet\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Setting;

class MonitorTrxDepositsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tron:monitor-trx-deposits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $depositRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->depositRepository = new DepositRepository();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $wallets = WalletAddress::with('wallet.currency')->whereIn('network_id', [NETWORK_TRX, NETWORK_TRC])->has('user')->get();

        foreach ($wallets as $wallet) {

            $currency = (new CurrencyRepository())->getCurrencyBySymbol('TRX');

            if(!$currency) return;

            $response = Http::get(env('APP_TRONSCAN_API', 'https://apilist.tronscan.org') . '/api/transfer', [
                'count' => 'true',
                'limit' => '20',
                'address' => $wallet->address,
                'start' => '0',
                'sort' => '-timestamp',
            ]);

            $data = $response->json();

            if($response->successful() && isset($data['total']) && $data['total'] > 0) {

                foreach ($data['data'] as $transaction) {

                    if(!$transaction['transactionHash']) continue;

                    if($transaction['transferToAddress'] !== $wallet->address) continue;

                    if($transaction['transferFromAddress'] === setting('tron.wallet')) continue;

                    $deposit = Deposit::where('txn', $transaction['transactionHash'])->where('network_id', NETWORK_TRX)->first();

                    // Deposit exists
                    if($deposit) {

                        if($deposit->status == DEPOSIT_PENDING) {

                            $amount = math_sub($deposit->amount, $deposit->system_fee);

                            $deposit->status = DEPOSIT_CONFIRMED;
                            $deposit->confirms = 1;
                            $deposit->update();

                            $currencyWallet = (new WalletRepository())->getWalletByCurrency($wallet->user_id, $currency->id, false);

                            (new WalletService())->increase($currencyWallet, $amount);

                            try {
                                // Notify user
                                Mail::to($wallet->user)->queue(new DepositReceived($wallet->user, $deposit->amount, $currency->symbol));

                                // Admin Email Notification
                                $adminEmail = Setting::get('notification.admin_email', false);
                                $notificationAllowed = Setting::get('notification.crypto_deposits', false);

                                if($adminEmail && $notificationAllowed) {
                                    $route = route('admin.reports.deposits') . "?search=" . $deposit->deposit_id;
                                    Mail::to($adminEmail)->queue(new AdminDepositReceived($deposit->amount, $currency->symbol, $route));
                                }
                                // END Admin Email Notification

                            } catch (\Exception $e) {
                                Log::error('Deposit Notify Email Exception');
                            }
                        }

                        continue;

                    }

                    $service = new TronService();

                    $data = [
                        'user_id' => $wallet->user_id,
                        'symbol' => $currency->symbol,
                        'hash' => $transaction['transactionHash'],
                        'deposit_id' => generate_string(),
                        'fee' => 0,
                        'address' => $transaction['transferToAddress'],
                        'confirms' => 1,
                        'amount' => math_divide($transaction['amount'], (string)pow(10, 6)),
                        'full_amount' => $transaction['amount'],
                    ];

                    $service->handleDeposit('trx', $data);

                }

            }

            if(!$response->successful()) {
                Log::error('Tronscan Exception:');
                Log::error($response->body());
            }

            usleep(500000);
        }
    }
}
