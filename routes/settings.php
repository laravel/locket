<?php

use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API Token Management - Using Passport Personal Access Tokens
    Route::post('settings/profile/tokens', [ProfileController::class, 'createToken'])->name('profile.tokens.create');
    Route::delete('settings/profile/tokens/{tokenId}', [ProfileController::class, 'revokeToken'])->name('profile.tokens.revoke');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');
});
