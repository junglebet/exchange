<?php

namespace App\Http\Requests\Api\Wallet;

use App\Http\Requests\Api\Wallet\Rules\WalletNetworkRule;
use App\Http\Requests\Api\Wallet\Rules\WalletSymbolRule;
use App\Http\Requests\Api\Wallet\Rules\WalletWithdrawAmountRule;
use App\Http\Requests\Web\Wallet\Rules\WithdrawAddressValidationRule;
use App\Repositories\Currency\CurrencyRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\RequiredIf;

class WithdrawRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $currency = (new CurrencyRepository())->getCurrencyBySymbol(request()->get('symbol'));

        return [
            'symbol' => ['bail', 'required', new WalletSymbolRule()],
            'address' => ['bail', 'required', 'max:255', new WithdrawAddressValidationRule()],
            'network' => ['bail', 'required', new WalletNetworkRule()],
            'payment_id' => ['bail', new RequiredIf($currency && $currency->has_payment_id)],
            'amount' => ['bail', 'required', 'numeric', 'gt:0', new WalletWithdrawAmountRule() ],
        ];
    }
}
