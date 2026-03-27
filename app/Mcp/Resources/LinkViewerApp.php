<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Browse and discover links shared on Locket.')]
#[AppMeta(resourceDomains: ['https://cdn.tailwindcss.com', 'https://cdn.jsdelivr.net'])]
class LinkViewerApp extends AppResource
{
    public function handle(Request $request): Response
    {
        return Response::view('mcp.link-viewer-app', [
            'title' => $this->title(),
        ]);
    }
}
