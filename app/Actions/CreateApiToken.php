<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Laravel\Passport\PersonalAccessTokenResult;

final class CreateApiToken
{
    public function handle(User $user, string $name, array $scopes = []): PersonalAccessTokenResult
    {
        return $user->createToken($name, $scopes);
    }
}
