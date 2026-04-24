<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\GetUserUnreadLinks;
use App\Actions\UpdateUserLink;
use App\Enums\LinkStatus;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;

class StartReading extends Tool
{
    public function __construct(
        protected UpdateUserLink $updateUserLink,
        protected GetUserUnreadLinks $getUserUnreadLinks,
    ) {}

    protected string $description = 'Transition one of your bookmarked links from Unread to Reading.';

    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Authentication required.');
        }

        $validated = $request->validate([
            'user_link_id' => 'required|integer',
        ]);

        try {
            $this->updateUserLink->handle(
                $validated['user_link_id'],
                $user,
                status: LinkStatus::READING->value,
            );
        } catch (ValidationException $e) {
            return Response::error(collect($e->errors())->flatten()->first() ?? 'Unable to start reading.');
        }

        $items = $this->getUserUnreadLinks->handle($user);

        return Response::structured([
            'items' => $items,
            'message' => $items === []
                ? 'Nice — your queue is clear.'
                : 'Reading started.',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'user_link_id' => $schema->integer()
                ->description('The UserLink id of the bookmark to start reading.')
                ->required(),
        ];
    }
}
