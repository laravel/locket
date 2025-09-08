<?php

declare(strict_types=1);

use App\Actions\CreateApiToken;
use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates an api token with default abilities', function () {
    $user = User::factory()->create();
    $action = new CreateApiToken;

    $result = $action->handle($user, 'Test Token');

    expect($result)->toBeInstanceOf(NewAccessToken::class);
    expect($result->accessToken->name)->toBe('Test Token');
    expect($result->accessToken->tokenable_id)->toBe($user->id);
    expect($result->accessToken->abilities)->toBe(['*']);
});

it('creates an api token with custom abilities', function () {
    $user = User::factory()->create();
    $action = new CreateApiToken;
    $abilities = ['read', 'write'];

    $result = $action->handle($user, 'Test Token', $abilities);

    expect($result)->toBeInstanceOf(NewAccessToken::class);
    expect($result->accessToken->name)->toBe('Test Token');
    expect($result->accessToken->tokenable_id)->toBe($user->id);
    expect($result->accessToken->abilities)->toBe($abilities);
});

it('returns a plain text token', function () {
    $user = User::factory()->create();
    $action = new CreateApiToken;

    $result = $action->handle($user, 'Test Token');

    expect($result->plainTextToken)->toBeString();
    expect($result->plainTextToken)->toContain('|');
});
