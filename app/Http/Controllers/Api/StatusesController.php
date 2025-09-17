<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GetAllRecentStatuses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusesController extends Controller
{
    /**
     * Get recent status messages from all users.
     */
    public function recent(Request $request, GetAllRecentStatuses $getRecentStatuses): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = (int) ($request->input('limit', 10));
        $statuses = $getRecentStatuses->handle($limit);

        return response()->json([
            'data' => $statuses,
            'meta' => [
                'count' => count($statuses),
                'limit' => $limit,
            ],
        ]);
    }
}
