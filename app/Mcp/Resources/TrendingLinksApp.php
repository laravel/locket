<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Ui\Enums\Library;

#[Description('Visual feed of trending links on Locket with one-click bookmark and summarise actions.')]
#[AppMeta(libraries: [Library::Tailwind, Library::Alpine])]
class TrendingLinksApp extends AppResource
{
    public function handle(Request $request): Response
    {
        return Response::view('mcp.trending-links-app', [
            'title' => $this->title(),
        ]);
    }
}
