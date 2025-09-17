<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final class RevokeApiToken
{
    public function handle(User $user, string $tokenId): bool
    {
        $token = $user->tokens()->where('id', $tokenId)->first();

        if (! $token) {
            return false;
        }

        $token->revoke();

        return true;
    }
}
