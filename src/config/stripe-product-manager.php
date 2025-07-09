<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains the Stripe API configuration settings.
    |
    */

    'stripe' => [
        'secret_key' => env('STRIPE_SECRET'),
        'publishable_key' => env('STRIPE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'api_version' => env('STRIPE_API_VERSION', '2024-06-20'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains database table name configurations.
    |
    */

    'database' => [
        'prefix' => env('STRIPE_PRODUCT_MANAGER_DB_PREFIX', 'stripe_'),
        'tables' => [
            'products' => 'stripe_products',
            'prices' => 'stripe_prices',
            'customers' => 'stripe_customers',
            'invoices' => 'stripe_invoices',
            'transactions' => 'stripe_transactions',
            'coupons' => 'stripe_coupons',
            'discounts' => 'stripe_discounts',
            'promotion_codes' => 'stripe_promotion_codes',
            'tax_codes' => 'stripe_tax_codes',
            'tax_rates' => 'stripe_tax_rates',
            'sync_logs' => 'stripe_sync_logs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains synchronization settings for Stripe data.
    |
    */

    'sync' => [
        'enabled' => env('STRIPE_SYNC_ENABLED', true),
        'batch_size' => env('STRIPE_SYNC_BATCH_SIZE', 100),
        'retry_attempts' => env('STRIPE_SYNC_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('STRIPE_SYNC_RETRY_DELAY', 5), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains Filament admin panel configurations.
    |
    */

    'filament' => [
        'enabled' => env('STRIPE_FILAMENT_ENABLED', true),
        'panel' => env('STRIPE_FILAMENT_PANEL', 'admin'),
        'resources' => [
            'products' => true,
            'prices' => true,
            'customers' => true,
            'invoices' => true,
            'transactions' => true,
            'coupons' => true,
            'discounts' => true,
            'promotion_codes' => true,
            'tax_codes' => true,
            'tax_rates' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains logging settings for the package.
    |
    */

    'logging' => [
        'enabled' => env('STRIPE_LOGGING_ENABLED', true),
        'channel' => env('STRIPE_LOGGING_CHANNEL', 'daily'),
        'level' => env('STRIPE_LOGGING_LEVEL', 'info'),
    ],
];
