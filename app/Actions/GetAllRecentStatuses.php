<?php

declare(strict_types=1);

namespace App\Actions;

use App\Http\Resources\UserStatusResource;
use App\Models\User;
use App\Models\UserStatus;

final class GetAllRecentStatuses
{
    /**
     * Get recent statuses as minimal payload.
     *
     * When a user is provided, only that user's statuses are returned.
     */
    public function handle(int $limit = 10, ?User $user = null): array
    {
        $query = UserStatus::query()
            ->with([
                'user' => function ($q) {
                    // Select only what's needed to compute avatar and name
                    $q->select('id', 'name', 'email', 'github_username', 'avatar');
                },
                'link' => function ($q) {
                    // Select link data for display...
                    $q->select('id', 'url', 'title', 'description');
                },
            ])
            ->latest();

        if ($user !== null) {
            $query->where('user_id', $user->id);
        }

        $statuses = $query->limit($limit)->get();

        return UserStatusResource::collection($statuses)->toArray(request());
    }
}
