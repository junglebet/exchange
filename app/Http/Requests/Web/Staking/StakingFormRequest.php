<?php

namespace App\Http\Requests\Web\Staking;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class StakingFormRequest extends FormRequest
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
            'id' => ['sometimes', 'required', 'numeric', 'integer','exists:staking'],
            'currency_id' => ['bail', 'required', 'exists:currencies,id', 'unique:staking,currency_id,' . (request()->get('id') ?? 0)],
            'allowed_days' => ['bail', 'required', 'max:255'],
            'rewards_percentage' => ['bail', 'required', 'max:255'],
            'min_amount' => ['bail', 'required', 'numeric', 'gte:0'],
            'max_amount' => ['bail', 'required', 'numeric', 'gte:0'],
            'status' => ['bail', 'required', 'max:20'],
        ];
    }
}
