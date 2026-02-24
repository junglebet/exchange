<?php

namespace App\Observers\User;

use App\Jobs\Wallet\CreateWalletsForUserJob;
use App\Models\User\User;
use App\Services\Wallet\WalletService;

class UserObserver
{
    public $walletService;

    public function __construct() {
        $this->walletService = new WalletService();
    }

    /**
     * Listen to the User created event.
     *
     * @param  \App\Models\User\User $user
     * @return void
     */
    public function created(User $user)
    {
        $job = new CreateWalletsForUserJob($user);

        dispatch_sync($job);
    }
}
