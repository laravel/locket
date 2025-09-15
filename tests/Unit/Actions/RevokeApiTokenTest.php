<?php

declare(strict_types=1);

use App\Actions\RevokeApiToken;
use App\Models\User;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

it('revokes an existing token', function () {
    $user = User::factory()->create();
    $token = $user->createSanctumToken('Test Token');
    $action = new RevokeApiToken;

    $result = $action->handle($user, $token->accessToken->id);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});

it('returns false when token does not exist', function () {
    $user = User::factory()->create();
    $action = new RevokeApiToken;

    $result = $action->handle($user, 999);

    expect($result)->toBeFalse();
});

it('cannot revoke another users token', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $token = $user1->createSanctumToken('Test Token');
    $action = new RevokeApiToken;

    $result = $action->handle($user2, $token->accessToken->id);

    expect($result)->toBeFalse();
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});
