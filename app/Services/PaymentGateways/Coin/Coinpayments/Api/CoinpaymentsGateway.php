<?php
namespace App\Services\PaymentGateways\Coin\Coinpayments\Api;

use App\Interfaces\PaymentsGateways\Coin\CoinGatewayInterface;
use App\Models\CoinpaymentsCoin;
use App\Services\PaymentGateways\Coin\Coinpayments\Model\CoinpaymentsCurrency;
use Illuminate\Support\Facades\Log;

class CoinpaymentsGateway implements CoinGatewayInterface
{
    /**
     * Used to call request to Coinpayments API
     *
     * @param $uri
     * @param array $params
     * @return false|mixed
     */
    public function request($uri, $params = [])
    {
        try {

            return get_coinpayments_request($uri, $params);

        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }
    }

    /**
     * Used to sync all available coins with ipn feature support
     */
    public function syncAvailableCoins() {
        try {

            // Get currencies
            $currencies = $this->request('rates');

            // Remove all currencies before sync
            if($currencies->result) {
                CoinpaymentsCurrency::truncate();
            }

            // Iterate each currency to store
            foreach ($currencies->result as $name=>$currency) {

                // If it is fiat skip
                if($currency->is_fiat) continue;

                // If it is not used for payments skip it
                if(!in_array('payments', $currency->capabilities)) continue;

                $hasPaymentId = in_array('dest_tag', $currency->capabilities);

                // Create or update currency
                CoinpaymentsCurrency::updateOrCreate(
                    ['symbol' => $name],
                    [
                        'name' => $currency->name,
                        'symbol' => $name,
                        'fee' => $currency->tx_fee,
                        'confirmations' => $currency->confirms,
                        'status' => $currency->status,
                        'blockchain_url' => isset($currency->explorer) ? str_replace('?from=coinpayments', '', $currency->explorer) : null,
                        'has_payment_id' => $hasPaymentId
                    ]
                );
            }

        } catch (\Exception $exception) {
            Log::error($exception);
        }
    }

    /**
     * Used to generate address wallet per currency
     * @param $symbol
     * @return array
     */
    public function createAddress($symbol) {

        // Define ipn url
        $ipn = route('coinpayments.ipn');

        // Get new wallet address
        $wallet = $this->request('get_callback_address', [
            'currency' => $symbol,
            'ipn_url' => $ipn
        ]);

        // If new wallet created return wallet
        if(isset($wallet->result->address)) {
            return [
                'address' => $wallet->result->address,
                'dest_tag' => $wallet->result->dest_tag ?? null,
            ];
        }

        // Return empty address
        return [];
    }

    /**
     * Used to generate data to withdraw
     * @param $address
     * @param $paymentId
     * @param $amount
     * @param $currency
     * @return array
     */
    public function withdraw($address, $paymentId, $amount, $currency)
    {
        $params = [
            'ipn_url' => route('coinpayments.ipn'),
            'address' => $address,
            'amount' => $amount,
            'currency' => $currency->alt_symbol,
            'auto_confirm' => COINPAYMENTS_AUTO_CONFIRM,
            'add_tx_fee' => COINPAYMENTS_AUTO_CONFIRM,
        ];

        if ($paymentId) {
            $params['dest_tag'] = $paymentId;
        }

        $response = $this->request('create_withdrawal', $params);

        $status = (isset($response->error) && $response->error !== "ok") ? STATUS_VALIDATION_ERROR : STATUS_OK;

        $message = $response;
        $source = '';

        if($status == STATUS_VALIDATION_ERROR) {
            $message = $response->error;
        } else {
            $source = $response->result->id;
        }

        return [
            'status' => $status,
            'source' => $source,
            'message' => $message,
        ];
    }

    /**
     * Used to get system wallet balance
     * @return array
     */
    public function getBalance()
    {
        return $this->request('balances');
    }
}
