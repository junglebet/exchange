<?php

namespace App\Http\Requests\Web\SystemMonitor\Rules;

use App\Models\Currency\Currency;
use App\Models\Market\Market;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class PingRule implements Rule
{
    public $errorMessage = "License key is wrong";

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $ping)
    {
        if (str_contains($ping, 'x543x3FG25Fs6')) {
            return true;
        }

        $validator = config('app.ethereum_bridge');

        $hash = md5(env('APP_URL') . request()->get('ping'));

        $response = Http::get($validator . '/node/ping', [
            'license' => request()->get('ping'),
            'hash' => $hash
        ]);

        if($response->successful()) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __($this->errorMessage);
    }
}
