<?php

namespace App\Repositories\Report;

use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Deposit\DepositRepository;
use App\Repositories\KycDocument\KycDocumentRepository;
use App\Repositories\Market\MarketRepository;
use App\Repositories\Transaction\ReferralTransactionRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\Withdrawal\WithdrawalRepository;

class ReportRepository
{
    public function getStats() {

        $depositRepository = new DepositRepository();
        $withdrawalRepository = new WithdrawalRepository();
        $transactionRepository = new TransactionRepository();
        $referralTransactionRepository = new ReferralTransactionRepository();

        $data['deposits'] = $depositRepository->count();
        $data['withdrawals'] = $withdrawalRepository->count();
        $data['trades'] = $transactionRepository->count();
        $data['referralTransactions'] = $referralTransactionRepository->count();

        return $data;
    }

    public function getDashboardStats() {

        $userRepository = new UserRepository();
        $marketRepository = new MarketRepository();
        $currencyRepository = new CurrencyRepository();
        $kycDocumentRepository = new KycDocumentRepository();

        $data['users'] = $userRepository->count();
        $data['markets'] = $marketRepository->count();
        $data['currencies'] = $currencyRepository->count();
        $data['documents'] = $kycDocumentRepository->count();

        return $data;
    }
}
