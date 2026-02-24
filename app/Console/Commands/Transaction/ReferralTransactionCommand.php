<?php

namespace App\Console\Commands\Transaction;

use App\Repositories\Transaction\ReferralTransactionRepository;
use App\Repositories\Wallet\WalletRepository;
use App\Services\Wallet\WalletService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReferralTransactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:referral-credits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Credit Referral Earnings to their wallets';

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
        $walletRepository = new WalletRepository();
        $walletService = new WalletService();
        $transactionRepository = new ReferralTransactionRepository();

        $transactions = $transactionRepository->getPending();

        foreach ($transactions as $transaction) {
            DB::transaction(function() use ($transaction, $walletRepository, $walletService) {

                // Update wallet
                $wallet = $walletRepository->getWalletByCurrency($transaction->user_id, $transaction->currency_id);
                $walletService->increase($wallet, $transaction->amount);

                // Update transaction status
                $transaction->is_credited = true;
                $transaction->update();
            }, DB_REPEAT_AFTER_DEADLOCK);
        }

    }
}
