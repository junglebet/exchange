<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction\Transaction;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class TransactionRepository
{
    public function count() {

        $transaction = Transaction::query();

        $transaction->maker();

        return $transaction->count();
    }

    public function getReport() {

        $transaction = Transaction::query();

        $transaction->filter(request()->only(['search', 'referrer']))->orderByLatest();

        $transaction->has('market')->has('user');

        $transaction->with(['market.quoteCurrency', 'market.baseCurrency', 'user']);

        return $transaction->paginate(50)->withQueryString();
    }

    public function getStatReport($period) {
        return DB::table('transactions')
            ->selectRaw('currencies.symbol, c2.symbol as baseSymbol, markets.name, markets.quote_precision as decimals, markets.base_precision as basedecimals, (SUM(transactions.base_currency) / 2) as baseVolume, (SUM(transactions.quote_currency) / 2) as quoteVolume, SUM(transactions.fee - transactions.referral_fee) as income, SUM(transactions.referral_fee) as referrals, COUNT(*) as total')
            ->join('markets', 'markets.id', 'transactions.market_id')
            ->join('currencies', 'currencies.id', 'markets.quote_currency_id')
            ->join('currencies as c2', 'c2.id', 'markets.base_currency_id')
            ->whereBetween('transactions.created_at', $period)
            ->groupByRaw('transactions.market_id, markets.name, markets.quote_precision, markets.base_precision, currencies.symbol, baseSymbol')->get();
    }

    public function getReportUser(User $user) {

        $transaction = Transaction::query();

        $transaction->filterUser(request()->only(['market', 'side']))->orderByLatest();

        $transaction->has('market')->has('user');

        $transaction->with(['market.quoteCurrency', 'market.baseCurrency']);

        $transaction->where('user_id', $user->id);

        return $transaction->paginate(50)->withQueryString();
    }
}
