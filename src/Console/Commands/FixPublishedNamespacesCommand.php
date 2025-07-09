<?php

namespace Fullstack\StripeProductManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixPublishedNamespacesCommand extends Command
{
    protected $signature = 'stripe-product-manager:fix-namespaces {--models : Fix model namespaces} {--filament : Fix Filament resource namespaces} {--all : Fix all namespaces}';
    protected $description = 'Fix namespaces of published Stripe Product Manager files';

    public function handle()
    {
        $this->info('🔧 Fixing published file namespaces...');

        if ($this->option('models') || $this->option('all')) {
            $this->fixModelNamespaces();
        }

        if ($this->option('filament') || $this->option('all')) {
            $this->fixFilamentNamespaces();
        }

        if (!$this->option('models') && !$this->option('filament') && !$this->option('all')) {
            $this->fixModelNamespaces();
            $this->fixFilamentNamespaces();
        }

        $this->info('✅ Namespace fixes completed!');
        $this->newLine();
        $this->info('📝 Next steps:');
        $this->line('   • Run composer dump-autoload to refresh autoloader');
        $this->line('   • Update any remaining references in your application');
    }

    private function fixModelNamespaces(): void
    {
        $this->info('📦 Fixing model namespaces...');

        $modelsDir = app_path('Models/Stripe');

        if (!File::exists($modelsDir)) {
            $this->warn("Models directory not found: {$modelsDir}");
            return;
        }

        $files = File::allFiles($modelsDir);
        $fixedCount = 0;

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $content = File::get($file->getPathname());
                $originalContent = $content;

                // Update namespace from package to app
                $content = str_replace(
                    'namespace Fullstack\StripeProductManager\Models;',
                    'namespace App\Models\Stripe;',
                    $content
                );

                // Update imports to use App namespace
                $content = str_replace(
                    'Fullstack\StripeProductManager\Models\\',
                    'App\Models\\',
                    $content
                );

                // Update config references to use the correct user model
                $content = str_replace(
                    'App\Models\User::class',
                    'config(\'auth.providers.users.model\')',
                    $content
                );

                if ($content !== $originalContent) {
                    File::put($file->getPathname(), $content);
                    $fixedCount++;
                    $this->line("✅ Fixed {$file->getRelativePathname()}");
                }
            }
        }

        $this->info("📦 Fixed {$fixedCount} model file(s)");
    }

    private function fixFilamentNamespaces(): void
    {
        $this->info('🎨 Fixing Filament resource namespaces...');

        $filamentDirs = [
            app_path('Filament/SuperAdmin'),
            app_path('Filament/Admin'),
            app_path('Filament/Member'),
        ];

        $fixedCount = 0;

        foreach ($filamentDirs as $dir) {
            if (!File::exists($dir)) {
                continue;
            }

            $files = File::allFiles($dir);

            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $content = File::get($file->getPathname());
                    $originalContent = $content;

                    // Update model imports to use App namespace
                    $content = str_replace(
                        'use Fullstack\StripeProductManager\Models\\',
                        'use App\Models\\',
                        $content
                    );

                    // Update model class references
                    $content = str_replace(
                        '\Fullstack\StripeProductManager\Models\\',
                        '\App\Models\\',
                        $content
                    );

                    if ($content !== $originalContent) {
                        File::put($file->getPathname(), $content);
                        $fixedCount++;
                        $this->line("✅ Fixed {$file->getRelativePathname()}");
                    }
                }
            }
        }

        $this->info("🎨 Fixed {$fixedCount} Filament file(s)");
    }
}
