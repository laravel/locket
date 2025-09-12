<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserStatus;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('renders recent statuses on the home page', function () {
    $user = User::factory()->create(['name' => 'janedoe']);
    UserStatus::factory()->for($user)->create(['status' => 'Hello world']);

    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('welcome')
        ->has('statuses', 1)
        ->where('statuses.0.status', 'Hello world')
        ->where('statuses.0.user.name', 'janedoe')
        ->has('statuses.0.user.avatar')
    );
});
