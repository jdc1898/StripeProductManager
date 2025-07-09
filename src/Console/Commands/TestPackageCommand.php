<?php

namespace Fullstack\StripeProductManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class TestPackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe-product-manager:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test if the Stripe Product Manager package is properly registered';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Stripe Product Manager Package...');

        // Test if service provider is loaded
        $this->info('1. Checking service provider...');
        if (class_exists('Fullstack\StripeProductManager\StripeProductManagerServiceProvider')) {
            $this->info('   ✅ Service provider class exists');
        } else {
            $this->error('   ❌ Service provider class not found');
        }

        // Test if models are available
        $this->info('2. Checking models...');
        $models = [
            'Fullstack\StripeProductManager\Models\StripeProduct',
            'Fullstack\StripeProductManager\Models\StripePrice',
            'Fullstack\StripeProductManager\Models\StripeCustomer',
        ];

        foreach ($models as $model) {
            if (class_exists($model)) {
                $this->info("   ✅ {$model} exists");
            } else {
                $this->error("   ❌ {$model} not found");
            }
        }

        // Test if routes are registered
        $this->info('3. Checking routes...');
        $routes = [];
        foreach (Route::getRoutes() as $route) {
            if (str_contains($route->uri(), 'stripe-product-manager')) {
                $routes[] = $route->uri();
            }
        }

        if (count($routes) > 0) {
            $this->info('   ✅ Package routes found:');
            foreach ($routes as $route) {
                $this->line("      - {$route}");
            }
        } else {
            $this->error('   ❌ No package routes found');
        }

        // Test if middleware is registered
        $this->info('4. Checking middleware...');
        if (app('router')->getMiddleware()['stripe.guard'] ?? false) {
            $this->info('   ✅ Stripe guard middleware registered');
        } else {
            $this->error('   ❌ Stripe guard middleware not registered');
        }

        // Test if guards are registered
        $this->info('5. Checking auth guards...');
        $guards = config('auth.guards');
        $requiredGuards = ['web', 'tenant-admin', 'super-admin'];

        foreach ($requiredGuards as $guard) {
            if (isset($guards[$guard])) {
                $this->info("   ✅ Guard '{$guard}' registered");
            } else {
                $this->error("   ❌ Guard '{$guard}' not registered");
            }
        }

        // Test if Filament panel providers are available
        $this->info('6. Checking Filament panel providers...');
        $panelProviders = [
            'App\Providers\Filament\SuperAdminPanelProvider',
            'App\Providers\Filament\TenantAdminPanelProvider',
            'App\Providers\Filament\MemberPanelProvider',
        ];

        foreach ($panelProviders as $provider) {
            if (class_exists($provider)) {
                $this->info("   ✅ {$provider} exists");
            } else {
                $this->error("   ❌ {$provider} not found");
            }
        }

        // Test if Filament panels are registered
        $this->info('7. Checking Filament panels...');
        $panels = [];
        foreach (Route::getRoutes() as $route) {
            if (str_contains($route->uri(), 'admin') || str_contains($route->uri(), 'super-admin') || str_contains($route->uri(), 'member')) {
                $panels[] = $route->uri();
            }
        }

        if (count($panels) > 0) {
            $this->info('   ✅ Filament panels found:');
            foreach ($panels as $panel) {
                $this->line("      - {$panel}");
            }
        } else {
            $this->error('   ❌ No Filament panels found');
        }

        $this->info('Test completed!');
    }
}
