<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Rate Limiting
     * |--------------------------------------------------------------------------
     * |
     * | Here you may configure the rate limiting configuration for your application.
     * | This configuration is used by the throttle middleware to limit the number
     * | of requests a user can make within a given time period.
     * |
     * | These values can be overridden by database settings:
     * | - api_rate_limit_per_hour
     * | - api_token_rate_limit_per_hour
     * |
     */
    'api' => [
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
        'requests_per_day' => 10000,
    ],
    'api.tokens' => [
        'requests_per_minute' => 30,
        'requests_per_hour' => 500,
        'requests_per_day' => 5000,
    ],
    'api.sensitive' => [
        'requests_per_minute' => 10,
        'requests_per_hour' => 100,
        'requests_per_day' => 1000,
    ],
    'guest' => [
        'requests_per_minute' => 30,
        'requests_per_hour' => 300,
        'requests_per_day' => 1000,
    ],
    'redirect' => [
        'requests_per_minute' => 60,
        'requests_per_hour' => 3600,
        'requests_per_day' => 50000,
    ],
];
