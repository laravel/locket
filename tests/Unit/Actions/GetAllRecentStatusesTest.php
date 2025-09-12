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

    expect(count($statuses))->toBe(3);
    expect($statuses[0]['user']['name'])->toBe('Alice');
    expect($statuses[1]['user']['name'])->toBe('Bob');
});

test('it respects the limit parameter', function () {
    $user = User::factory()->create();

    // Create 5 statuses
    UserStatus::factory()->count(5)->create(['user_id' => $user->id]);

    $action = new GetAllRecentStatuses;
    $statuses = $action->handle(3);

    expect(count($statuses))->toBe(3);
});

test('it returns empty collection when no statuses exist', function () {
    $action = new GetAllRecentStatuses;
    $statuses = $action->handle();

    expect($statuses)->toBeArray();
    expect(count($statuses))->toBe(0);
});

test('it eager loads user relationships', function () {
    $user = User::factory()->create(['name' => 'testuser']);
    UserStatus::factory()->create(['user_id' => $user->id]);

    $action = new GetAllRecentStatuses;
    $statuses = $action->handle();

    expect($statuses[0]['user']['name'])->toBe('testuser');
});
