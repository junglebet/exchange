<?php

const DB_REPEAT_AFTER_DEADLOCK = 5;

// Plain Utility Helper Functions

use Illuminate\Support\Str;

/*
 * Generate unique uuid
 */
if (!function_exists('generate_uuid')) {
    function generate_uuid()
    {
        return Str::uuid();
    }
}

/*
 * Generate unique uuid
 */
if (!function_exists('generate_string')) {
    function generate_string()
    {
        return mb_strtoupper(Str::random(15));
    }
}

/*
 * Get app prefix
 */
if (!function_exists('is_mobile_instance')) {
    function is_mobile_instance()
    {
        return false;
        $agent = new \Jenssegers\Agent\Agent;
        return $agent->isMobile();
    }
}

/*
 * Get EVN Networks
 */
if (!function_exists('get_evm_networks')) {
    function get_evm_networks()
    {
        return [NETWORK_ETH, NETWORK_ERC, NETWORK_BNB, NETWORK_BEP, NETWORK_MATIC, NETWORK_MATIC20];
    }
}
