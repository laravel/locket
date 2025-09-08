<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class GetRecentUserStatuses
{
    /**
     * Get recent statuses for a user.
     */
    public function handle(User $user, int $limit = 10): Collection
    {
        return $user->statuses()
            ->latest()
            ->limit($limit)
            ->get();
    }
}
