<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit\Deposit;
use App\Models\Deposit\FiatDeposit;
use App\Models\Withdrawal\FiatWithdrawal;
use App\Models\Withdrawal\Withdrawal;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Deposit\DepositRepository;
use App\Repositories\Deposit\FiatDepositRepository;
use App\Repositories\Launchpad\LaunchpadRepository;
use App\Repositories\Staking\StakingUserRepository;
use App\Repositories\Transaction\ReferralTransactionRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Repositories\Wallet\WalletRepository;
use App\Repositories\Withdrawal\FiatWithdrawalRepository;
use App\Repositories\Withdrawal\WithdrawalRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Setting;

class ReportController extends Controller
{
    public function index() {
        return Redirect::route('admin.reports.wallets.system');
    }

    public function deposits() {

        $depositRepository = new DepositRepository();

        $deposits = $depositRepository->getReport();

        return Inertia::render('Admin/Reports/Deposits', [
            'filters' => request()->all(['search', 'type', 'referrer']),
            'deposits' => $deposits,
        ]);
    }

    public function resyncDeposit(Request $request) {

        $deposit = Deposit::where('id', $request->get('id'))->first();

        if(!$deposit) return response()->json(['success' => false]);

        $deposit->wallet_transfer_status = DEPOSIT_PENDING;
        $deposit->updated_at = Carbon::now();
        $deposit->update();

        return response()->json(['success' => true]);
    }

    public function fiatDeposits() {

        $depositRepository = new FiatDepositRepository();

        $deposits = $depositRepository->getReport();

        return Inertia::render('Admin/Reports/FiatDeposits', [
            'filters' => request()->all(['search', 'type', 'referrer']),
            'deposits' => $deposits,
        ]);
    }

    public function withdrawals() {

        $withdrawalRepository = new WithdrawalRepository();

        $withdrawals = $withdrawalRepository->getReport();

        $withdrawals->data = collect($withdrawals->items())->map(function($withdrawal){

            $item = $withdrawal;

            if($item->network_id == NETWORK_ERC) {
                $item->wallet_balance = $item->currency->wallet_balance_erc;
            } elseif($item->network_id == NETWORK_BEP) {
                $item->wallet_balance = $item->currency->wallet_balance_bep;
            } elseif($item->network_id == NETWORK_TRC) {
                $item->wallet_balance = $item->currency->wallet_balance_trc;
            } elseif($item->network_id == NETWORK_MATIC20) {
                $item->wallet_balance = $item->currency->wallet_balance_matic;
            } else {
                $item->wallet_balance = $item->currency->wallet_balance;
            }

            return $item;
        });

        return Inertia::render('Admin/Reports/Withdrawals', [
            'filters' => request()->all(['search', 'type', 'referrer']),
            'withdrawals' => $withdrawals,
        ]);
    }

    public function fiatWithdrawals() {

        $withdrawalRepository = new FiatWithdrawalRepository();

        $withdrawals = $withdrawalRepository->getReport();

        return Inertia::render('Admin/Reports/FiatWithdrawals', [
            'filters' => request()->all(['search', 'referrer']),
            'withdrawals' => $withdrawals,
        ]);
    }

    public function trades() {

        $transactionRepository = new TransactionRepository();

        $transactions = $transactionRepository->getReport();

        return Inertia::render('Admin/Reports/Trades', [
            'filters' => request()->all(['search', 'type', 'referrer']),
            'transactions' => $transactions,
        ]);
    }

    public function wallets() {

        $walletRepository = new WalletRepository();

        $wallets = $walletRepository->getReport();

        $search = request()->get('search');
        $type = request()->get('type', 'all');
        $referrer = request()->get('referrer');
        $user = request()->get('user');

        return Inertia::render('Admin/Reports/Wallets', [
            'filters' => [
                'search' => $search,
                'type' => $type,
                'referrer' => $referrer,
                'user' => $user
            ],
            'wallets' => $wallets,
        ]);
    }

    public function transferWallets(Request $request) {

        $walletRepository = new WalletRepository();

        $wallet = $walletRepository->getWallet($request->get('wallet'));

        $walletRepository->depositInternal($wallet, $request->get('amount'));

        return response()->json(['success' => true]);
    }

    public function finances() {

        $walletRepository = new WalletRepository();

        $wallets = $walletRepository->getReport();

        $type = request()->get('type', 'all');
        $referrer = request()->get('referrer');

        return Inertia::render('Admin/Reports/Finances', [
            'filters' => [
                'type' => $type,
                'referrer' => $referrer,
            ],
        ]);
    }

    public function financesFetch() {

        $type = request()->get('type', 'trades');

        $end = Carbon::now()->format('Y-m-d 23:59:59');
        $start = Carbon::now()->format('Y-m-d 00:00:01');

        $period = request()->get('period', []);

        $period[0] = isset($period[0]) ? $period[0] . ' 00:00:01' : $start;
        $period[1] = isset($period[1]) ? $period[1] . ' 23:59:59' : $end;

        $reports = [];

        if($type == "trades") {
            $repository = new TransactionRepository();
            $reports = $repository->getStatReport($period);
        }

        if($type == "deposits") {
            $repository = new DepositRepository();
            $reports = $repository->getStatReport($period);
        }

        if($type == "withdrawals") {
            $repository = new WithdrawalRepository();
            $reports = $repository->getStatReport($period);
        }

        if($type == "fiat_deposits") {
            $repository = new FiatDepositRepository();
            $reports = $repository->getStatReport($period);
        }

        if($type == "fiat_withdrawals") {
            $repository = new FiatWithdrawalRepository();
            $reports = $repository->getStatReport($period);
        }

        return response()->json([
            $type => $reports,
        ]);
    }



    public function systemWallets() {

        $currencyRepository = new CurrencyRepository();

        $requests = request()->only(['search','type']);

        $currencies = $currencyRepository->getReport($requests);

        $search = request()->get('search');
        $type = request()->get('type', 'all');
        $referrer = request()->get('referrer');

        return Inertia::render('Admin/Reports/SystemWallets', [
            'filters' => [
                'search' => $search,
                'type' => $type,
                'referrer' => $referrer
            ],
            'currencies' => $currencies,
        ]);
    }

    public function referralTransactions() {

        $transactionRepository = new ReferralTransactionRepository();

        $transactions = $transactionRepository->getReport();

        return Inertia::render('Admin/Reports/ReferralTransactions', [
            'filters' => request()->all(['search', 'referrer']),
            'transactions' => $transactions,
        ]);
    }

    public function launchpadTransactions() {

        $transactionRepository = new LaunchpadRepository();

        $transactions = $transactionRepository->getReport();

        return Inertia::render('Admin/Reports/LaunchpadTransactions', [
            'filters' => request()->all(['search','referrer']),
            'transactions' => $transactions,
        ]);
    }

    public function stakingTransactions() {

        $transactionRepository = new StakingUserRepository();

        $transactions = $transactionRepository->get(true);

        return Inertia::render('Admin/Reports/StakingTransactions', [
            'filters' => request()->all(['search', 'referrer']),
            'transactions' => $transactions,
        ]);
    }

    /**
     * Moderate withdrawal action
     *
     * @return \Illuminate\Http\Response
     */
    public function moderateWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        $result = (new WithdrawalRepository())->moderate($withdrawal, $request->get('action'), $request->get('txn'));

        return Redirect::route('admin.reports.withdrawals');
    }


    /**
     * Moderate Fiat Deposit
     */
    public function moderateFiatDeposit(Request $request, FiatDeposit $deposit) {

        $result = (new FiatDepositRepository())->moderate($deposit, $request->get('action'));

        return Redirect::route('admin.reports.deposits.fiat');
    }

    /**
     * Moderate Fiat Withdrawal
     */
    public function moderateFiatWithdrawal(Request $request, FiatWithdrawal $withdrawal) {

        $result = (new FiatWithdrawalRepository())->moderate($withdrawal, $request->get('action'));

        return Redirect::route('admin.reports.withdrawals.fiat');
    }
}
