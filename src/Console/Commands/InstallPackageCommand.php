<?php

namespace Fullstack\StripeProductManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallPackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe-product-manager:install
                            {--force : Force installation even if already installed}
                            {--skip-migrations : Skip running migrations}
                            {--skip-seeding : Skip seeding permissions}
                            {--skip-config : Skip configuration updates}
                            {--publish-panels : Publish Filament panels for customization}
                            {--publish-models : Publish models for customization}
                            {--publish-migrations : Publish migrations for customization}
                            {--publish-config : Publish config for customization}

                            {--publish-all : Publish all customizable assets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Stripe Product Manager package into an existing Laravel project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Installing Stripe Product Manager Package...');
        $this->newLine();

        // Check if already installed
        if (! $this->option('force') && $this->isAlreadyInstalled()) {
            $this->warn('Package appears to be already installed. Use --force to reinstall.');

            return 1;
        }

        try {
            $this->installDependencies();

            // Update providers
            $this->updateBootstrapProviders();

            // Publish assets
            $this->publishAssets();

            // Run migrations
            $this->runMigrations();

            // Seed permissions
            $this->seedPermissions();


            $this->newLine();
            $this->info('✅ Package installed successfully!');
            $this->newLine();

            $this->displayNextSteps();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Installation failed: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return 1;
        }
    }

    /**
     * Check if the package is already installed
     */
    private function isAlreadyInstalled(): bool
    {
        return class_exists('Fullstack\StripeProductManager\StripeProductManagerServiceProvider') &&
               class_exists('Fullstack\StripeProductManager\Models\StripeProduct');
    }

    /**
     * Install required dependencies
     */
    private function installDependencies(): void
    {
        $this->info('📦 Checking dependencies...');

        $dependencies = [
            'spatie/laravel-permission',
            'stripe/stripe-php',
            'filament/filament',
            'flowframe/laravel-trend',
        ];

        $missingDependencies = [];

        foreach ($dependencies as $dependency) {
            // Check if the package is installed by looking for key classes
            $classMap = [
                'spatie/laravel-permission' => 'Spatie\Permission\PermissionServiceProvider',
                'stripe/stripe-php' => 'Stripe\Stripe',
                'filament/filament' => 'Filament\FilamentServiceProvider',
                'flowframe/laravel-trend' => 'Flowframe\Trend\TrendServiceProvider',
            ];

            if (! class_exists($classMap[$dependency])) {
                $missingDependencies[] = $dependency;
            }
        }

        if (! empty($missingDependencies)) {
            $this->warn('⚠️  Missing dependencies detected:');
            foreach ($missingDependencies as $dependency) {
                $this->line("   - {$dependency}");
            }
            $this->newLine();
            $this->info('Please install missing dependencies manually:');
            foreach ($missingDependencies as $dependency) {
                $this->line("   composer require {$dependency}");
            }
            $this->newLine();

            if (! $this->confirm('Continue with installation? (Some features may not work without dependencies)')) {
                throw new \Exception('Installation cancelled by user');
            }
        } else {
            $this->info('✅ All dependencies are installed');
        }

        // Try to install Filament if not already installed
        if (! class_exists('Filament\FilamentServiceProvider')) {
            $this->line('Installing Filament...');

            try {
                $this->call('filament:install', ['--panels' => true, '--no-interaction' => true]);
            } catch (\Exception $e) {
                $this->warn('Filament installation failed. Please install manually: composer require filament/filament');
            }
        }
    }


    /**
     * Publish package assets
     */
    private function publishAssets(): void
    {
        $this->info('📋 Publishing assets...');

        // Publish Spatie permissions
        $this->call('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
        ]);

        // Publish package assets
        $this->call('vendor:publish', [
            '--provider' => 'Fullstack\StripeProductManager\StripeProductManagerServiceProvider',
        ]);

        // Publish Filament panels if requested
        if ($this->option('publish-panels') || $this->option('publish-all')) {
            $this->info('🎨 Publishing Filament panels for customization...');

            $this->call('vendor:publish', [
                '--provider' => 'Fullstack\StripeProductManager\StripeProductManagerServiceProvider',
                '--tag' => 'stripe-product-manager-filament',
            ]);

            // Fix namespaces after publishing
            $this->call('stripe-product-manager:fix-namespaces', ['--filament' => true]);

            $this->info('✅ Filament panels published to app/Filament/');
            $this->warn('⚠️  Note: Published panels will override package panels. Update app/Providers/Filament/ to use published panel providers.');
        }

        // Publish models if requested
        if ($this->option('publish-models') || $this->option('publish-all')) {
            $this->info('📦 Publishing models for customization...');

            $this->call('vendor:publish', [
                '--provider' => 'Fullstack\StripeProductManager\StripeProductManagerServiceProvider',
                '--tag' => 'stripe-product-manager-models',
            ]);

            // Fix namespaces after publishing
            $this->call('stripe-product-manager:fix-namespaces', ['--models' => true]);

            $this->info('✅ Models published to app/Models/Stripe/ with correct namespace');
            $this->warn('⚠️  Note: Published models use App\Models\Stripe namespace. Update references in your code.');
        }

        // Publish migrations if requested
        if ($this->option('publish-migrations') || $this->option('publish-all')) {
            $this->info('🗄️ Publishing migrations for customization...');

            $this->call('vendor:publish', [
                '--provider' => 'Fullstack\StripeProductManager\StripeProductManagerServiceProvider',
                '--tag' => 'stripe-product-manager-migrations-custom',
            ]);

            $this->info('✅ Migrations published to database/migrations/stripe/');
            $this->warn('⚠️  Note: Published migrations will override package migrations. You may need to update model references.');
        }

        // Publish config if requested
        if ($this->option('publish-config') || $this->option('publish-all')) {
            $this->info('⚙️ Publishing config for customization...');

            $this->call('vendor:publish', [
                '--provider' => 'Fullstack\StripeProductManager\StripeProductManagerServiceProvider',
                '--tag' => 'stripe-product-manager-config-custom',
            ]);

            $this->info('✅ Config published to config/stripe-product-manager.php');
            $this->warn('⚠️  Note: Published config will override package config. Update settings as needed.');
        }



        $this->info('✅ Assets published successfully');
    }

    /**
     * Run migrations
     */
    private function runMigrations(): void
    {
        $this->info('🗄️ Running migrations...');

        try {
            $this->call('migrate');
            $this->info('✅ Migrations completed successfully');
        } catch (\Exception $e) {
            $this->error('❌ Migration failed: '.$e->getMessage());

            throw $e;
        }
    }

    /**
     * Seed permissions
     */
    private function seedPermissions(): void
    {
        $this->info('🌱 Seeding permissions...');

        try {
            $this->call('db:seed', [
                '--class' => 'Database\Seeders\StripeProductManagerPermissionSeeder',
            ]);
            $this->info('✅ Permissions seeded successfully');
        } catch (\Exception $e) {
            $this->error('❌ Seeding failed: '.$e->getMessage());

            throw $e;
        }
    }


    /**
     * Display next steps for the user
     */
    private function displayNextSteps(): void
    {
        $this->info('📋 Next steps:');
        $this->line('1. Update your Stripe API keys in .env file');
        $this->line('2. Create a user with super-admin role:');
        $this->line('   php artisan tinker');
        $this->line('   $user = User::create([\'name\' => \'Admin\', \'email\' => \'admin@example.com\', \'password\' => bcrypt(\'password\')]);');
        $this->line('   $user->assignRole(\'super-admin\');');
        $this->line('3. Visit /admin to access the admin panel');

        if ($this->option('publish-all')) {
            $this->newLine();
            $this->info('🎨 Full Customization Published:');
            $this->line('   • Panels: app/Filament/');
            $this->line('   • Models: app/Models/Stripe/');
            $this->line('   • Migrations: database/migrations/stripe/');
            $this->line('   • Config: config/stripe-product-manager.php');

            $this->line('   • Panel providers: app/Providers/Filament/');
            $this->line('   • Update bootstrap/providers.php to use published panel providers');
            $this->line('   • Update model namespace references in your code');
            $this->line('   • Customize settings in config/stripe-product-manager.php');
        } elseif ($this->option('publish-panels') || $this->option('publish-models') || $this->option('publish-migrations') || $this->option('publish-config')) {
            $this->newLine();
            $this->info('🎨 Customization Published:');
            if ($this->option('publish-panels')) {
                $this->line('   • Panels: app/Filament/');
                $this->line('   • Panel providers: app/Providers/Filament/');
            }
            if ($this->option('publish-models')) {
                $this->line('   • Models: app/Models/Stripe/');
            }
            if ($this->option('publish-migrations')) {
                $this->line('   • Migrations: database/migrations/stripe/');
            }
            if ($this->option('publish-config')) {
                $this->line('   • Config: config/stripe-product-manager.php');
            }

            $this->line('   • Update bootstrap/providers.php to use published panel providers');
            $this->line('   • Update model namespace references in your code');
            if ($this->option('publish-config')) {
                $this->line('   • Customize settings in config/stripe-product-manager.php');
            }
        } else {
            $this->newLine();
            $this->info('🎨 To customize later:');
            $this->line('   • Panels: php artisan vendor:publish --tag=stripe-product-manager-filament');
            $this->line('   • Models: php artisan vendor:publish --tag=stripe-product-manager-models');
            $this->line('   • Migrations: php artisan vendor:publish --tag=stripe-product-manager-migrations-custom');
            $this->line('   • Config: php artisan vendor:publish --tag=stripe-product-manager-config-custom');

            $this->line('   • Everything: php artisan vendor:publish --tag=stripe-product-manager-customize');
        }

        $this->newLine();
        $this->info('🧪 Test the installation:');
        $this->line('   php artisan stripe-product-manager:test');

        $this->newLine();
        $this->info('💳 Available Stripe Commands:');
        $this->line('   • php artisan stripe:fetch-all - Fetch all Stripe data');
        $this->line('   • php artisan stripe:fetch-products - Fetch Stripe products');
        $this->line('   • php artisan stripe:fetch-prices - Fetch Stripe prices');
        $this->line('   • php artisan stripe:fetch-customers - Fetch Stripe customers');
        $this->line('   • php artisan stripe:fetch-coupons - Fetch Stripe coupons');
        $this->line('   • php artisan stripe:fetch-discounts - Fetch Stripe discounts');
        $this->line('   • php artisan stripe:fetch-promotion-codes - Fetch promotion codes');
        $this->line('   • php artisan stripe:fetch-tax-codes - Fetch tax codes');
        $this->line('   • php artisan stripe:fetch-tax-rates - Fetch tax rates');
        $this->line('   • php artisan stripe:fetch-meters - Fetch meters');
        $this->line('   • php artisan stripe:get-meter-usage - Get meter usage');
        $this->line('   • php artisan list stripe - See all available Stripe commands');

        $this->newLine();
        $this->info('🔧 Utility Commands:');
        $this->line('   • php artisan stripe-product-manager:fix-namespaces - Fix published file namespaces');
    }


    private function updateBootstrapProviders(): void
    {
        $this->info('🔧 Updating bootstrap providers...');

        $path = base_path('bootstrap/providers.php');

        if (! file_exists($path)) {
            $this->error('bootstrap/providers.php not found.');
            return;
        }

        $providers = require $path;

        $newProviders = [
            \App\Providers\Filament\SuperAdminPanelProvider::class,
            \App\Providers\Filament\TenantAdminPanelProvider::class,
            \App\Providers\Filament\MemberPanelProvider::class,
        ];

        $providers = array_values(array_unique(array_merge(
            [],
            $this->insertAfter($providers, \App\Providers\AppServiceProvider::class, $newProviders)
        )));

        // Write the file back
        $contents = "<?php\n\nreturn [\n";
        foreach ($providers as $provider) {
            $contents .= "    {$provider}::class,\n";
        }
        $contents .= "];\n";

        file_put_contents($path, $contents);

        $this->info('Providers successfully updated.');
    }

    protected function insertAfter(array $original, string $after, array $insert): array
    {
        $new = [];
        foreach ($original as $provider) {
            $new[] = $provider;
            if ($provider === $after) {
                foreach ($insert as $i) {
                    if (!in_array($i, $original)) {
                        $new[] = $i;
                    }
                }
            }
        }

        return $new;
    }
}
