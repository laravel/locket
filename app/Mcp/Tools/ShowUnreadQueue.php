<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\GetUserUnreadLinks;
use App\Mcp\Resources\UnreadQueueApp;
use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Tool;

#[RendersApp(resource: UnreadQueueApp::class)]
class ShowUnreadQueue extends Tool
{
    public function __construct(
        protected GetUserUnreadLinks $getUserUnreadLinks,
    ) {}

    protected string $description = 'Open your unread reading queue. Lists your bookmarked links still marked as unread with one-click "Start reading" buttons.';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Authentication required to open your reading queue.');
        }

        $items = $this->getUserUnreadLinks->handle($user);

        return Response::structured([
            'items' => $items,
            'message' => $items === []
                ? 'Your reading queue is empty. Bookmark a link to get started.'
                : 'Your unread reading queue.',
        ]);
    }
}
