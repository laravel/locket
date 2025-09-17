<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Models\User;
use App\Models\UserLink;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

final class UpdateUserLink
{
    /**
     * Update status and/or category for user's bookmarked link.
     */
    public function handle(int $userLinkId, User $user, ?string $status = null, ?string $category = null): array
    {
        $data = array_filter([
            'user_link_id' => $userLinkId,
            'status' => $status,
            'category' => $category,
        ], fn ($value) => $value !== null);

        $rules = [
            'user_link_id' => 'required|integer',
        ];

        if ($status !== null) {
            $rules['status'] = [new Enum(LinkStatus::class)];
        }

        if ($category !== null) {
            $rules['category'] = [new Enum(LinkCategory::class)];
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Find the user's link...
        $userLink = UserLink::where('id', $userLinkId)
            ->where('user_id', $user->id)
            ->first();

        if (! $userLink) {
            throw ValidationException::withMessages([
                'user_link_id' => 'Link not found in your bookmarks.',
            ]);
        }

        $changes = [];

        // Update status if provided and valid transition...
        if ($status !== null) {
            $newStatus = LinkStatus::from($status);

            if (! $userLink->canTransitionTo($newStatus)) {
                throw ValidationException::withMessages([
                    'status' => "Cannot transition from {$userLink->status->value} to {$newStatus->value}.",
                ]);
            }

            $userLink->status = $newStatus;

            $changes['status'] = [
                'from' => $userLink->getOriginal('status'),
                'to' => $status,
            ];
        }

        // Update category if provided...
        if ($category !== null) {
            $oldCategory = $userLink->category->value;

            $userLink->category = LinkCategory::from($category);

            $changes['category'] = [
                'from' => $oldCategory,
                'to' => $category,
            ];
        }

        // Save changes...
        $userLink->save();

        return [
            'user_link' => [
                'id' => $userLink->id,
                'status' => $userLink->status->value,
                'category' => $userLink->category->value,
                'updated_at' => $userLink->updated_at->toISOString(),
            ],
            'changes' => $changes,
        ];
    }
}
