<?php

declare(strict_types=1);

use App\Http\Resources\UserStatusResource;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('formats user status with correct avatar URLs', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'github_username' => 'johndoe',
    ]);

    $status = UserStatus::factory()->create([
        'user_id' => $user->id,
        'status' => 'Hello world!',
    ]);

    $status->load('user');

    $resource = new UserStatusResource($status);
    $result = $resource->toArray(new Request);

    expect($result)->toHaveKeys(['id', 'status', 'created_at', 'user'])
        ->and($result['id'])->toBe($status->id)
        ->and($result['status'])->toBe('Hello world!')
        ->and($result['created_at'])->toBe($status->created_at->toAtomString())
        ->and($result['user'])->toHaveKeys(['name', 'github_username', 'avatar', 'avatar_fallback'])
        ->and($result['user']['name'])->toBe('johndoe')  // Now displays github_username
        ->and($result['user']['github_username'])->toBe('johndoe')
        ->and($result['user']['avatar'])->toContain('avatars.githubusercontent.com')  // GitHub avatar
        ->and($result['user']['avatar_fallback'])->toContain('avatars.laravel.cloud');
});

it('handles missing user gracefully', function () {
    $status = UserStatus::factory()->make([
        'status' => 'Test status',
        'user_id' => null,
    ]);
    $status->user = null;

    $resource = new UserStatusResource($status);
    $result = $resource->toArray(new Request);

    expect($result['user']['name'])->toBe('Unknown')
        ->and($result['user']['avatar'])->toContain('gravatar.com/avatar')
        ->and($result['user']['avatar_fallback'])->toContain('avatars.laravel.cloud');
});

it('creates consistent format with GetAllRecentStatuses action', function () {
    $user = User::factory()->create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'github_username' => 'janesmith',
    ]);

    $status = UserStatus::factory()->create([
        'user_id' => $user->id,
        'status' => 'Testing consistency!',
    ]);

    // Test resource format
    $status->load('user');
    $resource = new UserStatusResource($status);
    $resourceResult = $resource->toArray(new Request);

    // Test action format
    $action = new \App\Actions\GetAllRecentStatuses;
    $actionResults = $action->handle(1, $user);

    expect($actionResults)->toHaveCount(1)
        ->and($actionResults[0])->toHaveKeys(['id', 'status', 'created_at', 'user'])
        ->and($actionResults[0]['id'])->toBe($resourceResult['id'])
        ->and($actionResults[0]['status'])->toBe($resourceResult['status'])
        ->and($actionResults[0]['created_at'])->toBe($resourceResult['created_at'])
        ->and($actionResults[0]['user'])->toBe($resourceResult['user']);
});

it('provides convenient toFrontendFormat method', function () {
    $user = User::factory()->create([
        'name' => 'Bob Wilson',
        'email' => 'bob@example.com',
        'github_username' => 'bobwilson',
    ]);

    $status = UserStatus::factory()->create([
        'user_id' => $user->id,
        'status' => 'Convenience method test!',
    ]);

    $result = $status->toFrontendFormat();

    expect($result)->toHaveKeys(['id', 'status', 'created_at', 'user'])
        ->and($result['id'])->toBe($status->id)
        ->and($result['status'])->toBe('Convenience method test!')
        ->and($result['user']['name'])->toBe('bobwilson')  // Now displays github_username
        ->and($result['user']['avatar'])->toContain('avatars.githubusercontent.com')  // GitHub avatar
        ->and($result['user']['avatar_fallback'])->toContain('avatars.laravel.cloud');
});
