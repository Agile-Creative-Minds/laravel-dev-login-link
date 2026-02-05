<?php

namespace AgileCreativeMinds\DevLoginLink;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the Dev Login Link package.
 *
 * Registers the Artisan command and conditionally loads routes
 * only in local, development, and testing environments.
 */
class DevLoginLinkServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only load routes in local/dev environments
        if ($this->app->environment(['local', 'development', 'testing'])) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        // Register the command when running in the console
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\DevLoginLinkCommand::class,
            ]);
        }
    }
}
