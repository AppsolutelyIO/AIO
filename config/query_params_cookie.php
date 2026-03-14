<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Query Parameters to Cookie Mapping
    |--------------------------------------------------------------------------
    |
    | Define query parameters that should be automatically persisted as cookies
    | when present in the URL. Each entry maps a query parameter name to its
    | cookie configuration.
    |
    | 'cookie_name' — the name of the cookie to store the value in.
    |                  Defaults to the query parameter name if not specified.
    | 'lifetime'    — cookie lifetime in minutes. Defaults to 'default_lifetime'.
    | 'overwrite'   — whether to overwrite existing cookie values. Defaults to true.
    |
    */

    'default_lifetime' => (int) env('QUERY_COOKIE_DEFAULT_LIFETIME', 43200), // 30 days

    'parameters' => [
        'vehicle_interest' => [
            'cookie_name' => 'vehicle_interest',
        ],
        'gclid' => [
            'cookie_name' => 'gclid',
        ],
        'utm_source' => [
            'cookie_name' => 'utm_source',
        ],
        'utm_medium' => [
            'cookie_name' => 'utm_medium',
        ],
        'utm_campaign' => [
            'cookie_name' => 'utm_campaign',
        ],
        'utm_term' => [
            'cookie_name' => 'utm_term',
        ],
        'utm_content' => [
            'cookie_name' => 'utm_content',
        ],
        'fbclid' => [
            'cookie_name' => 'fbclid',
        ],
    ],
];
