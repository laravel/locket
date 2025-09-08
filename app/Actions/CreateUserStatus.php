<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\UserStatus;

final class CreateUserStatus
{
    /**
     * Create a new status for a user.
     */
    public function handle(User $user, string $status): UserStatus
    {
        return $user->statuses()->create([
            'status' => $status,
        ]);
    }
}
