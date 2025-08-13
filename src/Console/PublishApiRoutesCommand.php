<?php

namespace Anhht\LaravelServiceRepository\Console;

use Illuminate\Console\Command;

class PublishApiRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-repo:publish-api-routes {--force : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish API routes file for the service repository package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Publishing API files...');

        $force = $this->option('force');
        $published = false;

        // Publish API routes file
        if ($this->publishApiRoutes($force)) {
            $published = true;
        }

        // Publish BaseApiController
        if ($this->publishBaseApiController($force)) {
            $published = true;
        }

        // Publish ApiCodes constants
        if ($this->publishApiCodes($force)) {
            $published = true;
        }

        // Publish custom Exception Handler
        if ($this->publishExceptionHandler($force)) {
            $published = true;
        }

        if ($published) {
            $this->info('✅ API files published successfully!');
        } else {
            $this->info('ℹ️  All files already exist. Use --force to overwrite.');
        }

        return Command::SUCCESS;
    }

    /**
     * Publish API routes file
     */
    private function publishApiRoutes(bool $force = false): bool
    {
        $sourcePath = __DIR__ . '/../Routes/api.php';
        $targetPath = base_path('routes/api.php');

        // Check if source file exists
        if (!file_exists($sourcePath)) {
            $this->error('Source API routes file not found: ' . $sourcePath);
            return false;
        }

        // Check if target file exists and handle force option
        if (file_exists($targetPath) && !$force) {
            if ($this->confirm('API routes file already exists. Do you want to overwrite it?')) {
                $force = true;
            } else {
                $this->comment('Skipping API routes publishing');
                return false;
            }
        }

        // Create routes directory if not exists
        $routesDir = dirname($targetPath);
        if (!is_dir($routesDir)) {
            mkdir($routesDir, 0755, true);
            $this->line('Created directory: ' . $routesDir);
        }

        // Copy file
        if (copy($sourcePath, $targetPath)) {
            // Update bootstrap/app.php to include API routes
            $this->updateBootstrapApp();
            
            $this->info('✅ API routes file published successfully!');
            $this->line('📁 Location: ' . $targetPath);
            $this->comment('💡 You can now add your API routes to this file');
            return true;
        } else {
            $this->error('Failed to publish API routes file');
            return false;
        }
    }

    /**
     * Publish ApiCodes constants
     */
    private function publishApiCodes(bool $force = false): bool
    {
        $sourcePath = __DIR__ . '/../Constants/ApiCodes.php';
        $targetPath = app_path('Constants/ApiCodes.php');

        // Check if source file exists
        if (!file_exists($sourcePath)) {
            $this->error('Source ApiCodes file not found: ' . $sourcePath);
            return false;
        }

        // Check if target file exists and handle force option
        if (file_exists($targetPath) && !$force) {
            if ($this->confirm('ApiCodes already exists. Do you want to overwrite it?')) {
                $force = true;
            } else {
                $this->comment('Skipping ApiCodes publishing');
                return false;
            }
        }

        // Create constants directory if not exists
        $constantsDir = dirname($targetPath);
        if (!is_dir($constantsDir)) {
            mkdir($constantsDir, 0755, true);
            $this->line('Created directory: ' . $constantsDir);
        }

        // Copy file
        if (copy($sourcePath, $targetPath)) {
            $this->info('✅ ApiCodes constants published successfully!');
            $this->line('📁 Location: ' . $targetPath);
            $this->comment('💡 You can now use these constants in your controllers');
            return true;
        } else {
            $this->error('Failed to publish ApiCodes');
            return false;
        }
    }

    /**
     * Publish BaseApiController
     */
    private function publishBaseApiController(bool $force = false): bool
    {
        $sourcePath = __DIR__ . '/../Controllers/BaseApiController.php';
        $targetPath = app_path('Http/Controllers/Api/BaseApiController.php');

        // Check if source file exists
        if (!file_exists($sourcePath)) {
            $this->error('Source BaseApiController file not found: ' . $sourcePath);
            return false;
        }

        // Check if target file exists and handle force option
        if (file_exists($targetPath) && !$force) {
            if ($this->confirm('BaseApiController already exists. Do you want to overwrite it?')) {
                $force = true;
            } else {
                $this->comment('Skipping BaseApiController publishing');
                return false;
            }
        }

        // Create controllers directory if not exists
        $controllersDir = dirname($targetPath);
        if (!is_dir($controllersDir)) {
            mkdir($controllersDir, 0755, true);
            $this->line('Created directory: ' . $controllersDir);
        }

        // Copy file
        if (copy($sourcePath, $targetPath)) {
            $this->info('✅ BaseApiController published successfully!');
            $this->line('📁 Location: ' . $targetPath);
            $this->comment('💡 You can now extend this controller in your API controllers');
            return true;
        } else {
            $this->error('Failed to publish BaseApiController');
            return false;
        }
    }

    /**
     * Update bootstrap/app.php to include API routes
     */
    private function updateBootstrapApp(): void
    {
        $bootstrapAppPath = base_path('bootstrap/app.php');
        
        if (!file_exists($bootstrapAppPath)) {
            $this->warn('bootstrap/app.php not found, skipping API routes registration');
            return;
        }

        $content = file_get_contents($bootstrapAppPath);
        
        // Check if API routes are already registered
        if (strpos($content, 'api: __DIR__.\'/../routes/api.php\'') !== false) {
            $this->line('API routes already registered in bootstrap/app.php');
            return;
        }

        // Add API routes to withRouting
        $pattern = '/->withRouting\(\s*web: __DIR__\.\'\/\.\.\/routes\/web\.php\',\s*commands: __DIR__\.\'\/\.\.\/routes\/console\.php\',\s*health: \'\/up\',\s*\)/';
        $replacement = "->withRouting(\n        web: __DIR__.'/../routes/web.php',\n        api: __DIR__.'/../routes/api.php',\n        commands: __DIR__.'/../routes/console.php',\n        health: '/up',\n    )";
        
        $newContent = preg_replace($pattern, $replacement, $content);
        
        if ($newContent !== $content) {
            file_put_contents($bootstrapAppPath, $newContent);
            $this->info('✅ API routes registered in bootstrap/app.php');
        } else {
            $this->warn('Could not update bootstrap/app.php, please add API routes manually');
        }
    }

    /**
     * Publish custom Exception Handler
     */
    private function publishExceptionHandler(bool $force = false): bool
    {
        $sourcePath = __DIR__ . '/../Exceptions/Handler.php';
        $targetPath = app_path('Exceptions/Handler.php');

        // Check if source file exists
        if (!file_exists($sourcePath)) {
            $this->error('Source Exception Handler file not found: ' . $sourcePath);
            return false;
        }

        // Check if target file exists and handle force option
        if (file_exists($targetPath) && !$force) {
            if ($this->confirm('Exception Handler already exists. Do you want to overwrite it?')) {
                $force = true;
            } else {
                $this->comment('Skipping Exception Handler publishing');
                return false;
            }
        }

        // Create exceptions directory if not exists
        $exceptionsDir = dirname($targetPath);
        if (!is_dir($exceptionsDir)) {
            mkdir($exceptionsDir, 0755, true);
            $this->line('Created directory: ' . $exceptionsDir);
        }

        // Copy file and update namespace
        if (copy($sourcePath, $targetPath)) {
            // Update namespace from package to app
            $content = file_get_contents($targetPath);
            $content = str_replace(
                'namespace Anhht\\LaravelServiceRepository\\Exceptions;',
                'namespace App\\Exceptions;',
                $content
            );
            file_put_contents($targetPath, $content);
            
            $this->info('✅ Exception Handler published successfully!');
            $this->line('📁 Location: ' . $targetPath);
            $this->comment('💡 All API exceptions will now be standardized automatically');
            return true;
        } else {
            $this->error('Failed to publish Exception Handler');
            return false;
        }
    }
} 