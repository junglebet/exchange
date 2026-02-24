<?php

// Constants
const COINPAYMENTS_DEPOSIT_CONFIRMED = 100;
const COINPAYMENTS_WITHDRAW_CONFIRMED = 2;
const COINPAYMENTS_WITHDRAW_FAILED = 0;
const COINPAYMENTS_AUTO_CONFIRM = 1;

// Plain Coinpayments Helper Functions

/*
 * Get Coinpayments base url
 */

use GuzzleHttp\Client;

if (!function_exists('get_coinpayments_base_url')) {
    function get_coinpayments_base_url()
    {
        return 'https://www.coinpayments.net/api.php';
    }
}

/*
 * Get Coinpayments content type
 */
if (!function_exists('get_coinpayments_request_content_type')) {
    function get_coinpayments_request_content_type()
    {
        return 'application/x-www-form-urlencoded';
    }
}

/*
 * Get Coinpayments signed hmac request
 */
if (!function_exists('get_coinpayments_hmac_request')) {
    function get_coinpayments_hmac_request($query, $key)
    {
        return hash_hmac('sha512', http_build_query($query), $key);
    }
}

/*
 * Get Coinpayments request
 */
if (!function_exists('get_coinpayments_request')) {
    function get_coinpayments_request($uri, $params)
    {
        $query = array_merge($params, [
            'cmd' => $uri,
            'key' => get_coinpayments_keys('public_key'),
            'format' => get_coinpayments_format(),
            'version' => get_coinpayments_api_version(),
        ]);

        return get_coinpayments_response((new Client())->post(get_coinpayments_base_url(), [
            'form_params' => $query,
            'headers' => [
                'Content-Type' => get_coinpayments_request_content_type(),
                'HMAC' => get_coinpayments_hmac_request($query, get_coinpayments_keys('private_key'))
            ]
        ]));
    }
}

/*
 * Get Coinpayments format
 */
if (!function_exists('get_coinpayments_format')) {
    function get_coinpayments_format()
    {
        return 'json';
    }
}

/*
 * Get Coinpayments Api Version
 */
if (!function_exists('get_coinpayments_api_version')) {
    function get_coinpayments_api_version()
    {
        return '1';
    }
}

/*
 * Get Coinpayments Response
 */
if (!function_exists('get_coinpayments_response')) {
    function get_coinpayments_response($request)
    {
        return json_decode($request->getBody()->getContents());
    }
}

/*
 * Get Coinpayments Keys
 */
if (!function_exists('get_coinpayments_keys')) {
    function get_coinpayments_keys($key)
    {
        $settings = setting('coinpayments');

        $keys = [
            'public_key' => $settings['public_key'] ?? null,
            'private_key' => $settings['private_key'] ?? null
        ];

        return $keys[$key];
    }
}

