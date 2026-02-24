<?php

namespace App\Services\Settings;

use App\Services\PaymentGateways\Coin\Ethereum\Api\EthereumGateway;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Setting;

class SettingsService {

    /**
     *
     * @param $key
     * @param $data
     * @return void
     */
    public function updateBatch($key, $data)
    {
        foreach ($data as $field => $value) {
            $this->update($field, $value, get_settings_by_name($key));
        }

        Artisan::queue('horizon:terminate');
    }

    /**
     *
     * @param $key
     * @param $data
     * @return void
     */
    public function update($key, $value, $source)
    {
        $location = $source[$key]['location'];
        $field_key = $source[$key]['key'];

        if($location == "file") {
            update_settings_from_env($field_key, $value);
        }

        if($location == "database") {
           Setting::set($field_key, $value);
        }
    }

    public function getSettings($key) {

        if($key == "trade") {
            $settings = Setting::get('trade');
            return [
                'taker_fee' => $settings['taker_fee'] ?? INITIAL_TRADE_TAKER_FEE,
                'maker_fee' => $settings['maker_fee'] ?? INITIAL_TRADE_MAKER_FEE,
                'referral_fee' => $settings['referral_fee'] ?? INITIAL_REFERRAL_FEE,
                'disable_trades' => isset($settings['disable_trades']) && $settings['disable_trades'] == 1,
            ];
        }

        if($key == "mail") {
            return [
                'mail_from_name' => env('MAIL_FROM_NAME'),
                'mail_from_address' => env('MAIL_FROM_ADDRESS'),
                'mail_mailer' => env('MAIL_MAILER'),
                'mail_host' => env('MAIL_HOST'),
                'mail_port' => env('MAIL_PORT'),
                'mail_username' => env('MAIL_USERNAME'),
                'mail_password' => env('MAIL_PASSWORD'),
                'mail_encryption' => env('MAIL_ENCRYPTION')
            ];
        }

        if($key == "coinpayments") {
            $settings = Setting::get('coinpayments');
            return [
                'public_key' => $settings['public_key'] ?? '',
                'private_key' => $settings['private_key'] ?? '',
                'merchant_id' => $settings['merchant_id'] ?? '',
                'ipn_secret' => $settings['ipn_secret'] ?? '',
                'pay_deposit_fee' => isset($settings['pay_deposit_fee']) && $settings['pay_deposit_fee'] == 1,
            ];
        }

        if($key == "general") {
            $settings = Setting::get('general');
            return [
                'name' => env('APP_NAME'),
                'logo' => $settings['logo'] ?? '',
                'kyc_status' => isset($settings['kyc_status']) && $settings['kyc_status'] == 1,
                'maintenance_status' => isset($settings['maintenance_status']) && $settings['maintenance_status'] == 1,
                'language_status' => isset($settings['language_status']) && $settings['language_status'] == 1,
                'default_dark_mode_status' => isset($settings['default_dark_mode_status']) && $settings['default_dark_mode_status'] == 1,
                'dark_mode_status' => isset($settings['dark_mode_status']) && $settings['dark_mode_status'] == 1,
                'swap_market' => $settings['swap_market'] ?? '',
                'withdrawal_limit' => $settings['withdrawal_limit'] ?? 0,
                'withdrawal_limit_kyc' => $settings['withdrawal_limit_kyc'] ?? 0,
            ];
        }

        if($key == "ethereum") {
            $settings = Setting::get('ethereum');
            return [
                'wallet' => $settings['wallet'] ?? '',
                'private_key' => $settings['private_key'] ?? '',
            ];
        }

        if($key == "bnb") {
            $settings = Setting::get('bnb');
            return [
                'wallet' => $settings['wallet'] ?? '',
                'private_key' => $settings['private_key'] ?? '',
            ];
        }

        if($key == "polygon") {
            $settings = Setting::get('polygon');
            return [
                'wallet' => $settings['wallet'] ?? '',
                'private_key' => $settings['private_key'] ?? '',
            ];
        }

        if($key == "tron") {
            $settings = Setting::get('tron');
            return [
                'wallet' => $settings['wallet'] ?? '',
                'private_key' => $settings['private_key'] ?? '',
            ];
        }

        if($key == "bitcoin") {

            $generatedAddress = '';
            $settings = Setting::get('bitcoin');

            return [
                'wallet' => $settings['wallet'] ?? $generatedAddress,
            ];
        }

        if($key == "stripe") {
            $settings = Setting::get('stripe');
            return [
                'public_key' => $settings['public_key'] ?? '',
                'secret_key' => $settings['secret_key'] ?? '',
                'currency' => $settings['currency'] ?? 'usd',
            ];
        }

        if($key == "recaptcha") {
            $settings = Setting::get('recaptcha');
            return [
                'site_key' => env('NOCAPTCHA_SITEKEY'),
                'secret_key' => env('NOCAPTCHA_SECRET'),
                'status' => isset($settings['status']) && $settings['status'] == 1,
            ];
        }

        if($key == "notification") {
            $settings = Setting::get('notification');
            return [
                'admin_email' => $settings['admin_email'] ?? '',
                'crypto_deposits' => isset($settings['crypto_deposits']) && $settings['crypto_deposits'] == 1,
                'crypto_withdrawals' => isset($settings['crypto_withdrawals']) && $settings['crypto_withdrawals'] == 1,
                'fiat_deposits' => isset($settings['fiat_deposits']) && $settings['fiat_deposits'] == 1,
                'fiat_withdrawals' => isset($settings['fiat_withdrawals']) && $settings['fiat_withdrawals'] == 1,
                'kyc_received' => isset($settings['kyc_received']) && $settings['kyc_received'] == 1,
                'new_user_registered' => isset($settings['new_user_registered']) && $settings['new_user_registered'] == 1,
            ];
        }

        if($key == "social") {
            $settings = Setting::get('social');
            return [
                'youtube' => $settings['youtube'] ?? '',
                'telegram' => $settings['telegram'] ?? '',
                'facebook' => $settings['facebook'] ?? '',
                'twitter' => $settings['twitter'] ?? '',
                'reddit' => $settings['reddit'] ?? '',
                'instagram' => $settings['instagram'] ?? '',
                'medium' => $settings['medium'] ?? '',
                'vk' => $settings['vk'] ?? '',
                'discord' => $settings['discord'] ?? '',
                'coinmarketcap' => $settings['coinmarketcap'] ?? '',
                'github' => $settings['github'] ?? '',
                'linkedin' => $settings['linkedin'] ?? '',
            ];
        }
    }

    public function getCaptchaStatus() {

        $status = setting('recaptcha.status', false);

        $site_key = config('captcha.sitekey');
        $site_secret = config('captcha.secret');

        return $status && $site_key && $site_secret;

    }
}
