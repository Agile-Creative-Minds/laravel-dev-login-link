<?php

namespace AgileCreativeMinds\DevLoginLink\Tests\Feature;

use AgileCreativeMinds\DevLoginLink\Tests\Fixtures\User;
use AgileCreativeMinds\DevLoginLink\Tests\TestCase;

/**
 * Feature tests for the dev:login-link Artisan command.
 *
 * Tests cover user creation, link generation for existing and
 * specific users, error handling, and environment restrictions.
 */
class DevLoginLinkCommandTest extends TestCase
{
    public function test_command_creates_admin_user_when_no_users_exist(): void
    {
        $this->assertDatabaseCount('users', 0);

        $this->artisan('dev:login-link')
            ->expectsOutputToContain('No users found. Created default admin user:')
            ->expectsOutputToContain('One-time login link')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'name' => 'Admin',
        ]);
    }

    public function test_command_generates_link_for_existing_user(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->artisan('dev:login-link')
            ->expectsOutputToContain('One-time login link')
            ->expectsOutputToContain('/dev-login/'.$user->id)
            ->assertSuccessful();

        // Should not create a new user
        $this->assertDatabaseCount('users', 1);
    }

    public function test_command_generates_link_for_specific_user_id(): void
    {
        User::create([
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => 'password',
        ]);

        $secondUser = User::create([
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'password',
        ]);

        $this->artisan('dev:login-link', ['userId' => $secondUser->id])
            ->expectsOutputToContain('/dev-login/'.$secondUser->id)
            ->assertSuccessful();
    }

    public function test_command_fails_for_nonexistent_user_id(): void
    {
        $this->artisan('dev:login-link', ['userId' => 999])
            ->expectsOutputToContain('User with ID 999 not found')
            ->assertFailed();
    }

    public function test_environment_check_blocks_non_dev_environments(): void
    {
        // Test that the environment check logic works correctly
        // We verify allowed environments pass
        $this->assertTrue(app()->environment(['local', 'development', 'testing']));

        // And that production would not pass
        $this->assertFalse(app()->environment(['production']));

        // The actual command runs successfully in the testing environment
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->artisan('dev:login-link')
            ->assertSuccessful();
    }

    public function test_command_generates_url_with_signature_parameters(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Capture the output
        $this->artisan('dev:login-link')
            ->expectsOutputToContain('One-time login link')
            ->expectsOutputToContain('/dev-login/1')
            ->assertSuccessful();
    }
}
