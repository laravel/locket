<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

final class CreateApiToken
{
    public function handle(User $user, string $name, array $abilities = ['*']): NewAccessToken
    {
        return $user->createSanctumToken($name, $abilities);
    }
}
