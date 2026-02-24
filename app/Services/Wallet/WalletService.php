<?php

namespace App\Services\Wallet;

use App\Models\Currency\Currency;
use App\Models\User\User;
use App\Models\Wallet\Wallet;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Network\NetworkRepository;
use App\Repositories\Wallet\WalletRepository;
use App\Repositories\Withdrawal\WithdrawalRepository;
use App\Services\Currency\CurrencyService;
use DB;
use Carbon\Carbon;

class WalletService {

    private $walletRepository;

    public function __construct()
    {
        $this->walletRepository = new WalletRepository();
    }

    public $reflectField = [
      'order' => 'balance_in_order',
      'wallet' => 'balance_in_wallet',
      'withdraw' => 'balance_in_withdraw'
    ];

    public function increase(Wallet $wallet, $quantity, $field = 'wallet') {

        $originalField = $this->reflectField[$field];

        DB::table('wallets')
            ->where('id', $wallet->id)
            ->update([
                $originalField => DB::raw("$originalField + $quantity"),
                'updated_at' => Carbon::now(),
            ]);
    }

    public function decrease(Wallet $wallet, $quantity, $field = 'order') {

        $originalField = $this->reflectField[$field];

        DB::table('wallets')
            ->where('id', $wallet->id)
            ->update([
                $originalField => DB::raw("$originalField - $quantity"),
                'updated_at' => Carbon::now(),
            ]);
    }

    public function revert(Wallet $wallet, $quantity, $field = 'order', $fee = 0) {

        $walletField = $this->reflectField['wallet'];
        $reflectedField = $this->reflectField[$field];

        DB::table('wallets')
            ->where('id', $wallet->id)
            ->update([
                $reflectedField => DB::raw("$reflectedField - $quantity"),
                $walletField => DB::raw("$walletField + $quantity + $fee"),
                'updated_at' => Carbon::now(),
            ]);
    }

    public function createWalletsForUser(User $user) {

       $currencies = Currency::get();

       foreach ($currencies as $currency) {
           $this->assignWallet($currency, [$user]);
       }
    }

    public function createWalletsForCurrency(Currency $currency) {
        $this->assignWallet($currency, User::get());
    }

    public function assignWallet(Currency $currency, $users) {
        foreach ($users as $user) {
            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->currency_id = $currency->id;
            $wallet->save();
        }
    }

    public function getWallets($user_id = false) {

        // If user not defined then take from current user
        if(!$user_id)
            $user_id = auth()->user()->id;

        return $this->walletRepository->getWallets($user_id);
    }

    public function getWallet($id) {
        return $this->walletRepository->getWallet($id);
    }

    public function getWalletByCurrency($user_id, $currency) {
        return $this->walletRepository->getWalletByCurrency($user_id, $currency);
    }

    public function getWalletAddress($wallet, $currency, $network) {
        return $this->walletRepository->getWalletAddress($wallet, $currency, $network);
    }

    public function getWalletData($currency, $user_id) {
        return $this->getWalletByCurrency($user_id, $currency);
    }

    public function generateWalletAddress($symbol, $user_id = false, $network = false) {

        if(!$user_id)
            $user_id = auth()->user()->id;

        $currency = (new CurrencyService())->getCurrencyBySymbol($symbol);

        if(!$currency) {
            return [
                'data' => null,
                'status' => STATUS_VALIDATION_ERROR,
                'message' => 'currency_not_found'
            ];
        }

        $wallet = $this->getWalletByCurrency($user_id, $currency->id);

        if(!$wallet) {
            return [
                'data' => null,
                'status' => STATUS_VALIDATION_ERROR,
                'message' => 'wallet_not_found'
            ];
        }

        $network = (new NetworkRepository())->getById($network);

        $walletAddress = $this->getWalletAddress($wallet, $currency, $network);

        if(!$walletAddress) {
            return [
                'data' => null,
                'status' => STATUS_VALIDATION_ERROR,
                'message' => 'address_was_not_generated'
            ];
        }

        return [
            'data' => [
                'address' => $walletAddress->address,
                'paymentId' => $walletAddress->payment_id,
            ],
            'status' => STATUS_OK,
        ];

    }

    public function withdrawCrypto($symbol, $address, $amount, $user_id = false, $network = false, $payment_id = null) {

        if(!$user_id)
            $user_id = auth()->user()->id;

        $currency = (new CurrencyService())->getCurrencyBySymbol($symbol);

        $wallet = $this->getWalletByCurrency($user_id, $currency->id);

        $internal_id = null;
        $isInternal = (new NetworkRepository())->getInternalWallet($address);

        if($isInternal) {

            $feeAmount = 0;
            $network = (new NetworkRepository())->getById(NETWORK_INTERNAL);
            $internal_id = generate_uuid();

        } else {

            $network = (new NetworkRepository())->getById($network);

            if ($network->slug == "erc20") {
                $feeAmount = $currency->withdraw_fee_erc == 0 ? $currency->withdraw_fee_erc_fixed : math_percentage($amount, $currency->withdraw_fee_erc);
            } elseif ($network->slug == "bep20") {
                $feeAmount = $currency->withdraw_fee_bep == 0 ? $currency->withdraw_fee_bep_fixed : math_percentage($amount, $currency->withdraw_fee_bep);
            } elseif ($network->slug == "trc20") {
                $feeAmount = $currency->withdraw_fee_trc == 0 ? $currency->withdraw_fee_trc_fixed : math_percentage($amount, $currency->withdraw_fee_trc);
            } elseif ($network->slug == "matic20") {
                $feeAmount = $currency->withdraw_fee_matic == 0 ? $currency->withdraw_fee_matic_fixed : math_percentage($amount, $currency->withdraw_fee_matic);
            } else {
                $feeAmount = $currency->withdraw_fee == 0 ? $currency->withdraw_fee_fixed : math_percentage($amount, $currency->withdraw_fee);
            }

        }

        $extraStatus = '';

        if($network->slug == "brc20") {
            $extraStatus = WITHDRAWAL_INSCRIBING;
        }

        $data = [
            'withdrawal_id' => generate_uuid(),
            'txn' => null,
            'source_id' => null,
            'currency_id' => $currency->id,
            'type' => 'coin',
            'network_id' => $network->id,
            'amount' => $amount,
            'fee' => $feeAmount,
            'address' => $address,
            'payment_id' => $payment_id,
            'user_id' => $wallet->user_id,
            'confirms' => 0,
            'status' => WITHDRAWAL_WAITING_APPROVAL,
            'extra_status' => $extraStatus,
            'initial_raw' => null,
            'internal_id' => $internal_id,
            'inusd' => math_multiply($amount, (new CurrencyRepository())->currencyPriceInUsd($currency))
        ];

        (new WithdrawalRepository())->store($data);

        // Decrease from balance
        $this->decrease($wallet, $amount, 'wallet');

        // Increase withdraw balance
        $this->increase($wallet, $amount, 'withdraw');

        return true;
    }

    public function withdrawSystem($currency, $network, $address, $amount) {

        $data = [
            'withdrawal_id' => generate_uuid(),
            'txn' => null,
            'source_id' => 'system',
            'currency_id' => $currency,
            'type' => 'coin',
            'network_id' => $network,
            'amount' => $amount,
            'fee' => 0,
            'address' => $address,
            'payment_id' => null,
            'user_id' => 1,
            'confirms' => 0,
            'status' => WITHDRAWAL_WAITING_APPROVAL,
            'initial_raw' => null,
        ];

        return (new WithdrawalRepository())->store($data);
    }

    public function withdrawCryptoConfirmed($withdrawal, $txn = false) {
        return $this->walletRepository->withdrawCrypto($withdrawal, $txn);
    }

    public function internalTransfer($withdrawal) {
        return [
            'status' => STATUS_OK,
            'source' => 'internal',
            'message' => [],
        ];
    }
}
