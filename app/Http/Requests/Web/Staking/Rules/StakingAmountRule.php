<?php

namespace App\Http\Requests\Web\Staking\Rules;

use App\Models\Staking\Staking;
use App\Repositories\Wallet\WalletRepository;
use Illuminate\Contracts\Validation\Rule;

class StakingAmountRule implements Rule
{
    public $error = '';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $amount)
    {
        $staking = Staking::where('id', request()->get('id'))->active()->first();

        if(!$staking) {
            $this->error = 'Staking is not active';
            return false;
        }

        if(math_compare($amount, $staking->min_amount) === -1) {
            $this->error = 'Amount is less than minimum stake amount';
            return false;
        }

        if(math_compare($amount, $staking->max_amount) === 1) {
            $this->error = 'Amount is more than maximum stake amount';
            return false;
        }

        $user = auth()->user();

        $wallet = (new WalletRepository())->getWalletByCurrency($user->id, $staking->currency->id, false);

        if(math_compare($wallet->balance_in_wallet, $amount) === -1) {
            $this->error = 'Insufficient balance';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __($this->error);
    }
}
