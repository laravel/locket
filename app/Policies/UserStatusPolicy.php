<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserStatus;

class UserStatusPolicy
{
    /**
     * Determine whether the user can view the user status.
     */
    public function view(User $user, UserStatus $userStatus): bool
    {
        // Statuses are generally public, but only the owner can see all details
        return true;
    }

    /**
     * Determine whether the user can update the user status.
     */
    public function update(User $user, UserStatus $userStatus): bool
    {
        return $user->id === $userStatus->user_id;
    }

    /**
     * Determine whether the user can delete the user status.
     */
    public function delete(User $user, UserStatus $userStatus): bool
    {
        return $user->id === $userStatus->user_id;
    }
}
