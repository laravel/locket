<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserLink;

class UserLinkPolicy
{
    /**
     * Determine whether the user can view the user link.
     */
    public function view(User $user, UserLink $userLink): bool
    {
        return $user->id === $userLink->user_id;
    }

    /**
     * Determine whether the user can update the user link.
     */
    public function update(User $user, UserLink $userLink): bool
    {
        return $user->id === $userLink->user_id;
    }

    /**
     * Determine whether the user can delete the user link.
     */
    public function delete(User $user, UserLink $userLink): bool
    {
        return $user->id === $userLink->user_id;
    }

    /**
     * Determine whether the user can add notes to this user link.
     */
    public function addNote(User $user, UserLink $userLink): bool
    {
        return $user->id === $userLink->user_id;
    }
}
