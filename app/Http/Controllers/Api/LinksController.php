<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateStatusWithLink;
use App\Actions\GetRecentLinks;
use App\Actions\GetTrendingLinksToday;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddLinkRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinksController extends Controller
{
    /**
     * Get the most recently added links.
     */
    public function recent(Request $request, GetRecentLinks $getRecentLinks): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:25',
        ]);

        $limit = (int) ($request->input('limit', 10));
        $links = $getRecentLinks->handle($limit);

        return response()->json([
            'data' => $links,
            'meta' => [
                'count' => count($links),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Get trending links for today.
     */
    public function trending(Request $request, GetTrendingLinksToday $getTrendingLinks): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:25',
        ]);

        $limit = (int) ($request->input('limit', 10));
        $links = $getTrendingLinks->handle($limit);

        return response()->json([
            'data' => $links,
            'meta' => [
                'count' => count($links),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Add a new link.
     */
    public function store(AddLinkRequest $request, CreateStatusWithLink $createStatusWithLink): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $createStatusWithLink->handle(
                $validated['url'],
                $validated['thoughts'] ?? null,
                $request->user(),
                $validated['category_hint'] ?? null
            );

            return response()->json([
                'data' => [
                    'link' => $result['link'],
                    'user_link' => $result['user_link'],
                    'status' => $result['status'],
                    'note' => $result['note'] ?? null,
                ],
                'meta' => [
                    'already_bookmarked' => $result['already_bookmarked'],
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to add link',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
