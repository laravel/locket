<?php

use App\Actions\GetAllRecentStatuses;
use App\Actions\GetTrendingLinksToday;
use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (GetAllRecentStatuses $statuses, GetTrendingLinksToday $trendingLinks) {
    return Inertia::render('welcome', [
        'statuses' => Inertia::merge($statuses->handle(20))->matchOn('id'),
        'trendingLinks' => $trendingLinks->handle(10),
    ]);
})->name('home');

Route::get('/mcp', function () {
    if (in_array('text/event-stream', request()->getAcceptableContentTypes(), true)) {
        return response('', 405)->header('Allow', 'POST');
    }

    return Inertia::render('mcp');
})->name('mcp');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        try {
            $userLinks = $user->userLinks()
                ->with(['link', 'notes' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($userLink) {
                    return [
                        'id' => $userLink->id,
                        'status' => $userLink->status->value,
                        'category' => $userLink->category->value,
                        'created_at' => $userLink->created_at->toISOString(),
                        'link' => [
                            'id' => $userLink->link->id,
                            'url' => $userLink->link->url,
                            'title' => $userLink->link->title,
                            'description' => $userLink->link->description,
                        ],
                        'notes' => $userLink->notes->map(function ($note) {
                            return [
                                'id' => $note->id,
                                'note' => $note->note,
                                'created_at' => $note->created_at->toISOString(),
                            ];
                        })->toArray(),
                    ];
                })->toArray();
        } catch (Exception $e) {
            // If there's an error with userLinks, default to empty array
            $userLinks = [];
        }

        return Inertia::render('dashboard', [
            'userLinks' => $userLinks,
        ]);
    })->name('dashboard');

    Route::post('status-with-link', [LinkController::class, 'storeStatusWithLink'])->name('status-with-link.store');

    // Link management routes
    Route::post('links', [LinkController::class, 'store'])->name('links.store');
    Route::post('links/notes', [LinkController::class, 'storeNote'])->name('links.notes.store');
    Route::patch('user-links/{userLink}', [LinkController::class, 'update'])->name('user-links.update');
    Route::post('links/{linkId}/bookmark', [LinkController::class, 'bookmark'])->name('links.bookmark');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
