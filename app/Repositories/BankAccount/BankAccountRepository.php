<?php

namespace App\Repositories\BankAccount;

use App\Models\BankAccount\BankAccount;
use Auth;

class BankAccountRepository
{
    /**
     * @var BankAccount
     */
    protected $bankAccount;

    /**
     * BankAccountRepository constructor.
     *
     */
    public function __construct()
    {
        $this->bankAccount = new BankAccount();
    }

    public function get() {

        $bankAccounts = BankAccount::query();

        $bankAccounts->orderBy('id', 'desc');

        return $bankAccounts->paginate(50)->withQueryString();
    }

    public function all($onlyActive = false) {

        $bankAccounts = BankAccount::query();

        if($onlyActive) {
            $bankAccounts->active();
        }

        return $bankAccounts->get();

    }

    public function getBankAccountById($id) {
        return BankAccount::find($id);
    }

    public function getBankAccountBySlug($slug) {

        $bankAccount = BankAccount::query();

        $bankAccount->whereSlug($slug);

        $bankAccount->active();

        return $bankAccount->first();
    }

    public function store($data) {

        $bankAccount = $this->bankAccount->create($data);

        return $bankAccount->fresh();
    }

    public function update($id, $data) {

        $bankAccount = BankAccount::find($id);
        $bankAccount->update($data);
        return $bankAccount->fresh();
    }

    public function delete($id) {

        $bankAccount = BankAccount::find($id);
        $bankAccount->delete();

        return true;
    }
}
