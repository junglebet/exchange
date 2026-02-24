<?php

namespace App\Http\Requests\Web\ColdStorage;

use App\Http\Requests\Web\ColdStorage\Rules\ColdStorageCreateRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyNetworkRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyStatusRule;
use App\Http\Requests\Web\Currency\Rules\CurrencySymbolRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyTypeRule;
use Illuminate\Foundation\Http\FormRequest;
use Auth;
use Illuminate\Validation\Rule;

class ColdStorageFormRequest extends FormRequest
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
            'id' => ['sometimes', 'integer', 'numeric', 'required', 'exists:cold_storage'],
            'status' => ['required', 'boolean'],
            'currency_id' => ['required', 'integer', 'numeric', 'required', 'exists:currencies,id', new ColdStorageCreateRule()],
            'network_id' => ['required', 'integer', 'numeric'],
            'address' => ['required', 'max:255', 'min:15'],
            'cold_min_balance_amount' => ['required', 'numeric'],
            'cold_transfer_amount' => ['required', 'numeric', 'lte:cold_min_balance_amount'],
        ];
    }
}
