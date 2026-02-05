<?php

namespace AgileCreativeMinds\DevLoginLink\Tests\Feature;

use AgileCreativeMinds\DevLoginLink\Tests\Fixtures\User;
use AgileCreativeMinds\DevLoginLink\Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

/**
 * Feature tests for the DevLoginController.
 *
 * Tests verify that signed URLs properly authenticate users,
 * handle redirects, and reject invalid, expired, or tampered URLs.
 */
class DevLoginControllerTest extends TestCase
{
    public function test_valid_signed_url_logs_in_user(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $url = URL::temporarySignedRoute(
            'dev.login',
            now()->addMinutes(10),
            ['user' => $user->id]
        );

        $this->assertFalse(Auth::check());

        $response = $this->get($url);

        $response->assertRedirect('/');
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_redirects_to_dashboard_when_route_exists(): void
    {
        // Register a dashboard route
        $this->app['router']->get('/dashboard', fn () => 'Dashboard')->name('dashboard');

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $url = URL::temporarySignedRoute(
            'dev.login',
            now()->addMinutes(10),
            ['user' => $user->id]
        );

        $response = $this->get($url);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_expired_signed_url_is_rejected(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $url = URL::temporarySignedRoute(
            'dev.login',
            now()->subMinutes(1), // Expired 1 minute ago
            ['user' => $user->id]
        );

        $response = $this->get($url);

        $response->assertStatus(403);
        $this->assertFalse(Auth::check());
    }

    public function test_tampered_signed_url_is_rejected(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $url = URL::temporarySignedRoute(
            'dev.login',
            now()->addMinutes(10),
            ['user' => $user->id]
        );

        // Tamper with the URL by changing the user ID
        $tamperedUrl = str_replace('/dev-login/'.$user->id, '/dev-login/999', $url);

        $response = $this->get($tamperedUrl);

        $response->assertStatus(403);
        $this->assertFalse(Auth::check());
    }

    public function test_unsigned_url_is_rejected(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->get('/dev-login/'.$user->id);

        $response->assertStatus(403);
        $this->assertFalse(Auth::check());
    }

    public function test_nonexistent_user_returns_404(): void
    {
        $url = URL::temporarySignedRoute(
            'dev.login',
            now()->addMinutes(10),
            ['user' => 999]
        );

        $response = $this->get($url);

        $response->assertStatus(404);
    }
}
