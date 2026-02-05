<?php

namespace AgileCreativeMinds\DevLoginLink\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * User model fixture for testing.
 *
 * A minimal User model that provides the necessary authentication
 * functionality for testing the dev login link package.
 */
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
