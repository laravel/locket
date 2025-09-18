<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [SocialiteController::class, 'redirectToGitHub'])
        ->name('login');

    Route::get('auth/github', [SocialiteController::class, 'redirectToGitHub'])
        ->name('auth.github');

    Route::get('auth/github/callback', [SocialiteController::class, 'handleGitHubCallback'])
        ->name('auth.github.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
