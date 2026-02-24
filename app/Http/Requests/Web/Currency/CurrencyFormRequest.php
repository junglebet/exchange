<?php

namespace App\Http\Requests\Web\Currency;

use App\Http\Requests\Web\Currency\Rules\CurrencyAltSymbolRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyBankAccountRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyBankStatusRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyBepContractRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyMaticContractRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyTrcContractRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyCcExchangeRateRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyCcStatusRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyContractRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyNetworkRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyStatusRule;
use App\Http\Requests\Web\Currency\Rules\CurrencySymbolRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyTypeRule;
use Illuminate\Foundation\Http\FormRequest;
use Auth;
use Illuminate\Validation\Rule;

class CurrencyFormRequest extends FormRequest
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
        return [
            'id' => ['sometimes', 'required', 'exists:currencies'],
            'name' => ['bail', 'required', 'max:50'],
            'decimals' => ['bail', 'required', 'integer', 'numeric', 'gte:0', 'max:18'],
            'symbol' => ['bail', 'required', 'max:20', new CurrencySymbolRule()],
            'alt_symbol' => ['bail', 'max:30', new CurrencyAltSymbolRule()],
            'type' => ['bail', 'required', 'in:coin,fiat', 'max:10', new CurrencyTypeRule()],
            'networks' => ['bail', 'required', new CurrencyNetworkRule()],
            'status' => ['required', 'boolean', new CurrencyStatusRule()],
            'bank_account' => ['bail', 'exclude_if:type,coin', 'required_if:bank_status,true', new CurrencyBankAccountRule()],
            'bank_status' => ['bail', 'exclude_if:type,coin', 'required_if:cc_status,false', 'nullable', 'boolean', new CurrencyBankStatusRule()],
            'cc_status' => ['bail', 'exclude_if:type,coin', 'required_if:bank_status,false', 'nullable', 'boolean', new CurrencyCcStatusRule()],
            'cc_exchange_rate' => ['bail', 'exclude_if:type,coin', 'required_if:cc_status,true', 'nullable', 'numeric', 'max:999999', new CurrencyCcExchangeRateRule()],
            'file_id' => ['required', 'numeric', 'exists:file_uploads,id'],
            'deposit_status' => ['required', 'boolean'],
            'withdraw_status' => ['required', 'boolean'],


            'deposit_fee' => ['required', 'numeric', 'gte:0', 'max:100'],
            'deposit_fee_bep' => ['required', 'numeric', 'gte:0', 'max:100'],
            'deposit_fee_erc' => ['required', 'numeric', 'gte:0', 'max:100'],
            'deposit_fee_trc' => ['required', 'numeric', 'gte:0', 'max:100'],
            'deposit_fee_matic' => ['required', 'numeric', 'gte:0', 'max:100'],

            'deposit_fee_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'deposit_fee_bep_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'deposit_fee_erc_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'deposit_fee_trc_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'deposit_fee_matic_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],

            'withdraw_fee' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'withdraw_fee_bep' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'withdraw_fee_erc' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'withdraw_fee_trc' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'withdraw_fee_matic' => ['required', 'numeric', 'gte:0', 'max:1000000'],

            'withdraw_fee_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'withdraw_fee_bep_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'withdraw_fee_erc_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'withdraw_fee_trc_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],
            'withdraw_fee_matic_fixed' => ['required', 'numeric', 'gte:0', 'max:1000000'],



            'min_deposit' => ['required', 'numeric', 'gte:0', 'max:99999999999999999'],
            'max_deposit' => ['required', 'numeric', 'gte:0', 'max:99999999999999999'],
            'min_withdraw' => ['required', 'numeric', 'gte:0', 'max:99999999999999999'],
            'max_withdraw' => ['required', 'numeric', 'gte:0', 'max:99999999999999999'],
            'min_deposit_confirmation' => ['required', 'numeric', 'min:0', 'max:10000'],
            'contract' => ['bail', new CurrencyContractRule()],
            'bep_contract' => ['bail', new CurrencyBepContractRule()],
            'trc_contract' => ['bail', new CurrencyTrcContractRule()],
            'matic_contract' => ['bail', new CurrencyMaticContractRule()],
        ];
    }
}
