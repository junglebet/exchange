<?php

namespace App\Http\Requests\Web\Article;

use App\Http\Requests\Web\Currency\Rules\CurrencyAltSymbolRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyBankAccountRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyBankStatusRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyBepContractRule;
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

class ArticleFormRequest extends FormRequest
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
            'id' => ['sometimes', 'required', 'exists:articles'],
            'title' => ['bail', 'required', 'max:254'],
            'language' => ['bail', 'required', 'exists:languages,id'],
            'file_id' => ['required', 'numeric', 'exists:file_uploads,id'],
            'category_id' => ['bail', 'required'],
            'slug' => ['bail', 'required', 'max:100', Rule::unique('articles')->ignore(request()->get('id', false), 'id')],
            'body' => ['bail', 'required', 'max:30000'],
            'status' => ['required', 'boolean'],
        ];
    }
}
