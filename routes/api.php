<?php

use App\Http\Controllers\Api\LinksController;
use App\Http\Controllers\Api\StatusesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    // User endpoint
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Links endpoints
    Route::get('/links/recent', [LinksController::class, 'recent']);
    Route::get('/links/trending', [LinksController::class, 'trending']);
    Route::post('/links', [LinksController::class, 'store']);

    // Statuses endpoints
    Route::get('/statuses/recent', [StatusesController::class, 'recent']);
});
