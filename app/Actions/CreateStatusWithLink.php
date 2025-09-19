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
     *
     * @return array{link: array{id: int, url: string, title: string, description: string, category: string}, user_link: array{id: int, category: string, status: string, created_at: string}, status: array{id: int, status: string, created_at: string}, already_bookmarked: bool, note?: array{id: int, note: string, created_at: string}}
     */
    public function handle(string $url, ?string $thoughts, User $user, ?string $categoryHint = null): array
    {
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

        $linkResult = $this->addLink->handle($url, $user, $categoryHint ?? 'read');

        $statusText = $thoughts ? trim($thoughts) : '';

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

        // Also save thoughts as private notes if provided...
        if (! empty($thoughts)) {
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
