<?php

declare(strict_types=1);

use App\Actions\GetRecentUserStatuses;
use App\Models\User;
use App\Models\UserStatus;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it returns recent statuses for a user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create statuses for the user
    $status1 = UserStatus::factory()->create(['user_id' => $user->id, 'created_at' => now()->subHours(2)]);
    $status2 = UserStatus::factory()->create(['user_id' => $user->id, 'created_at' => now()->subHour()]);
    $status3 = UserStatus::factory()->create(['user_id' => $user->id, 'created_at' => now()]);

    // Create status for other user (should not be included)
    UserStatus::factory()->create(['user_id' => $otherUser->id]);

    $action = new GetRecentUserStatuses;
    $statuses = $action->handle($user);

    expect($statuses->count())->toBe(3);
    expect($statuses->first()->id)->toBe($status3->id); // Most recent first
    expect($statuses->get(1)->id)->toBe($status2->id);
    expect($statuses->last()->id)->toBe($status1->id);
});

test('it respects the limit parameter', function () {
    $user = User::factory()->create();

    // Create 5 statuses
    UserStatus::factory()->count(5)->create(['user_id' => $user->id]);

    $action = new GetRecentUserStatuses;
    $statuses = $action->handle($user, 3);

    expect($statuses->count())->toBe(3);
});

test('it returns empty collection when user has no statuses', function () {
    $user = User::factory()->create();

    $action = new GetRecentUserStatuses;
    $statuses = $action->handle($user);

    expect($statuses->count())->toBe(0);
    expect($statuses->isEmpty())->toBeTrue();
});
