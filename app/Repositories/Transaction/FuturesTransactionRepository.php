<?php

namespace App\Repositories\Transaction;

use App\Models\Order\FuturesContract;
use App\Models\Transaction\Transaction;
use App\Models\User\User;

class FuturesTransactionRepository
{
    public function count() {

        $transaction = FuturesContract::query();

        return $transaction->count();
    }

    public function getReport() {

        $transaction = Transaction::query();

        $transaction->filter(request()->only(['search']))->orderByLatest();

        $transaction->has('market')->has('user');

        $transaction->with(['market.quoteCurrency', 'market.baseCurrency', 'user']);

        return $transaction->paginate(50)->withQueryString();
    }

    public function getReportUser(User $user) {

        $transaction = FuturesContract::query();

        $transaction->filterUser(request()->only(['market', 'type']))->orderByLatest();

        $transaction->has('market')->has('user');

        $transaction->with(['market.quoteCurrency', 'market.baseCurrency']);

        $transaction->where('status', '<>','active');

        $transaction->where('user_id', $user->id);

        return $transaction->paginate(50)->withQueryString();
    }
}
