<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Link;

final class GetRecentLinks
{
    /**
     * Get the most recently added links.
     */
    public function handle(int $limit = 10): array
    {
        $recentLinks = Link::with('submittedBy:id,name')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($link) {
                return [
                    'id' => $link->id,
                    'url' => $link->url,
                    'title' => $link->title,
                    'description' => $link->description,
                    'category' => $link->category->value,
                    'submitted_by' => $link->submittedBy?->name ?? 'Anonymous',
                    'created_at' => $link->created_at->diffForHumans(),
                ];
            });

        return $recentLinks->toArray();
    }
}
