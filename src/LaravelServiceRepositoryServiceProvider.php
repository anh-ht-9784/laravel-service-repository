<?php

namespace Anhht\LaravelServiceRepository;

use Illuminate\Support\ServiceProvider;
use Anhht\LaravelServiceRepository\Console\CreateRepositoryAndService;
use Anhht\LaravelServiceRepository\Console\PublishCommand;


class LaravelServiceRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateRepositoryAndService::class,
                PublishCommand::class,
            ]);
        }
    }
}
