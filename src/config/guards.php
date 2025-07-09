<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Guards Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains authentication guard configurations for different
    | admin levels.
    |
    */
    'super-admin' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'tenant-admin' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
];
