<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Add Link')]
class AddLink extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'A description of what this tool does.';

    /**
     * Handle the tool call.
     */
    public function handle(Request $request): ToolResult
    {
        return ToolResult::text('Tool executed successfully.');
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'example' => $schema->string()
                ->description('An example input description.')
                ->required(),
        ];
    }
}
