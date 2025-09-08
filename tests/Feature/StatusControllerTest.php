<?php

declare(strict_types=1);

use App\Models\User;

test('authenticated user can create a status', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/status', [
            'status' => 'Working on something exciting!',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('message', 'Status updated successfully!');

    $this->assertDatabaseHas('user_statuses', [
        'user_id' => $user->id,
        'status' => 'Working on something exciting!',
    ]);
});

test('status is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/status', [
            'status' => '',
        ]);

    $response->assertSessionHasErrors(['status']);
});

test('status cannot exceed 500 characters', function () {
    $user = User::factory()->create();
    $longStatus = str_repeat('a', 501);

    $response = $this->actingAs($user)
        ->post('/status', [
            'status' => $longStatus,
        ]);

    $response->assertSessionHasErrors(['status']);
});

test('unauthenticated user cannot create status', function () {
    $response = $this->post('/status', [
        'status' => 'Test status',
    ]);

    $response->assertRedirect('/login');
});
