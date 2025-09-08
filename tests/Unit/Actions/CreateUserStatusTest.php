<?php

declare(strict_types=1);

use App\Actions\CreateUserStatus;
use App\Models\User;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it creates a status for a user', function () {
    $user = User::factory()->create();
    $statusText = 'Working on something exciting!';

    $action = new CreateUserStatus;
    $status = $action->handle($user, $statusText);

    expect($status->user_id)->toBe($user->id);
    expect($status->status)->toBe($statusText);
    expect($status->exists)->toBeTrue();
});

test('it saves the status to the database', function () {
    $user = User::factory()->create();
    $statusText = 'Currently in a meeting';

    $action = new CreateUserStatus;
    $action->handle($user, $statusText);

    $this->assertDatabaseHas('user_statuses', [
        'user_id' => $user->id,
        'status' => $statusText,
    ]);
});
