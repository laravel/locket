<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\UserLink;

final class GetUserUnreadLinks
{
    /**
     * Get the user's unread reading queue.
     *
     * @return array<int, array{user_link_id: int, url: string, title: string, description: string, category: string}>
     */
    public function handle(User $user, int $limit = 25): array
    {
        return UserLink::query()
            ->unread()
            ->where('user_id', $user->id)
            ->with('link:id,url,title,description,category')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (UserLink $userLink): array => [
                'user_link_id' => $userLink->id,
                'url' => $userLink->link->url,
                'title' => $userLink->link->title,
                'description' => $userLink->link->description,
                'category' => $userLink->category->value,
            ])
            ->all();
    }
}
