<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\GetTrendingLinksToday;
use App\Mcp\Resources\LinkViewerApp;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Tool;

#[RendersApp(resource: LinkViewerApp::class)]
class GetTrendingLinks extends Tool
{
    public function __construct(
        protected GetTrendingLinksToday $getTrendingLinksToday
    ) {}

    protected string $description = 'Get trending links that are popular today based on how many users have bookmarked them. Shows what the Locket community is reading right now.';

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'limit' => 'numeric|min:1|max:25',
        ], [
            'limit' => 'Invalid limit, must be numeric, minimum of 1, and maximum of 25',
        ]);

        $limit = (int) ($validated['limit'] ?? 10);

        $trendingLinks = $this->getTrendingLinksToday->handle($limit);

        if (empty($trendingLinks)) {
            return Response::structured([
                'links' => [],
                'message' => 'No trending links found today. Be the first to add some links to Locket!',
            ]);
        }

        return Response::structured([
            'links' => $trendingLinks,
            'message' => "Today's trending links on Locket.",
        ]);
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()
                ->default(10)
                ->description('Number of trending links to retrieve (default: 10, max: 25)'),
        ];
    }
}
