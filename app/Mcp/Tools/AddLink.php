<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\CreateStatusWithLink;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class AddLink extends Tool
{
    public function __construct(
        protected CreateStatusWithLink $createStatusWithLink
    ) {}

    protected string $description = 'Add a link to your Locket reading list with optional thoughts and category hint. Creates a status update showing what you\'re reading and saves private notes if thoughts provided.';

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'url' => 'required|url|max:2048',
            'thoughts' => 'nullable|string|max:2000',
            'category_hint' => 'nullable|string|in:read,reference,watch,tools',
        ], [
            'url' => 'A valid URL is required',
            'thoughts' => 'Thoughts must be less than 2000 characters',
            'category_hint' => 'Category must be one of: read, reference, watch, tools',
        ]);

        $user = $request->user();
        if (!$user) {
            return Response::error('Authentication required to add links');
        }

        try {
            $result = $this->createStatusWithLink->handle(
                $validated['url'],
                $validated['thoughts'] ?? null,
                $user,
                $validated['category_hint'] ?? null
            );

            $wasBookmarked = $result['already_bookmarked'] ? 'already bookmarked' : 'added to your reading list';
            $categoryLabel = ucfirst($result['user_link']['category']);
            
            $output = "✅ Link {$wasBookmarked}!\n\n";
            $output .= "**{$result['link']['title']}**\n";
            $output .= "URL: {$result['link']['url']}\n";
            $output .= "Category: {$categoryLabel}\n";
            
            if (isset($result['note'])) {
                $output .= "Note: {$result['note']['note']}\n";
            }
            
            $output .= "\nStatus update created: {$result['status']['status']}";

            return Response::text($output);

        } catch (\Exception $e) {
            return Response::error("Failed to add link: {$e->getMessage()}");
        }
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'url' => $schema->string()
                ->description('The URL to add to your reading list')
                ->required(),
            'thoughts' => $schema->string()
                ->description('Optional thoughts or notes about this link (will be saved as a private note)')
                ->required(false),
            'category_hint' => $schema->string()
                ->enum(['read', 'reference', 'watch', 'tools'])
                ->description('Optional category hint: read (articles/blogs), reference (docs/specs), watch (videos), tools (libraries/services)')
                ->required(false),
        ];
    }
}
