# Stripe Product Manager

A comprehensive Laravel package for managing Stripe products, prices, customers, invoices, and transactions with Filament admin integration.

## Installation

You can install the package via composer:

```bash
composer require fullstack/stripe-product-manager
```

**Note:** This package requires the Spatie Laravel Permission package. If you haven't installed it yet, you'll need to:

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

## Publishing Assets

The package provides several publishing groups for different types of assets:

### Publish Everything
```bash
php artisan vendor:publish --provider="Fullstack\StripeProductManager\StripeProductManagerServiceProvider"
```

### Publish Specific Assets

**Configuration only:**
```bash
php artisan vendor:publish --provider="Fullstack\StripeProductManager\StripeProductManagerServiceProvider" --tag="stripe-product-manager-config"
```

**Migrations only:**
```bash
php artisan vendor:publish --provider="Fullstack\StripeProductManager\StripeProductManagerServiceProvider" --tag="stripe-product-manager-migrations"
```

**Seeders only:**
```bash
php artisan vendor:publish --provider="Fullstack\StripeProductManager\StripeProductManagerServiceProvider" --tag="stripe-product-manager-seeders"
```

## Configuration

After publishing the configuration file, you can configure the package in `config/stripe-product-manager.php`:

```php
return [
    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'api_version' => env('STRIPE_API_VERSION', '2024-06-20'),
    ],

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

    'sync' => [
        'enabled' => env('STRIPE_SYNC_ENABLED', true),
        'batch_size' => env('STRIPE_SYNC_BATCH_SIZE', 100),
        'retry_attempts' => env('STRIPE_SYNC_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('STRIPE_SYNC_RETRY_DELAY', 5),
    ],

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

    'logging' => [
        'enabled' => env('STRIPE_LOGGING_ENABLED', true),
        'channel' => env('STRIPE_LOGGING_CHANNEL', 'daily'),
        'level' => env('STRIPE_LOGGING_LEVEL', 'info'),
    ],
];
```

## Environment Variables

Add these to your `.env` file:

```env
# Stripe Configuration
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_API_VERSION=2024-06-20

# Package Configuration
STRIPE_PRODUCT_MANAGER_DB_PREFIX=stripe_
STRIPE_SYNC_ENABLED=true
STRIPE_SYNC_BATCH_SIZE=100
STRIPE_SYNC_RETRY_ATTEMPTS=3
STRIPE_SYNC_RETRY_DELAY=5

# Filament Integration
STRIPE_FILAMENT_ENABLED=true
STRIPE_FILAMENT_PANEL=admin

# Logging
STRIPE_LOGGING_ENABLED=true
STRIPE_LOGGING_CHANNEL=daily
STRIPE_LOGGING_LEVEL=info
```

## Database Setup

Run the migrations to create the necessary database tables:

```bash
php artisan migrate
```

## Permissions Setup

After running migrations, seed the permissions and roles:

```bash
php artisan db:seed --class="Fullstack\StripeProductManager\Database\Seeders\StripeProductManagerPermissionSeeder"
```

This will create the following roles and permissions:

### Roles
- **stripe-admin** - Full access to all Stripe functionality
- **stripe-manager** - Can manage products, prices, customers, invoices, and basic operations
- **stripe-viewer** - Read-only access to view Stripe data

### Guards
The package supports three authentication guards:
- **web** - Standard web authentication
- **tenant-admin** - Tenant-specific admin authentication
- **super-admin** - Super admin authentication

Each guard has its own set of roles and permissions, allowing for multi-tenant and hierarchical access control.

### Permissions
The package creates granular permissions for each Stripe entity:
- `stripe.products.*` - Product management permissions
- `stripe.prices.*` - Price management permissions
- `stripe.customers.*` - Customer management permissions
- `stripe.invoices.*` - Invoice management permissions
- `stripe.transactions.*` - Transaction management permissions
- `stripe.coupons.*` - Coupon management permissions
- `stripe.discounts.*` - Discount management permissions
- `stripe.promotion_codes.*` - Promotion code management permissions
- `stripe.tax_codes.*` - Tax code management permissions
- `stripe.tax_rates.*` - Tax rate management permissions
- `stripe.admin.*` - Administrative permissions

### Using Permissions in Your User Model

Add the `HasStripePermissions` trait to your User model:

```php
use Fullstack\StripeProductManager\Traits\HasStripePermissions;

class User extends Authenticatable
{
    use HasStripePermissions;

    // ... rest of your User model
}
```

Then you can use the permission methods:

```php
// Check permissions
if ($user->canViewStripeProducts()) {
    // User can view products
}

if ($user->canCreateStripeProducts()) {
    // User can create products
}

// Check roles
if ($user->isStripeAdmin()) {
    // User is a Stripe admin
}

if ($user->hasStripeRole()) {
    // User has any Stripe role
}

// Guard-specific checks
if ($user->isStripeAdminForTenant()) {
    // User is a Stripe admin for tenant-admin guard
}

if ($user->isStripeAdminForSuper()) {
    // User is a Stripe admin for super-admin guard
}

if ($user->canViewStripeProductsForTenant()) {
    // User can view products for tenant-admin guard
}
```

### Using Middleware

The package provides middleware for guard-based authentication:

```php
// In your routes file
Route::middleware(['stripe.guard:tenant-admin'])->group(function () {
    Route::get('/stripe/products', [StripeController::class, 'index']);
});

Route::middleware(['stripe.guard:super-admin'])->group(function () {
    Route::get('/stripe/admin', [StripeAdminController::class, 'index']);
});
```

Or in your controllers:

```php
public function __construct()
{
    $this->middleware('stripe.guard:tenant-admin');
}
```

## Usage

### Models

The package provides several Eloquent models for Stripe entities:

- `StripeProduct` - Stripe products
- `StripePrice` - Product pricing
- `StripeCustomer` - Customer data
- `StripeInvoice` - Invoice information
- `StripeTransaction` - Transaction data
- `StripeCoupon` - Coupon management
- `StripeDiscount` - Discount tracking
- `StripePromotionCode` - Promotion codes
- `StripeTaxCode` - Tax code definitions
- `StripeTaxRate` - Tax rate information

### Filament Integration

The package includes Filament resources for all Stripe entities. These will be automatically registered if Filament is installed and the `STRIPE_FILAMENT_ENABLED` option is set to `true`.

### Console Commands

The package will include console commands for syncing data with Stripe (to be implemented):

```bash
# Sync products from Stripe
php artisan stripe:sync-products

# Sync customers from Stripe
php artisan stripe:sync-customers

# Sync invoices from Stripe
php artisan stripe:sync-invoices
```

## Features

- ✅ Complete Stripe entity management
- ✅ Filament admin panel integration
- ✅ Database synchronization
- ✅ Webhook handling
- ✅ Tax management
- ✅ Coupon and promotion code support
- ✅ Invoice and transaction tracking
- ✅ Customer management
- ✅ Configurable database prefixes
- ✅ Comprehensive logging
- ✅ Role-based access control with Spatie permissions
- ✅ Granular permissions for each Stripe entity
- ✅ Pre-configured roles (Admin, Manager, Viewer)
- ✅ Permission trait for easy integration
- ✅ Multi-guard support (web, tenant-admin, super-admin)
- ✅ Guard-specific permission methods
- ✅ Middleware for guard authentication

## Support

For support, please contact [david.church@fullstackllc.net](mailto:david.church@fullstackllc.net)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
