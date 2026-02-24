<?php

namespace App\Http\Requests\Web\Wallet\Rules;

use App\Models\FileUpload\FileUpload;
use App\Models\Wallet\WalletAddress;
use App\Repositories\Currency\CurrencyRepository;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;

class WithdrawAddressValidationRule implements Rule
{
    public $errorMessage = 'Invalid wallet address';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $address)
    {
        $network = request()->get('network');

        if(in_array($network, get_evm_networks()) && !preg_match('/^(0x)?(?i:[0-9a-f]){40}$/', $address)) {
            return false;
        }

        if(WalletAddress::where('address', mb_strtolower($address))->where('user_id', auth()->user()->getAuthIdentifier())->first()) {
            $this->errorMessage = 'The provided wallet address is your deposit address.';
            return false;
        }

        if(in_array($network, [NETWORK_BRC20, NETWORK_BTC])) {

            try {
                $response = bitcoind()->validateaddress($address)->get();

                if(!$response['isvalid']) {
                    return false;
                }

            } catch (\Exception $e) {
                Log::info('Can not connect to Bitcoin Node');
                $this->errorMessage = 'Address validation service is not available.';
                return false;
            }


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
        return __($this->errorMessage);
    }
}

