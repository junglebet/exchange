<?php

namespace App\Http\Requests\Web\Page;

use App\Http\Requests\Web\Currency\Rules\CurrencyNetworkRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyStatusRule;
use App\Http\Requests\Web\Currency\Rules\CurrencySymbolRule;
use App\Http\Requests\Web\Currency\Rules\CurrencyTypeRule;
use Illuminate\Foundation\Http\FormRequest;
use Auth;
use Illuminate\Validation\Rule;

class PageFormRequest extends FormRequest
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
            'id' => ['sometimes', 'required', 'numeric', 'integer','exists:pages'],
            'title' => ['bail', 'required', 'max:255', 'min:1'],
            'slug' => ['bail', 'required', 'max:100', Rule::unique('pages')->ignore(request()->get('id', false), 'id')],
            'content' => ['bail', 'required', 'max:4294965000'],
            'status' => ['required', 'boolean'],
            'seo_title' => ['max:255'],
            'seo_description' => ['max:1000'],
            'seo_keywords' => ['max:500'],
        ];
    }
}
