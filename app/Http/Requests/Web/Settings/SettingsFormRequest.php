<?php

namespace App\Http\Requests\Web\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class SettingsFormRequest extends FormRequest
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
            'general.name' => ['sometimes', 'required', 'max:1000'],
            'general.logo' => ['sometimes', 'required', 'max:1000'],
            'general.kyc_status' => ['sometimes', 'required', 'boolean'],
            'general.maintenance_status' => ['sometimes', 'required', 'boolean'],
            'general.language_status' => ['sometimes', 'required', 'boolean'],
            'general.default_dark_mode_status' => ['sometimes', 'required', 'boolean'],
            'general.swap_market' => ['sometimes', 'max:30'],
            'general.withdrawal_limit' => ['sometimes', 'max:50'],
            'general.withdrawal_limit_kyc' => ['sometimes', 'max:50'],
            'general.dark_mode_status' => ['sometimes', 'required', 'boolean'],

            'trade.maker_fee' => ['sometimes', 'required', 'numeric', 'gte:0', 'max: 100'],
            'trade.taker_fee' => ['sometimes', 'required', 'numeric', 'gte:0', 'max: 100'],

            'mail.mail_encryption' => ['sometimes', 'required', 'max:200'],
            'mail.mail_from_address' => ['sometimes', 'required', 'email', 'max:200'],
            'mail.mail_from_name' => ['sometimes', 'required', 'max:200'],
            'mail.mail_host' => ['sometimes', 'required', 'max:200'],
            'mail.mail_mailer' => ['sometimes', 'required', 'max:200'],
            'mail.mail_password' => ['sometimes', 'required', 'max:255'],
            'mail.mail_port' => ['sometimes', 'required', 'max:200'],
            'mail.mail_username' => ['sometimes', 'required', 'max:200'],

            'coinpayments.ipn_secret' => ['sometimes', 'required','max:1000'],
            'coinpayments.merchant_id' => ['sometimes', 'required', 'max:500'],
            'coinpayments.pay_deposit_fee' => ['sometimes', 'required', 'boolean'],
            'coinpayments.private_key' => ['sometimes', 'required', 'max:1200'],
            'coinpayments.public_key' => ['sometimes', 'required', 'max:1200'],

            'ethereum.private_key' => ['sometimes', 'required', 'max:255'],
            'ethereum.wallet' => ['sometimes', 'required', 'max:255'],

            'bnb.private_key' => ['sometimes', 'required', 'max:255'],
            'bnb.wallet' => ['sometimes', 'required', 'max:255'],

            'tron.private_key' => ['sometimes', 'required', 'max:255'],
            'tron.wallet' => ['sometimes', 'required', 'max:255'],

            'bitcoin.wallet' => ['sometimes', 'required', 'max:255'],

            'stripe.currency' => ['sometimes', 'required', 'min:3', 'max:25'],
            'stripe.public_key' => ['sometimes', 'required', 'max:1000'],
            'stripe.secret_key' => ['sometimes', 'required', 'max:1000'],

            'recaptcha.secret_key' => ['sometimes', 'required', 'max:1000'],
            'recaptcha.site_key' => ['sometimes', 'required', 'max:1000'],
            'recaptcha.status' => ['sometimes', 'required', 'boolean'],

            'notification.admin_email' => ['sometimes', 'required', 'email', 'max:100'],
            'notification.crypto_deposits' => ['sometimes', 'required', 'boolean'],
            'notification.crypto_withdrawals' => ['sometimes', 'required', 'boolean'],
            'notification.fiat_deposits' => ['sometimes', 'required', 'boolean'],
            'notification.fiat_withdrawals' => ['sometimes', 'required', 'boolean'],
            'notification.kyc_received' => ['sometimes', 'required', 'boolean'],
            'notification.new_user_registered' => ['sometimes', 'required', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'general.name' => 'Site name',
            'general.logo' => 'Site logo',

            'trade.maker_fee' => 'Maker Fee',
            'trade.taker_fee' => 'Take Fee',

            'mail.mail_encryption' => 'Mail Encryption',
            'mail.mail_from_address' => 'Mail Address',
            'mail.mail_from_name' => 'Mail Name',
            'mail.mail_host' => 'Mail Host',
            'mail.mail_mailer' => 'Mail Type',
            'mail.mail_password' => 'Mail Password',
            'mail.mail_port' => 'Mail Port',
            'mail.mail_username' => 'Mail Username',

            'coinpayments.ipn_secret' => 'IPN Secret',
            'coinpayments.merchant_id' => 'Merchant ID',
            'coinpayments.private_key' => 'Private Key',
            'coinpayments.public_key' => 'Public Key',

            'ethereum.private_key' => 'Private Key',
            'ethereum.wallet' => 'Wallet',

            'bnb.private_key' => 'Private Key',
            'bnb.wallet' => 'Wallet',

            'tron.private_key' => 'Private Key',
            'tron.wallet' => 'Wallet',

            'stripe.currency' => 'Base Currency',
            'stripe.public_key' => 'Public Key',
            'stripe.secret_key' => 'Private Key',

            'recaptcha.secret_key' => 'Secret Key',
            'recaptcha.site_key' => 'Site Key',
            'recaptcha.status' => 'Status',

            'notification.admin_email' => "Admin Email",
            'notification.crypto_deposits' => "Crypto Deposits",
            'notification.crypto_withdrawals' => "Crypto Withdrawals",
            'notification.fiat_deposits' => "Fiat Deposits",
            'notification.fiat_withdrawals' => "Fiat Withdrawals",
            'notification.kyc_received' => "KYC Received",
            'notification.new_user_registered' => "New User Registered",
        ];
    }
}
