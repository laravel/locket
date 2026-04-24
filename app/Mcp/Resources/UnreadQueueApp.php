<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Ui\Enums\Library;

#[Description('Unread reading queue with one-click start-reading action.')]
#[AppMeta(libraries: [Library::Tailwind, Library::Alpine])]
class UnreadQueueApp extends AppResource
{
    public function handle(Request $request): Response
    {
        return Response::view('mcp.unread-queue-app', [
            'title' => $this->title(),
        ]);
    }
}
