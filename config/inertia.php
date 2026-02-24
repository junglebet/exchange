<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inertia SSR
    |--------------------------------------------------------------------------
    |
    | If you are not running a Node SSR server, keep this disabled.
    |
    */

    'ssr' => [
        'enabled' => env('INERTIA_SSR_ENABLED', true),

        // Only used when enabled:
        'url' => env('INERTIA_SSR_URL', 'http://127.0.0.1:13714'),

        // Only used when enabled:
        'bundle' => env('INERTIA_SSR_BUNDLE', base_path('bootstrap/ssr/ssr.js')),
    ],

];