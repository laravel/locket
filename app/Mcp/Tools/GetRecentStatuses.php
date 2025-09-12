<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\GetAllRecentStatuses;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tool;

class GetRecentStatuses extends Tool
{
    public function __construct(
        protected GetAllRecentStatuses $getAllRecentStatuses
    ) {}

    protected string $description = 'Get recent status messages from all users.';

    public function handle(Request $request): string
    {
        $validated = $request->validate([
            'limit' => 'numeric|min:1|max:50',
        ], [
            'limit' => 'Invalid limit, must be numeric, minimum of 1, and maximum of 50',
        ]);

        $limit = (int) $validated['limit'];

        $statuses = $this->getAllRecentStatuses->handle($limit);

        if ($statuses->isEmpty()) {
            return 'No status messages found.';
        }

        $output = "Recent status messages from all users:\n\n";

        foreach ($statuses as $status) {
            $output .= "• {$status->user->name}: {$status->status} ({$status->created_at->diffForHumans()})\n";
        }

        return $output;
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()
                ->default(10)
                ->description('Number of recent statuses to retrieve (default: 10, max: 50)'),
        ];
    }
}
