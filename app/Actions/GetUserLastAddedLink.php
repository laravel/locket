<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\UserLink;

final class GetUserLastAddedLink
{
    /**
     * Get the user's most recently added link with its notes.
     *
     * @return array{user_link: array{id: int, category: string, status: string, created_at: string}, link: array{id: int, url: string, title: string, description: string, category: string}, notes: array<int, array{id: int, note: string, created_at: string}>}|null
     */
    public function handle(User $user): ?array
    {
        /** @var UserLink|null $userLink */
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
            'notes' => $userLink->notes->map(
                function (\App\Models\LinkNote $note, int $key) {
                    return [
                        'id' => $note->id,
                        'note' => $note->note,
                        'created_at' => $note->created_at->diffForHumans(),
                    ];
                }
            )->toArray(),
        ];
    }
}
