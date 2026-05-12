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
];
