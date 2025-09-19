<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    // Create personal access client for testing
    Artisan::call('passport:client', ['--personal' => true, '--no-interaction' => true]);
});

it('can create a personal access token', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('profile.tokens.create'), [
        'name' => 'Test Token',
    ]);

    $response->assertRedirect();
    expect($user->fresh()->tokens()->count())->toBe(1);
    expect($user->fresh()->tokens()->first()->name)->toBe('Test Token');
});

it('can list personal access tokens', function () {
    $user = User::factory()->create();

    // Create some tokens with sleep to ensure different timestamps
    $token1 = $user->createToken('Token 1');
    sleep(1);
    $token2 = $user->createToken('Token 2');

    $this->actingAs($user);

    $response = $this->get(route('profile.edit'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/profile')
        ->has('tokens', 2)
        ->where('tokens.0.name', 'Token 2') // Most recent first
        ->where('tokens.1.name', 'Token 1')
    );
});

it('can revoke a personal access token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Token');

    $this->actingAs($user);

    $response = $this->delete(route('profile.tokens.revoke', $token->token->id));

    $response->assertRedirect();

    // Check token is revoked
    $user->refresh();
    expect($user->tokens()->where('id', $token->token->id)->first()->revoked)->toBe(true);
});

it('cannot revoke a token that does not belong to the user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $token = $user2->createToken('Other User Token');

    $this->actingAs($user1);

    $response = $this->delete(route('profile.tokens.revoke', $token->token->id));

    $response->assertRedirect();
    $response->assertSessionHasErrors('token');
});

it('validates token name when creating', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('profile.tokens.create'), [
        'name' => '',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('name');
});

it('can use personal access token to access API', function () {
    $user = User::factory()->create();
    $tokenResult = $user->createToken('API Token');

    $response = $this->getJson('/api/user', [
        'Authorization' => 'Bearer '.$tokenResult->accessToken,
    ]);

    $response->assertSuccessful();
    expect($response->json('id'))->toBe($user->id);
    expect($response->json('name'))->toBe($user->name);
    expect($response->json('email'))->toBe($user->email);
});

it('cannot access API without valid token', function () {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});

it('cannot access API with revoked token', function () {
    $user = User::factory()->create();
    $tokenResult = $user->createToken('API Token');

    // Revoke the token
    $tokenResult->token->revoke();

    $response = $this->getJson('/api/user', [
        'Authorization' => 'Bearer '.$tokenResult->accessToken,
    ]);

    $response->assertUnauthorized();
});
