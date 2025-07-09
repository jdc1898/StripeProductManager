<?php

namespace Fullstack\StripeProductManager;

use Illuminate\Support\ServiceProvider;

class StripeProductManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/stripe-product-manager.php',
            'stripe-product-manager'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/config/guards.php',
            'auth.guards'
        );

        $this->app['router']->aliasMiddleware(
            'stripe.guard',
            \Fullstack\StripeProductManager\Http\Middleware\StripeGuardMiddleware::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if (file_exists(__DIR__ . '/routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }

        if (is_dir(__DIR__ . '/resources/views')) {
            $this->loadViewsFrom(__DIR__ . '/resources/views', 'stripe-product-manager');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Fullstack\StripeProductManager\Console\Commands\TestPackageCommand::class,
                \Fullstack\StripeProductManager\Console\Commands\InstallPackageCommand::class,
                \Fullstack\StripeProductManager\Console\Commands\FixPublishedNamespacesCommand::class,

                // Stripe commands
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchAllStripeData::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchStripeProducts::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchStripePrices::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchStripeCustomers::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchStripeCoupons::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchStripeDiscounts::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchStripePromotionCodes::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchStripeTaxCodes::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchStripeTaxRates::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\FetchStripeMeters::class,
                \Fullstack\StripeProductManager\Console\Commands\Stripe\GetMeterUsage::class,
            ]);

            // One single publish group
            $this->publishes([
                __DIR__ . '/config/stripe-product-manager.php' => config_path('stripe-product-manager.php'),
                __DIR__ . '/database/migrations' => database_path('migrations'),
                __DIR__ . '/database/seeders' => database_path('seeders'),
                __DIR__ . '/Models' => app_path('Models'),
                __DIR__ . '/Filament' => app_path('Filament'),
                __DIR__ . '/Providers/Filament' => app_path('Providers/Filament'),
                __DIR__ . '/Services/Stripe' => app_path('Services/Stripe'),
            ], 'stripe-product-manager');
        }
    }
}
