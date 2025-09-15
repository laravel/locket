<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final class RevokeApiToken
{
    public function handle(User $user, int $tokenId): bool
    {
        return $user->sanctumTokens()->where('id', $tokenId)->delete() > 0;
    }
}
