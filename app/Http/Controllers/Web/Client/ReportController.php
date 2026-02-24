<?php

namespace App\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Currency\CurrencyLiteCollection;
use App\Http\Resources\Launchpad\Launchpad;
use App\Http\Resources\Launchpad\LaunchpadCollection;
use App\Http\Resources\Launchpad\LaunchpadTransaction;
use App\Http\Resources\Market\MarketLiteCollection;
use App\Http\Resources\Order\OpenFuturesOrder;
use App\Http\Resources\Order\OrderHistory;
use App\Http\Resources\ReferralTransaction\ReferralTransaction;
use App\Http\Resources\Transaction\FuturesTransaction;
use App\Http\Resources\Transaction\Trades\Trade;
use App\Http\Resources\Wallet\Deposit\Deposit;
use App\Http\Resources\Wallet\Deposit\FiatDeposit;
use App\Http\Resources\Wallet\Withdrawal\FiatWithdrawal;
use App\Http\Resources\Wallet\Withdrawal\Withdrawal;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Deposit\DepositRepository;
use App\Repositories\Deposit\FiatDepositRepository;
use App\Repositories\Launchpad\LaunchpadRepository;
use App\Repositories\Market\MarketRepository;
use App\Repositories\Order\OrderHistoryRepository;
use App\Repositories\Transaction\FuturesTransactionRepository;
use App\Repositories\Transaction\ReferralTransactionRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Repositories\Withdrawal\FiatWithdrawalRepository;
use App\Repositories\Withdrawal\WithdrawalRepository;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Setting;

class ReportController extends Controller
{
    public function deposits() {

        $depositRepository = new DepositRepository();
        $currencyRepository = new CurrencyRepository();

        $currencies = new CurrencyLiteCollection($currencyRepository->all(false, false, [], 'coin'));

        $deposits = Deposit::collection($depositRepository->getReportUser(auth()->user()))->response()->getData(true);

        return Inertia::render('Report/Deposits', [
            'filters' => Request::all(['currency','txn','status']),
            'deposits' => $deposits,
            'currencies' => $currencies
        ]);
    }

    public function withdrawals() {

        $withdrawalRepository = new WithdrawalRepository();
        $currencyRepository = new CurrencyRepository();

        $currencies = new CurrencyLiteCollection($currencyRepository->all(false, false, [], 'coin'));

        $withdrawals = Withdrawal::collection($withdrawalRepository->getReportUser(auth()->user()))->response()->getData(true);

        return Inertia::render('Report/Withdrawals', [
            'filters' => Request::all(['currency','txn','status']),
            'withdrawals' => $withdrawals,
            'currencies' => $currencies
        ]);
    }

    public function fiatDeposits() {

        $depositRepository = new FiatDepositRepository();
        $currencyRepository = new CurrencyRepository();

        $currencies = new CurrencyLiteCollection($currencyRepository->all(false, false, [], 'fiat'));

        $deposits = FiatDeposit::collection($depositRepository->getReportUser(auth()->user()))->response()->getData(true);

        return Inertia::render('Report/FiatDeposits', [
            'filters' => Request::all(['currency', 'status']),
            'deposits' => $deposits,
            'currencies' => $currencies
        ]);
    }

    public function fiatWithdrawals() {

        $withdrawalRepository = new FiatWithdrawalRepository();
        $currencyRepository = new CurrencyRepository();

        $currencies = new CurrencyLiteCollection($currencyRepository->all(false, false, [], 'fiat'));

        $withdrawals = FiatWithdrawal::collection($withdrawalRepository->getReportUser(auth()->user()))->response()->getData(true);

        return Inertia::render('Report/FiatWithdrawals', [
            'filters' => Request::all(['currency', 'status']),
            'withdrawals' => $withdrawals,
            'currencies' => $currencies
        ]);
    }

    /**
     * @return \Inertia\Response
     */
    public function trades() {

        $transactionRepository = new TransactionRepository();
        $marketRepository = new MarketRepository();

        $markets = new MarketLiteCollection($marketRepository->all(false));

        $transactions = Trade::collection($transactionRepository->getReportUser(auth()->user()))->response()->getData(true);

        return Inertia::render('Report/Trades', [
            'filters' => Request::all(['market', 'side']),
            'transactions' => $transactions,
            'markets' => $markets
        ]);
    }

    /**
     * @return \Inertia\Response
     */
    public function futuresTrades() {

        $transactionRepository = new FuturesTransactionRepository();
        $marketRepository = new MarketRepository();

        $markets = new MarketLiteCollection($marketRepository->all(false));

        $transactions = FuturesTransaction::collection($transactionRepository->getReportUser(auth()->user()))->response()->getData(true);

        return Inertia::render('Report/FuturesTrades', [
            'filters' => Request::all(['market', 'type']),
            'transactions' => $transactions,
            'markets' => $markets
        ]);
    }

    /**
     * @return \Inertia\Response
     */
    public function orderHistory() {

        $orderHistoryRepository = new OrderHistoryRepository();
        $marketRepository = new MarketRepository();

        $markets = new MarketLiteCollection($marketRepository->all(false));

        $orders = OrderHistory::collection($orderHistoryRepository->getReportUser(auth()->user()))->response()->getData(true);

        return Inertia::render('Report/OrderHistories', [
            'filters' => Request::all(['market', 'side']),
            'orders' => $orders,
            'markets' => $markets
        ]);
    }

    /**
     * @return \Inertia\Response
     */
    public function referralTransactions() {

        $transactionRepository = new ReferralTransactionRepository();

        $transactions = ReferralTransaction::collection($transactionRepository->getReportUser(auth()->user()))->response()->getData(true);

        return Inertia::render('Report/ReferralTransactions', [
            'filters' => Request::all(['search']),
            'transactions' => $transactions,
        ]);
    }

    /**
     * @return \Inertia\Response
     */
    public function launchpadTransactions() {

        $transactionRepository = new LaunchpadRepository();

        $transactions = LaunchpadTransaction::collection($transactionRepository->getReportUser(auth()->user()))->response()->getData(true);

        return Inertia::render('Report/LaunchpadTransactions', [
            'filters' => Request::all(['search']),
            'transactions' => $transactions,
        ]);
    }
}
