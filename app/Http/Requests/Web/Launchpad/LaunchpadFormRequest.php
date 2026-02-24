<?php

namespace App\Http\Requests\Web\Launchpad;

use App\Http\Requests\Web\Launchpad\Rules\LaunchpadDecimalRule;
use Illuminate\Foundation\Http\FormRequest;
use Auth;

class LaunchpadFormRequest extends FormRequest
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
            'id' => ['sometimes', 'integer', 'numeric', 'required', 'exists:launchpads'],
            'name' => ['required', 'max:150'],
            'description' => ['required', 'max:10000'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'network_id' => ['required', 'exists:networks,id'],
            'rate' => ['required', new LaunchpadDecimalRule()],
            'min_buy' => ['required', new LaunchpadDecimalRule()],
            'max_buy' => ['required', new LaunchpadDecimalRule()],
            'soft_cap' => ['required', new LaunchpadDecimalRule()],
            'hard_cap' => ['required', new LaunchpadDecimalRule()],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date'],
            'status' => ['required', 'boolean'],
        ];
    }
}
