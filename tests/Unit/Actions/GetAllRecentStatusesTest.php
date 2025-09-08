<?php

declare(strict_types=1);

use App\Actions\GetAllRecentStatuses;
use App\Models\User;
use App\Models\UserStatus;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it returns recent statuses from all users', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);

    // Create statuses for different users
    $status1 = UserStatus::factory()->create(['user_id' => $user1->id, 'created_at' => now()->subHours(2)]);
    $status2 = UserStatus::factory()->create(['user_id' => $user2->id, 'created_at' => now()->subHour()]);
    $status3 = UserStatus::factory()->create(['user_id' => $user1->id, 'created_at' => now()]);

    $action = new GetAllRecentStatuses;
    $statuses = $action->handle();

    expect($statuses->count())->toBe(3);
    expect($statuses->first()->id)->toBe($status3->id); // Most recent first
    expect($statuses->get(1)->id)->toBe($status2->id);
    expect($statuses->last()->id)->toBe($status1->id);

    // Check that users are loaded
    expect($statuses->first()->user->name)->toBe('Alice');
    expect($statuses->get(1)->user->name)->toBe('Bob');
});

test('it respects the limit parameter', function () {
    $user = User::factory()->create();

    // Create 5 statuses
    UserStatus::factory()->count(5)->create(['user_id' => $user->id]);

    $action = new GetAllRecentStatuses;
    $statuses = $action->handle(3);

    expect($statuses->count())->toBe(3);
});

test('it returns empty collection when no statuses exist', function () {
    $action = new GetAllRecentStatuses;
    $statuses = $action->handle();

    expect($statuses->count())->toBe(0);
    expect($statuses->isEmpty())->toBeTrue();
});

test('it eager loads user relationships', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    UserStatus::factory()->create(['user_id' => $user->id]);

    $action = new GetAllRecentStatuses;
    $statuses = $action->handle();

    expect($statuses->first()->relationLoaded('user'))->toBeTrue();
    expect($statuses->first()->user->name)->toBe('Test User');
});
