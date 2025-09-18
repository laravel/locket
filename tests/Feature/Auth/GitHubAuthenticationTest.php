<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login redirects to GitHub OAuth', function () {
    $response = $this->get(route('login'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('github.com/login/oauth/authorize');
});

test('GitHub callback creates new user and logs them in', function () {
    $mockUser = new SocialiteUser;
    $mockUser->id = '12345678';
    $mockUser->nickname = 'testuser';
    $mockUser->name = 'Test User';
    $mockUser->email = 'test@example.com';
    $mockUser->avatar = 'https://avatars.githubusercontent.com/u/12345678?v=4';

    Socialite::shouldReceive('driver->user')->andReturn($mockUser);

    $response = $this->get(route('auth.github.callback'));

    $this->assertAuthenticated();
    $response->assertRedirect('/');

    $user = User::where('github_id', '12345678')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->github_username)->toBe('testuser');
    expect($user->avatar)->toBe('https://avatars.githubusercontent.com/u/12345678?v=4');
});

test('GitHub callback updates existing user by GitHub ID', function () {
    $existingUser = User::factory()->create([
        'github_id' => '12345678',
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $mockUser = new SocialiteUser;
    $mockUser->id = '12345678';
    $mockUser->nickname = 'updateduser';
    $mockUser->name = 'Updated User';
    $mockUser->email = 'updated@example.com';
    $mockUser->avatar = 'https://avatars.githubusercontent.com/u/12345678?v=4';

    Socialite::shouldReceive('driver->user')->andReturn($mockUser);

    $response = $this->get(route('auth.github.callback'));

    $this->assertAuthenticated();
    $response->assertRedirect('/');

    $existingUser->refresh();
    expect($existingUser->name)->toBe('Updated User');
    expect($existingUser->email)->toBe('updated@example.com');
    expect($existingUser->github_username)->toBe('updateduser');
    expect($existingUser->avatar)->toBe('https://avatars.githubusercontent.com/u/12345678?v=4');
});

test('GitHub callback updates existing user by email', function () {
    $existingUser = User::factory()->create([
        'github_id' => null,
        'email' => 'same@example.com',
        'name' => 'Old Name',
    ]);

    $mockUser = new SocialiteUser;
    $mockUser->id = '87654321';
    $mockUser->nickname = 'newgithubuser';
    $mockUser->name = 'New GitHub User';
    $mockUser->email = 'same@example.com';
    $mockUser->avatar = 'https://avatars.githubusercontent.com/u/87654321?v=4';

    Socialite::shouldReceive('driver->user')->andReturn($mockUser);

    $response = $this->get(route('auth.github.callback'));

    $this->assertAuthenticated();
    $response->assertRedirect('/');

    $existingUser->refresh();
    expect($existingUser->github_id)->toBe('87654321');
    expect($existingUser->name)->toBe('New GitHub User');
    expect($existingUser->github_username)->toBe('newgithubuser');
    expect($existingUser->avatar)->toBe('https://avatars.githubusercontent.com/u/87654321?v=4');
});

test('GitHub callback handles authentication failure', function () {
    Socialite::shouldReceive('driver->user')->andThrow(new \Exception('OAuth failed'));

    $response = $this->get(route('auth.github.callback'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['email' => 'GitHub authentication failed.']);
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});
