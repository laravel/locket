<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LinkNote;
use App\Models\User;

class LinkNotePolicy
{
    /**
     * Determine whether the user can view the link note.
     */
    public function view(User $user, LinkNote $linkNote): bool
    {
        return $user->id === $linkNote->user_id;
    }

    /**
     * Determine whether the user can update the link note.
     */
    public function update(User $user, LinkNote $linkNote): bool
    {
        return $user->id === $linkNote->user_id;
    }

    /**
     * Determine whether the user can delete the link note.
     */
    public function delete(User $user, LinkNote $linkNote): bool
    {
        return $user->id === $linkNote->user_id;
    }
}
