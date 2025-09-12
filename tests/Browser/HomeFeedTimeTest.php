<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserStatus;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows relative time for recent statuses', function () {
    $user = User::factory()->create(['name' => 'timetest']);
    // Create a status 2 hours ago
    $status = UserStatus::factory()->for($user)->create(['status' => 'Recent status']);
    $status->created_at = now()->subHours(2);
    $status->save();

    $page = visit('/');

    $page->assertSee('Recent status');
});
