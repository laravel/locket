<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\UserLink;

final class GetUserLastAddedLink
{
    /**
     * Get the user's most recently added link with its notes.
     */
    public function handle(User $user): ?array
    {
        $userLink = UserLink::with(['link', 'notes' => function ($query) use ($user) {
            $query->forUser($user->id)->recent();
        }])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->first();

        if (! $userLink) {
            return null;
        }

        return [
            'user_link' => [
                'id' => $userLink->id,
                'category' => $userLink->category->value,
                'status' => $userLink->status->value,
                'created_at' => $userLink->created_at->diffForHumans(),
            ],
            'link' => [
                'id' => $userLink->link->id,
                'url' => $userLink->link->url,
                'title' => $userLink->link->title,
                'description' => $userLink->link->description,
                'category' => $userLink->link->category->value,
            ],
            'notes' => $userLink->notes->map(function ($note) {
                return [
                    'id' => $note->id,
                    'note' => $note->note,
                    'created_at' => $note->created_at->diffForHumans(),
                ];
            })->toArray(),
        ];
    }
}
