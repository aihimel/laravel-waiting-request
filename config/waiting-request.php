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
    'timeout' => env('LW_REQUEST_MAX_WAITING_TIME', 5), // seconds

    /*
    |--------------------------------------------------------------------------
    | Check Interval
    |--------------------------------------------------------------------------
    |
    | The time in milliseconds to wait between each check for the blocker
    | to be resolved.
    |
    */
    'check_interval' => env('LW_REQUEST_CHECK_INTERVAL', 250), // milliseconds

    /**
	 * Maximum blocking time for a blocker
	 * Time in seconds, this is the maximum duration that a blocker can be allowed
	 */
	'max_blocking_time' => env('LW_REQUEST_MAX_BLOCKING_TIME', 10),
];
