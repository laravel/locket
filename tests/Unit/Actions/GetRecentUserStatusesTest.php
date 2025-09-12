<?php

declare(strict_types=1);

use App\Actions\GetAllRecentStatuses;
use App\Models\User;
use App\Models\UserStatus;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it returns recent statuses for a user (merged action)', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create statuses for the user
    $status1 = UserStatus::factory()->create(['user_id' => $user->id, 'created_at' => now()->subHours(2)]);
    $status2 = UserStatus::factory()->create(['user_id' => $user->id, 'created_at' => now()->subHour()]);
    $status3 = UserStatus::factory()->create(['user_id' => $user->id, 'created_at' => now()]);

    // Create status for other user (should not be included)
    UserStatus::factory()->create(['user_id' => $otherUser->id]);

    $action = new GetAllRecentStatuses;
    $statuses = $action->handle(10, $user);

    expect(count($statuses))->toBe(3);
});
