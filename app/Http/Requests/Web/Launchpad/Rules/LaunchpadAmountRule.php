<?php

namespace App\Http\Requests\Web\Launchpad\Rules;

use App\Models\Launchpad\Launchpad;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Wallet\WalletRepository;
use Illuminate\Contracts\Validation\Rule;

class LaunchpadAmountRule implements Rule
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
        $launchpad = Launchpad::where('id', request()->get('id'))->where('status', 1)->where('purchasable', 1)->first();

        if(!$launchpad) {
            $this->error = 'Launchpad is not active';
            return false;
        }

        if(math_compare($amount, $launchpad->min_buy) === -1) {
            $this->error = 'Amount is less than minimum buy amount';
            return false;
        }

        if(math_compare($amount, $launchpad->max_buy) === 1) {
            $this->error = 'Amount is more than maximum buy amount';
            return false;
        }

        $symbol = $launchpad->network_id == NETWORK_BNB ? 'BNB' : 'ETH';

        $user = auth()->user();

        $currency = (new CurrencyRepository())->getCurrencyBySymbol($symbol);
        $wallet = (new WalletRepository())->getWalletByCurrency($user->id, $currency->id, false);

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
