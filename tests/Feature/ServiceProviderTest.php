<?php

namespace AgileCreativeMinds\DevLoginLink\Tests\Feature;

use AgileCreativeMinds\DevLoginLink\DevLoginLinkServiceProvider;
use AgileCreativeMinds\DevLoginLink\Tests\TestCase;
use Illuminate\Support\Facades\Route;

/**
 * Feature tests for the DevLoginLinkServiceProvider.
 *
 * Tests verify that routes and commands are properly registered
 * in the appropriate environments.
 */
class ServiceProviderTest extends TestCase
{
    public function test_route_is_registered_in_local_environment(): void
    {
        $this->app['config']->set('app.env', 'local');

        // Re-register the service provider to pick up env change
        $this->app->register(DevLoginLinkServiceProvider::class, true);

        $this->assertTrue(Route::has('dev.login'));
    }

    public function test_route_is_registered_in_testing_environment(): void
    {
        $this->app['config']->set('app.env', 'testing');

        $this->assertTrue(Route::has('dev.login'));
    }

    public function test_command_is_registered(): void
    {
        $this->artisan('list')
            ->expectsOutputToContain('dev:login-link');
    }
}
