<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::post('status', [App\Http\Controllers\StatusController::class, 'store'])->name('status.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
