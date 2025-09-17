<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Link;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class GetTrendingLinksToday
{
    /**
     * Get the top trending links for today based on user bookmarks.
     */
    public function handle(int $limit = 10): array
    {
        $today = Carbon::today();

        // First get the trending link IDs with counts...
        $trendingLinkIds = DB::table('user_links')
            ->select('link_id')
            ->selectRaw('COUNT(*) as bookmark_count')
            ->whereDate('created_at', $today)
            ->groupBy('link_id')
            ->orderByDesc('bookmark_count')
            ->limit($limit)
            ->get()
            ->keyBy('link_id');

        if ($trendingLinkIds->isEmpty()) {
            return [];
        }

        // Then, get the full link details...
        $trendingLinks = Link::whereIn('id', $trendingLinkIds->keys())
            ->get()
            ->map(function ($link) use ($trendingLinkIds) {
                return [
                    'id' => $link->id,
                    'url' => $link->url,
                    'title' => $link->title,
                    'description' => $link->description,
                    'category' => $link->category->value,
                    'bookmark_count' => $trendingLinkIds[$link->id]->bookmark_count,
                ];
            })
            ->sortByDesc('bookmark_count')
            ->values();

        return $trendingLinks->toArray();
    }
}
