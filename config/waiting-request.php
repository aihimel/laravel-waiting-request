<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | This option defines the prefix that will be prepended to the cache keys
    | used by the package. This helps avoid key collisions with other
    | parts of your application.
    |
    */
    'cache_prefix' => env('LW_REQUEST_CACHE_PREFIX', 'lw_request_'),

    /*
    |--------------------------------------------------------------------------
    | Default Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum time in seconds to wait for a blocker to be resolved
    | when using the whenResolved method.
    |
    */
    'timeout' => 5, // seconds

    /*
    |--------------------------------------------------------------------------
    | Check Interval
    |--------------------------------------------------------------------------
    |
    | The time in milliseconds to wait between each check for the blocker
    | to be resolved.
    |
    */
    'check_interval' => 250, // milliseconds
];
