<?php

namespace App\Repositories\Withdrawal;

use App\Interfaces\Withdrawal\WithdrawalRepositoryInterface;
use App\Mail\Withdrawals\WithdrawalRejected;
use App\Models\ColdStorage\ColdStorage;
use App\Models\User\User;
use App\Models\Wallet\WalletAddress;
use App\Models\Withdrawal\Withdrawal;
use App\Repositories\Wallet\WalletRepository;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class WithdrawalRepository implements WithdrawalRepositoryInterface
{
    /**
     * @var Withdrawal
     */
    protected $withdrawal;

    /**
     * WithdrawalRepository constructor.
     *
     */
    public function __construct()
    {
        $this->withdrawal = new Withdrawal();
    }

    public function get($type = 'coin') {

        $withdrawal = Withdrawal::query();

        $withdrawal->with('currency');

        $withdrawal->has('currency');

        if($type == 'fiat') {
            $withdrawal->fiat();
        } else {
            $withdrawal->coin();
        }

        $withdrawal->orderBy('created_at', 'desc');

        $withdrawal->limit(10);

        return $withdrawal->get();
    }

    public function getReport($type = 'coin') {

        $withdrawal = Withdrawal::query();

        $withdrawal->filter(request()->only(['search', 'type','referrer']))->orderByLatest();

        $withdrawal->has('currency')->has('user');

        $withdrawal->with(['currency', 'network', 'user']);

        if($type == 'fiat') {
            $withdrawal->fiat();
        } else {
            $withdrawal->coin();
        }

        return $withdrawal->paginate(50)->withQueryString();
    }

    public function getStatReport($period) {
        return DB::table('withdrawals')
            ->selectRaw('currencies.symbol as name, SUM(withdrawals.amount) as volume, SUM(withdrawals.fee) as income, COUNT(*) as total')
            ->join('currencies', 'currencies.id', 'withdrawals.currency_id')
            ->where('withdrawals.status', WITHDRAWAL_CONFIRMED_BY_PROVIDER)
            ->whereBetween('withdrawals.created_at', $period)
            ->groupByRaw('currencies.symbol')->get();
    }

    public function getReportUser(User $user, $pagination = true) {

        $withdrawal = Withdrawal::query();

        $withdrawal->filterUser(request()->only(['currency','txn','status']))->orderByLatest();

        $withdrawal->has('currency');

        $withdrawal->with(['currency.file']);

        $withdrawal->where('user_id', $user->id);

        if(!$pagination) {
            $withdrawal->limit(15);
            return $withdrawal->get();
        }

        return $withdrawal->paginate(50)->withQueryString();
    }


    public function count() {

        $withdrawal = Withdrawal::query();

        return $withdrawal->count();
    }

    public function getWithdrawal($id) {
        return Withdrawal::with('currency')->whereId($id)->first();
    }

    public function store($data) {
        return $this->withdrawal->create($data);
    }

    public function update($withdrawal, $data) {
        return $withdrawal->update($data);
    }

    public function getBySource($source_id, $network) {
        return Withdrawal::with('currency')->where('source_id', $source_id)->where('network_id', $network)->first();
    }

    /**
     * Moderate Withdrawal
     */
    public function moderate($withdrawal, $action, $txn = false) {

        return DB::transaction(function() use ($withdrawal, $action, $txn) {

            if($withdrawal->status != WITHDRAWAL_WAITING_APPROVAL) return false;

            $wallet = (new WalletRepository())->getWalletByCurrency($withdrawal->user_id, $withdrawal->currency_id);
            $walletService = new WalletService();

            if($action == "approve") {

                $withdrawal->status = WITHDRAWAL_CONFIRMED_BY_SYSTEM;
                $withdrawal->save();

                $response = $walletService->withdrawCryptoConfirmed($withdrawal->fresh(), $txn);

                if($response['status'] == STATUS_VALIDATION_ERROR) {

                    $withdrawal->status = WITHDRAWAL_FAILED;
                    $withdrawal->rejected_reason = $response['message'];
                    $withdrawal->update();

                    if($withdrawal->source_id != "system") {
                        // Decrease from withdraw
                        $walletService->decrease($wallet, $withdrawal->amount, 'withdraw');

                        // Increase wallet balance
                        $walletService->increase($wallet, $withdrawal->amount, 'wallet');
                    } else {

                        DB::table('cold_storage')
                            ->where('cold_storage_transaction_id', $withdrawal->id)
                            ->update(['cold_storage_transaction_id' => null]);

                    }

                } else {

                    $withdrawal->fresh();

                    $withdrawal->source_id = $response['source'];
                    $withdrawal->initial_raw = json_encode($response['message']);

                    if($withdrawal->network_id == NETWORK_BTC || $withdrawal->network_id == NETWORK_BRC20) {
                        $withdrawal->status = WITHDRAWAL_CONFIRMED_BY_PROVIDER;
                    } elseif($response['source'] == "internal") {

                        $withdrawal->status = WITHDRAWAL_CONFIRMED_BY_PROVIDER;

                        $walletRepository = new WalletRepository();

                        // Get user by wallet address
                        $internalUser = WalletAddress::where('address', $withdrawal->address)->first();

                        $depositWallet = $walletRepository->getWalletByCurrency($internalUser->user_id, $withdrawal->currency_id,false);

                        $walletRepository->depositInternal($depositWallet, $withdrawal->amount);

                    } else {
                        $withdrawal->status = WITHDRAWAL_WAITING_PROVIDER_APPROVAL;
                    }

                    $withdrawal->update();
                }

            } else {

                $withdrawal->status = WITHDRAWAL_REJECTED;
                $withdrawal->rejected_reason = nl2br(request()->get('reason'));
                $withdrawal->save();

                if($withdrawal->source_id != "system") {
                    // Decrease from withdraw
                    $walletService->decrease($wallet, $withdrawal->amount, 'withdraw');

                    // Increase wallet balance
                    $walletService->increase($wallet, $withdrawal->amount, 'wallet');

                    // Notify user
                    Mail::to($withdrawal->user)->queue(new WithdrawalRejected($withdrawal->user, $withdrawal->amount, $withdrawal->currency->symbol, $withdrawal->rejected_reason));
                } else {

                    DB::table('cold_storage')
                        ->where('cold_storage_transaction_id', $withdrawal->id)
                        ->update(['cold_storage_transaction_id' => null]);

                }
            }

            return true;

        }, DB_REPEAT_AFTER_DEADLOCK);
    }
}
