<?php

use AgileCreativeMinds\DevLoginLink\Http\Controllers\DevLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/dev-login/{user}', DevLoginController::class)
    ->name('dev.login')
    ->middleware(['web', 'signed']);
