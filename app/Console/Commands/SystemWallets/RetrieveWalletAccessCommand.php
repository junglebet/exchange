<?php

namespace App\Console\Commands\SystemWallets;

use App\Models\Wallet\WalletAddress;
use Illuminate\Console\Command;

class RetrieveWalletAccessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallets:retrieve-wallet-access {address}';

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
        $address = $this->argument('address', null);

        if(!$address) {
            $this->info('Wallet Address is not provided');
            return;
        }

        $wallet = WalletAddress::where('address', $address)->first();

        if(!$wallet) {
            $this->info('Wallet Address not found');
            return;
        }

        $this->info($wallet->private_key);
    }
}
