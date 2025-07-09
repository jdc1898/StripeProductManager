# Stripe Product Manager

A comprehensive Laravel package for managing Stripe products, prices, customers, and subscriptions with a beautiful Filament admin interface.

## Features

- 🛍️ **Product Management**: Create and manage Stripe products with multiple pricing tiers
- 💰 **Price Management**: Handle complex pricing strategies including recurring and one-time payments
- 👥 **Customer Management**: Manage customer data and subscription relationships
- 🔄 **Subscription Management**: Track and manage customer subscriptions
- 🎨 **Filament Admin Interface**: Beautiful, responsive admin panels for different user roles
- 🔐 **Role-Based Access Control**: Super admin, tenant admin, and member roles
- 📊 **Analytics Dashboard**: MRR tracking and subscription analytics
- 🔌 **Stripe Integration**: Seamless integration with Stripe API
- 🚀 **Easy Installation**: Simple artisan command for quick setup

## Installation

### Quick Install (Recommended)

The easiest way to install the package is using the provided artisan command:

```bash
php artisan stripe-product-manager:install
```

This command will:
- Install all required dependencies
- Update configuration files
- Publish assets and migrations
- Run migrations and seed permissions
- Update your User model with required traits
- Add environment variables to your .env file

### Manual Installation

If you prefer to install manually:

1. **Install the package via Composer:**
   ```bash
   composer require fullstack/stripe-product-manager
   ```

2. **Install required dependencies:**
   ```bash
   composer require spatie/laravel-permission stripe/stripe-php filament/filament
   ```

3. **Publish assets:**
   ```bash
   php artisan vendor:publish --provider="Fullstack\StripeProductManager\StripeProductManagerServiceProvider"
   ```

4. **Run migrations:**
   ```bash
   php artisan migrate
   ```

5. **Seed permissions:**
   ```bash
   php artisan db:seed --class="Fullstack\StripeProductManager\Database\Seeders\StripeProductManagerPermissionSeeder"
   ```

6. **Update your User model** to include the `HasStripePermissions` trait:
   ```php
   use Fullstack\StripeProductManager\Traits\HasStripePermissions;

   class User extends Authenticatable
   {
       use HasApiTokens, HasFactory, Notifiable, HasStripePermissions;
       // ...
   }
   ```

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```env
# Stripe Configuration
STRIPE_SECRET_KEY=sk_test_your_test_key_here
STRIPE_PUBLISHABLE_KEY=pk_test_your_test_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
STRIPE_API_VERSION=2024-06-20

# Package Configuration
STRIPE_PRODUCT_MANAGER_DB_PREFIX=stripe_
STRIPE_SYNC_ENABLED=true
STRIPE_FILAMENT_ENABLED=true
STRIPE_FILAMENT_PANEL=admin
```

### User Roles

The package creates three main roles:

1. **super-admin**: Full access to all features
2. **tenant-admin**: Access to tenant-specific features
3. **member**: Basic access to member features

To assign a role to a user:

```php
$user = User::find(1);
$user->assignRole('super-admin');
```

## Usage

### Accessing the Admin Interface

After installation, you can access the admin interface at:

- **Super Admin Panel**: `/super-admin`
- **Tenant Admin Panel**: `/tenant-admin`
- **Member Panel**: `/member`

### Available Commands

```bash
# Install the package
php artisan stripe-product-manager:install

# Test if the package is properly installed
php artisan stripe-product-manager:test

# Sync products from Stripe
php artisan stripe:sync-products

# Sync prices from Stripe
php artisan stripe:sync-prices

# Sync customers from Stripe
php artisan stripe:sync-customers
```

### Models

The package provides several models for managing Stripe data:

- `StripeProduct`: Manage Stripe products
- `StripePrice`: Handle product pricing
- `StripeCustomer`: Customer management
- `StripeSubscription`: Subscription tracking
- `StripeInvoice`: Invoice management
- `StripePayment`: Payment tracking

### Middleware

The package includes middleware for role-based access:

```php
// Protect routes with super admin access
Route::middleware(['stripe.guard:super-admin'])->group(function () {
    // Super admin routes
});

// Protect routes with tenant admin access
Route::middleware(['stripe.guard:tenant-admin'])->group(function () {
    // Tenant admin routes
});
```

## Testing

To test if the package is properly installed:

```bash
php artisan stripe-product-manager:test
```

This will check:
- Service provider registration
- Model availability
- Route registration
- Middleware registration
- Guard configuration
- Filament panel providers

## Troubleshooting

### Common Issues

1. **"Class not found" errors**: Run `composer dump-autoload`
2. **Migration errors**: Check your database configuration
3. **Permission errors**: Ensure the permission seeder ran successfully
4. **Filament not loading**: Clear all caches with `php artisan optimize:clear`

### Support

For issues and questions:
1. Check the troubleshooting section above
2. Review the test command output
3. Check Laravel and Filament logs
4. Ensure all dependencies are properly installed

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
