<?php

namespace Anhht\LaravelServiceRepository\Console;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-repo:publish {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Base Setup package files (helpers and config)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Publishing Base Setup package files...');

        $force = $this->option('force');
        $published = false;

        // Publish helpers
        if ($this->publishHelpers($force)) {
            $published = true;
        }

        // Publish config
        if ($this->publishConfig($force)) {
            $published = true;
        }

        // Update AppServiceProvider (only if not already updated)
        $this->updateAppServiceProvider();

        if ($published) {
            $this->info('✅ Base Setup package files published successfully!');
        } else {
            $this->info('ℹ️  All files already exist. Use --force to overwrite.');
        }
    }

    /**
     * Publish helpers file
     */
    private function publishHelpers(bool $force = false): bool
    {
        $sourcePath = __DIR__ . '/../Helpers/functions.php';
        $targetPath = app_path('Helpers/functions.php');

        if (!file_exists($targetPath) || $force) {
            // Create directory if not exists
            if (!is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0755, true);
                $this->line('Created directory: ' . dirname($targetPath));
            }

            // Copy file
            if (file_exists($sourcePath)) {
                copy($sourcePath, $targetPath);
                $this->line('Helpers published to: ' . $targetPath);
                return true;
            } else {
                $this->error('Source helpers file not found: ' . $sourcePath);
                return false;
            }
        }

        $this->line('Helpers already exist: ' . $targetPath);
        return false;
    }

    /**
     * Publish config file
     */
    private function publishConfig(bool $force = false): bool
    {
        $sourcePath = __DIR__ . '/../Config/laravel-service-repository.php';
        $targetPath = config_path('laravel-service-repository.php');

        if (!file_exists($targetPath) || $force) {
            if (file_exists($sourcePath)) {
                // Copy file
                copy($sourcePath, $targetPath);
                $this->line('Config published to: ' . $targetPath);
                return true;
            } else {
                $this->error('Source config file not found: ' . $sourcePath);
                return false;
            }
        }

        $this->line('Config already exists: ' . $targetPath);
        return false;
    }

    /**
     * Update AppServiceProvider with repository and service bindings
     */
    private function updateAppServiceProvider(): void
    {
        $appServiceProviderPath = app_path('Providers/AppServiceProvider.php');

        if (!file_exists($appServiceProviderPath)) {
            $this->error('AppServiceProvider.php not found');
            return;
        }

        $content = file_get_contents($appServiceProviderPath);

        // Check if bindings are already added
        if (strpos($content, 'registerRepositories()') !== false) {
            return;
        }

        // Check if register() method exists
        if (strpos($content, 'public function register(): void') !== false) {
            // Add method calls to register() method
            $content = $this->addMethodCallsToRegister($content);

            // Add the binding methods
            $content = $this->addBindingMethods($content);

            // Write back to file
            file_put_contents($appServiceProviderPath, $content);

            $this->line('AppServiceProvider updated with repository and service bindings');
        } else {
            $this->line('AppServiceProvider register() method not found, skipping update');
        }
    }

    /**
     * Add method calls to register() method
     */
    private function addMethodCallsToRegister(string $content): string
    {
        // Find the closing brace of register() method and add method calls before it
        $pattern = '/(public function register\(\): void\s*\{[^}]*)\}/s';

        $replacement = '$1
        $this->registerServiceAndRepositories();
    }';

        return preg_replace($pattern, $replacement, $content);
    }

    /**
     * Add binding methods to the class
     */
    private function addBindingMethods(string $content): string
    {
        $bindingMethods = '

    /**
     * Register service and repositories
     */
    protected function registerServiceAndRepositories(): void
    {
    }';

        // Add methods before the last closing brace of the class (only the last one)
        $lastBracePos = strrpos($content, '}');
        if ($lastBracePos !== false) {
            $content = substr($content, 0, $lastBracePos) . $bindingMethods . "\n" . substr($content, $lastBracePos);
        }

        return $content;
    }

}
