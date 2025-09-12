<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class CreateStatusWithLink
{
    public function __construct(
        private AddLink $addLink,
        private AddLinkNote $addLinkNote
    ) {}

    /**
     * Add a link and create a status update mentioning it.
     */
    public function handle(string $url, ?string $thoughts, User $user, ?string $categoryHint = null): array
    {
        // Validate input
        $validator = Validator::make([
            'url' => $url,
            'thoughts' => $thoughts,
            'category' => $categoryHint,
        ], [
            'url' => 'required|url|max:2048',
            'thoughts' => 'nullable|string|max:2000',
            'category' => 'nullable|string|in:read,reference,watch,tools',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Add the link to user's collection
        $linkResult = $this->addLink->handle($url, $user, $categoryHint ?? 'read');

        // Create formatted status message
        $statusText = $thoughts ? trim($thoughts) : '';

        if ($statusText) {
            $statusText .= "\n\n";
        }

        $action = $linkResult['already_bookmarked'] ? 'Bookmarked' : 'Saved';
        $statusText .= "{$action} link: {$url}";

        // Create the status update
        $status = UserStatus::create([
            'user_id' => $user->id,
            'status' => $statusText,
            'link_id' => $linkResult['link']['id'],
        ]);

        $result = [
            'link' => $linkResult['link'],
            'user_link' => $linkResult['user_link'],
            'status' => [
                'id' => $status->id,
                'status' => $status->status,
                'created_at' => $status->created_at->toISOString(),
            ],
            'already_bookmarked' => $linkResult['already_bookmarked'],
        ];

        // Also save thoughts as private notes if provided
        if (!empty($thoughts)) {
            $noteResult = $this->addLinkNote->handle(
                $linkResult['link']['id'],
                $thoughts,
                $user
            );
            $result['note'] = $noteResult['note'];
        }

        return $result;
    }
}
