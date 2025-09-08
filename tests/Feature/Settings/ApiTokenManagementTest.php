<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can display tokens on profile page', function () {
    $user = User::factory()->create();
    $user->createToken('Test Token');

    $response = actingAs($user)->get('/settings/profile');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/profile')
        ->has('tokens', 1)
        ->where('tokens.0.name', 'Test Token')
    );
});

it('can create an api token', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/settings/profile/tokens', [
        'name' => 'Mobile App Token',
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'token',
        'accessToken' => ['id', 'name', 'last_used_at', 'created_at'],
    ]);

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
        'name' => 'Mobile App Token',
    ]);
});

it('validates token name when creating', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/settings/profile/tokens', [
        'name' => '',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name']);
});

it('validates token name length when creating', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/settings/profile/tokens', [
        'name' => str_repeat('a', 256),
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name']);
});

it('can revoke an api token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Token');

    $response = actingAs($user)->deleteJson("/settings/profile/tokens/{$token->accessToken->id}");

    $response->assertSuccessful();
    $response->assertJson(['message' => 'Token revoked successfully']);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});

it('cannot revoke a token that does not exist', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->deleteJson('/settings/profile/tokens/999');

    $response->assertNotFound();
    $response->assertJson(['error' => 'Token not found']);
});

it('cannot revoke another users token', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $token = $user1->createToken('User 1 Token');

    $response = actingAs($user2)->deleteJson("/settings/profile/tokens/{$token->accessToken->id}");

    $response->assertNotFound();

    // Token should still exist
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});

it('requires authentication to create tokens', function () {
    $response = $this->postJson('/settings/profile/tokens', [
        'name' => 'Test Token',
    ]);

    $response->assertUnauthorized();
});

it('requires authentication to revoke tokens', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Token');

    $response = $this->deleteJson("/settings/profile/tokens/{$token->accessToken->id}");

    $response->assertUnauthorized();
});

it('creates tokens with correct abilities', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->postJson('/settings/profile/tokens', [
        'name' => 'Test Token',
    ]);

    $response->assertSuccessful();

    $token = PersonalAccessToken::where('name', 'Test Token')->first();
    expect($token->abilities)->toBe(['*']);
});

it('tokens are ordered by creation date desc', function () {
    $user = User::factory()->create();

    // Create some tokens
    $user->createToken('First Token');
    $user->createToken('Second Token');
    $user->createToken('Third Token');

    $response = actingAs($user)->get('/settings/profile');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/profile')
        ->has('tokens', 3)
    );

    // Verify that tokens array exists and has the right structure
    $tokens = $response->viewData('page')['props']['tokens'];
    expect($tokens)->toHaveCount(3);
    expect($tokens[0])->toHaveKey('name');
    expect($tokens[0])->toHaveKey('created_at');
});
