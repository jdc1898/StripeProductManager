<?php

namespace Fullstack\StripeProductManager\Console\Commands\Stripe;

use Illuminate\Console\Command;

class FetchAllStripeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:fetch-all
                            {--limit=100 : Number of records to fetch for each type}
                            {--save : Save data to database tables}
                            {--products : Fetch products only}
                            {--prices : Fetch prices only}
                            {--customers : Fetch customers only}
                            {--meters : Fetch meters only}
                            {--discounts : Fetch discounts only}
                            {--promotion-codes : Fetch promotion codes only}
                            {--coupons : Fetch coupons only}
                            {--tax-codes : Fetch tax codes only}
                            {--tax-rates : Fetch tax rates only}
                            {--skip-products : Skip fetching products}
                            {--skip-prices : Skip fetching prices}
                            {--skip-customers : Skip fetching customers}
                            {--skip-meters : Skip fetching meters}
                            {--skip-discounts : Skip fetching discounts}
                            {--skip-promotion-codes : Skip fetching promotion codes}
                            {--skip-coupons : Skip fetching coupons}
                            {--skip-tax-codes : Skip fetching tax codes}
                            {--skip-tax-rates : Skip fetching tax rates}
                            {--detailed : Show detailed information for each command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all Stripe data (products, prices, customers, meters) in sequence';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $save = $this->option('save');
        $detailed = $this->option('detailed');

        // Check environment configuration and Stripe connection
        if (!$this->checkEnvironmentAndStripeConnection()) {
            return 1;
        }

        // Determine which commands to run
        $commands = $this->getCommandsToRun();

        if (empty($commands)) {
            $this->error('No commands to run. Please specify which data types to fetch.');
            return 1;
        }

        $this->info('Starting Stripe data fetch...');
        $this->info('Commands to run: ' . implode(', ', array_keys($commands)));
        $this->info('Limit: ' . $limit . ' records per type');
        $this->info('Save to database: ' . ($save ? 'Yes' : 'No'));
        $this->newLine();

        $results = [];
        $startTime = microtime(true);

        foreach ($commands as $name => $command) {
            $this->info("=== Fetching $name ===");

            try {
                $result = $this->executeCommand($command, $limit, $save, $detailed);
                $results[$name] = $result;

                if ($result['success']) {
                    $this->info("✓ $name completed successfully");
                } else {
                    $this->error("✗ $name failed: " . $result['error']);
                }
            } catch (\Exception $e) {
                $this->error("✗ $name failed with exception: " . $e->getMessage());
                $results[$name] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }

            $this->newLine();
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->info('=== Summary ===');
        $this->info("Total time: {$duration}s");

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $totalCount = count($results);

        $this->info("Commands completed: $successCount/$totalCount");

        if ($successCount === $totalCount) {
            $this->info('✓ All commands completed successfully!');
            return 0;
        } else {
            $this->warn('⚠ Some commands failed. Check the output above for details.');
            return 1;
        }
    }

    /**
     * Determine which commands to run based on options
     */
    private function getCommandsToRun(): array
    {
        $commands = [
            'products' => 'stripe:fetch-products',
            'prices' => 'stripe:fetch-prices',
            'customers' => 'stripe:fetch-customers',
            'meters' => 'stripe:fetch-meters',
            'discounts' => 'stripe:fetch-discounts',
            'promotion-codes' => 'stripe:fetch-promotion-codes',
            'coupons' => 'stripe:fetch-coupons',
            'tax-codes' => 'stripe:fetch-tax-codes',
            'tax-rates' => 'stripe:fetch-tax-rates',
        ];

        // If specific commands are requested, only run those
        $specificCommands = [];
        foreach (['products', 'prices', 'customers', 'meters', 'discounts', 'promotion-codes', 'coupons', 'tax-codes', 'tax-rates'] as $type) {
            if ($this->option($type)) {
                $specificCommands[$type] = $commands[$type];
            }
        }

        if (!empty($specificCommands)) {
            return $specificCommands;
        }

        // Otherwise, run all commands except skipped ones
        foreach (['skip-products', 'skip-prices', 'skip-customers', 'skip-meters', 'skip-discounts', 'skip-promotion-codes', 'skip-coupons', 'skip-tax-codes', 'skip-tax-rates'] as $skipOption) {
            $type = str_replace('skip-', '', $skipOption);
            if ($this->option($skipOption)) {
                unset($commands[$type]);
            }
        }

        return $commands;
    }

    /**
     * Run a specific command
     */
    private function executeCommand(string $command, int $limit, bool $save, bool $detailed): array
    {
        $args = ["--limit=$limit"];

        if ($save) {
            $args[] = '--save';
        }

        if ($detailed) {
            $args[] = '--detailed';
        }

        $fullCommand = "php artisan $command " . implode(' ', $args);

        $this->line("Running: $fullCommand");

        $process = \Illuminate\Support\Facades\Process::run($fullCommand);

        if ($process->successful()) {
            return [
                'success' => true,
                'output' => $process->output(),
                'command' => $fullCommand
            ];
        } else {
            return [
                'success' => false,
                'error' => $process->errorOutput(),
                'command' => $fullCommand
            ];
        }
    }

    /**
     * Check if environment variables are set and Stripe connection is available
     */
    private function checkEnvironmentAndStripeConnection(): bool
    {
        $this->info('Checking environment configuration and Stripe connection...');

        // Check if .env file exists
        if (!file_exists(base_path('.env'))) {
            $this->error('❌ .env file not found. Please create a .env file with your Stripe configuration.');
            return false;
        }

        // Check required environment variables
        $requiredEnvVars = [
            'STRIPE_KEY' => env('STRIPE_KEY'),
            'STRIPE_SECRET' => env('STRIPE_SECRET'),
            'STRIPE_WEBHOOK_SECRET' => env('STRIPE_WEBHOOK_SECRET'),
        ];

        $missingVars = [];
        foreach ($requiredEnvVars as $var => $value) {
            if (empty($value)) {
                $missingVars[] = $var;
            }
        }

        if (!empty($missingVars)) {
            $this->error('❌ Missing required environment variables:');
            foreach ($missingVars as $var) {
                $this->error("   - $var");
            }
            $this->error('Please add these variables to your .env file.');
            return false;
        }

        $this->info('✅ Environment variables are properly configured');

        // Test Stripe connection
        try {
            $this->info('Testing Stripe connection...');

            // Check if Stripe PHP SDK is available
            if (!class_exists('\Stripe\Stripe')) {
                $this->error('❌ Stripe PHP SDK not found. Please install it with: composer require stripe/stripe-php');
                return false;
            }

            // Initialize Stripe with the secret key
            $stripeKey = env('STRIPE_SECRET');
            \Stripe\Stripe::setApiKey($stripeKey);

            // Test the connection by making a simple API call
            $account = \Stripe\Account::retrieve();

            if ($account && isset($account->id)) {
                $this->info('✅ Stripe connection successful');
                $this->info("   Account ID: {$account->id}");
                $this->info("   Live mode: " . ($account->charges_enabled ? 'Yes' : 'No'));
                return true;
            } else {
                $this->error('❌ Stripe connection failed: Unable to retrieve account information');
                return false;
            }

        } catch (\Stripe\Exception\AuthenticationException $e) {
            $this->error('❌ Stripe authentication failed: Invalid API key');
            $this->error("   Error: {$e->getMessage()}");
            return false;
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $this->error('❌ Stripe connection failed: Network error');
            $this->error("   Error: {$e->getMessage()}");
            return false;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->error('❌ Stripe API error');
            $this->error("   Error: {$e->getMessage()}");
            return false;
        } catch (\Exception $e) {
            $this->error('❌ Unexpected error while testing Stripe connection');
            $this->error("   Error: {$e->getMessage()}");
            return false;
        }
    }
}
