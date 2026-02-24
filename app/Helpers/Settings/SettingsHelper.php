<?php

const GENERAL_SETTINGS = [
    'name' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'APP_NAME'
    ],
    'swap_market' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'general.swap_market'
    ],
    'withdrawal_limit' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'general.withdrawal_limit'
    ],
    'withdrawal_limit_kyc' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'general.withdrawal_limit_kyc'
    ],
    'logo' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'general.logo'
    ],
    'kyc_status' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'general.kyc_status'
    ],
    'maintenance_status' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'general.maintenance_status'
    ],
    'default_dark_mode_status' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'general.default_dark_mode_status'
    ],
    'dark_mode_status' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'general.dark_mode_status'
    ],
    'language_status' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'general.language_status'
    ],
];

const MAIL_SETTINGS = [
    'mail_from_name' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'MAIL_FROM_NAME'
    ],
    'mail_from_address' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'MAIL_FROM_ADDRESS'
    ],
    'mail_mailer' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'MAIL_MAILER'
    ],
    'mail_host' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'MAIL_HOST'
    ],
    'mail_port' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'MAIL_PORT'
    ],
    'mail_username' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'MAIL_USERNAME'
    ],
    'mail_password' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'MAIL_PASSWORD'
    ],
    'mail_encryption' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'MAIL_ENCRYPTION'
    ],
];

const COINPAYMENTS_SETTINGS = [
    'public_key' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'coinpayments.public_key'
    ],
    'private_key' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'coinpayments.private_key'
    ],
    'ipn_secret' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'coinpayments.ipn_secret'
    ],
    'merchant_id' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'coinpayments.merchant_id'
    ],
    'pay_deposit_fee' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'coinpayments.pay_deposit_fee'
    ],
];

const RECAPTCHA_SETTINGS = [
    'site_key' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'NOCAPTCHA_SITEKEY'
    ],
    'secret_key' => [
        'type' => 'string',
        'location' => 'file',
        'key' => 'NOCAPTCHA_SECRET'
    ],
    'status' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'recaptcha.status'
    ],
];

const TRADE_SETTINGS = [
    'taker_fee' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'trade.taker_fee'
    ],
    'maker_fee' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'trade.maker_fee'
    ],
    'referral_fee' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'trade.referral_fee'
    ],
    'disable_trades' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'trade.disable_trades'
    ],
];

const ETHEREUM_SETTINGS = [
    'wallet' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'ethereum.wallet'
    ],
    'private_key' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'ethereum.private_key'
    ],
];

const BNB_SETTINGS = [
    'wallet' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'bnb.wallet'
    ],
    'private_key' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'bnb.private_key'
    ],
];

const POLYGON_SETTINGS = [
    'wallet' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'polygon.wallet'
    ],
    'private_key' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'polygon.private_key'
    ],
];

const TRON_SETTINGS = [
    'wallet' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'tron.wallet'
    ],
    'private_key' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'tron.private_key'
    ],
];

const STRIPE_SETTINGS = [
    'public_key' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'stripe.public_key'
    ],
    'secret_key' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'stripe.secret_key'
    ],
    'currency' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'stripe.currency'
    ],
];

const NOTIFICATION_SETTINGS = [
    'admin_email' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'notification.admin_email'
    ],
    'crypto_deposits' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'notification.crypto_deposits'
    ],
    'crypto_withdrawals' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'notification.crypto_withdrawals'
    ],
    'fiat_deposits' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'notification.fiat_deposits'
    ],
    'fiat_withdrawals' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'notification.fiat_withdrawals'
    ],
    'kyc_received' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'notification.kyc_received'
    ],
    'new_user_registered' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'notification.new_user_registered'
    ],
];

const SOCIAL_SETTINGS = [
    'youtube' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.youtube'
    ],
    'telegram' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.telegram'
    ],
    'facebook' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.facebook'
    ],
    'twitter' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.twitter'
    ],
    'reddit' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.reddit'
    ],
    'instagram' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.instagram'
    ],
    'medium' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.medium'
    ],
    'vk' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.vk'
    ],
    'discord' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.discord'
    ],
    'coinmarketcap' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.coinmarketcap'
    ],
    'github' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.github'
    ],
    'linkedin' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'social.linkedin'
    ],
];

const BITCOIN_SETTINGS = [
    'wallet' => [
        'type' => 'string',
        'location' => 'database',
        'key' => 'bitcoin.wallet'
    ],
];


if ( ! function_exists('get_settings_by_name')) {
    function get_settings_by_name($key)
    {
        switch ($key) {
            case 'general':
                return GENERAL_SETTINGS;
            case 'trade':
                return TRADE_SETTINGS;
            case 'mail':
                return MAIL_SETTINGS;
            case 'coinpayments':
                return COINPAYMENTS_SETTINGS;
            case 'ethereum':
                return ETHEREUM_SETTINGS;
            case 'bnb':
                return BNB_SETTINGS;
            case 'polygon':
                return POLYGON_SETTINGS;
            case 'tron':
                return TRON_SETTINGS;
            case 'recaptcha':
                return RECAPTCHA_SETTINGS;
            case 'stripe':
                return STRIPE_SETTINGS;
            case 'notification':
                return NOTIFICATION_SETTINGS;
            case 'social':
                return SOCIAL_SETTINGS;
            case 'bitcoin':
                return BITCOIN_SETTINGS;
        }
    }
}

if ( ! function_exists('update_settings_from_env'))
{
    function update_settings_from_env($key, $value)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $str .= "\n";
        $keyPosition = strpos($str, "{$key}=");
        $endOfLinePosition = strpos($str, PHP_EOL, $keyPosition);
        $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

        $forbiddenChars = ['"','\\', '\''];
        foreach ($forbiddenChars as $char) {
            $value = str_replace("$char", '', $value);
        }
        $value = htmlspecialchars_decode(strip_tags($value));

        $str = str_replace($oldLine, "{$key}='{$value}'", $str);
        $str = substr($str, 0, -1);

        $fp = fopen($envFile, 'w');
        fwrite($fp, $str);
        fclose($fp);
    }
}
