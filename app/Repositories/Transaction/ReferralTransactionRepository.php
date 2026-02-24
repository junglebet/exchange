<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction\ReferralTransaction;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;

class ReferralTransactionRepository
{
    /**
     * @var ReferralTransaction
     */
    protected $transaction;

    /**
     * ReferralTransactionRepository constructor.
     *
     */
    public function __construct()
    {
        $this->transaction = new ReferralTransaction();
    }

    /**
     * @param $data
     * @return ReferralTransaction
     */
    public function store($data) {

        $transaction = $this->transaction->create($data);

        return $transaction;
    }

    public function count() {

        $transaction = ReferralTransaction::query();

        return $transaction->count();
    }

    /**
     * @return Collection
     */
    public function getPending() {

        $transaction = ReferralTransaction::query();

        $transaction->pending();

        return $transaction->get();
    }

    public function getReport() {

        $transaction = ReferralTransaction::query();

        $transaction->filter(request()->only(['search', 'referrer']))->orderByLatest();

        $transaction->has('currency')->has('user');

        $transaction->with(['currency', 'user']);

        return $transaction->paginate(50)->withQueryString();
    }

    public function getReportUser(User $user) {

        $transaction = ReferralTransaction::query();

        $transaction->filter(request()->only(['search']))->orderByLatest();

        $transaction->has('currency')->has('user');

        $transaction->with(['currency']);

        $transaction->where('user_id', $user->id);

        return $transaction->paginate(50)->withQueryString();
    }
}
