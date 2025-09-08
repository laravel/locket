<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\CreateUserStatus;
use App\Models\User;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\ToolResult;

class UpdateStatus extends Tool
{
    public function __construct(
        protected CreateUserStatus $createUserStatus
    ) {}

    /**
     * The tool's description.
     */
    protected string $description = 'Update your current status message.';

    /**
     * Handle the tool call.
     */
    public function handle(Request $request, User $user): ToolResult
    {
        $request->validate(['status' => 'string|required|min:3|max:280']);
        $status = $request->get('status');

        $userStatus = $this->createUserStatus->handle($user, $status);

        return ToolResult::text("Status updated successfully: \"{$userStatus->status}\"");
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()
                ->description('Your new status message')
                ->required(),
        ];
    }
}
