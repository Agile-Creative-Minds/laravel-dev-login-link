# Laravel Dev Login Link

[![Latest Version](https://img.shields.io/packagist/v/agile-creative-minds/laravel-dev-login-link.svg?label=version)](https://packagist.org/packages/agile-creative-minds/laravel-dev-login-link)
[![Packagist Downloads](https://img.shields.io/packagist/dm/agile-creative-minds/laravel-dev-login-link.svg?label=downloads)](https://packagist.org/packages/agile-creative-minds/laravel-dev-login-link)
[![CI](https://github.com/Agile-Creative-Minds/laravel-dev-login-link/actions/workflows/ci.yml/badge.svg)](https://github.com/Agile-Creative-Minds/laravel-dev-login-link/actions)
[![License](https://img.shields.io/packagist/l/agile-creative-minds/laravel-dev-login-link.svg)](LICENSE)

A dev-only Artisan command that generates one-time login links for Laravel.

**Why this exists:** During local development, you often need to quickly log in as different users without resetting passwords or navigating login forms. This package provides a single command to generate a secure, short-lived login link — saving time and reducing friction in your dev workflow.

## Installation

```bash
composer require agile-creative-minds/laravel-dev-login-link --dev
```

The package auto-discovers, so no manual service provider registration is needed.

## Usage

```bash
# Generate a login link for the first user
# (if no users exist, a default admin user admin@example.com is created)
php artisan dev:login-link

# Generate a login link for a specific user
php artisan dev:login-link 5
```

The generated URL is valid for 10 minutes.

## Security

This package is designed for **local development only**:

- Routes are only registered when `APP_ENV` is `local`, `development`, or `testing`
- The command refuses to run in production environments
- URLs use Laravel's signed routes with short expiration times
- Requires shell/container access to generate links

**Never enable this in production.**

## How It Works

1. The command generates a temporary signed URL using `URL::temporarySignedRoute('dev.login', ...)`
2. When visited, the `signed` middleware verifies the URL hasn't been tampered with or expired
3. If valid, the user is logged in via `Auth::login()` and redirected to `/dashboard` (or `/` if no dashboard route exists)

## Troubleshooting

### "I clicked the link but nothing seems different"

On a fresh Laravel install without an auth starter kit (Breeze, Jetstream, etc.), there's no `/dashboard` route or visible auth UI. The login **is working**, but you're redirected to `/` which doesn't show login status.

**Solution:** Install Laravel Breeze for a complete auth experience:

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
php artisan migrate
```

After this, the login link will redirect you to `/dashboard` where you'll see you're authenticated.

### "I get redirected to the login page"

This usually means the session isn't persisting. Make sure your Laravel app has:

- `SESSION_DRIVER` set in `.env` (e.g., `database`, `file`, `cookie`)
- The `sessions` table migrated if using `database` driver
- The `/dev-login/{user}` route uses the `web` middleware group (handled by the package by default)

### "Route [dev.login] not defined"

The route only loads in `local`, `development`, or `testing` environments. Check your `APP_ENV` in `.env`:

```env
APP_ENV=local
```

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x (any app using the default `web` guard and `users` provider)
- For best experience: Laravel Breeze, Jetstream, or custom auth scaffolding

## License

MIT
