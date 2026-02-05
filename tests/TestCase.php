<?php

namespace AgileCreativeMinds\DevLoginLink\Tests;

use AgileCreativeMinds\DevLoginLink\DevLoginLinkServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 * Base test case for the Dev Login Link package.
 *
 * Configures Orchestra Testbench with the package's service provider,
 * an in-memory SQLite database, and a test User model fixture.
 */
abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            DevLoginLinkServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', Fixtures\User::class);

        // Set APP_URL for signed URL generation
        $app['config']->set('app.url', 'http://localhost');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
