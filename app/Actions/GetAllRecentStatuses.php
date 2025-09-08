<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\UserStatus;
use Illuminate\Database\Eloquent\Collection;

final class GetAllRecentStatuses
{
    /**
     * Get recent statuses from all users.
     */
    public function handle(int $limit = 10): Collection
    {
        return UserStatus::with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
