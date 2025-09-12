<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AddLinkNote
{
    /**
     * Add a personal note to a user's bookmarked link.
     */
    public function handle(int $linkId, string $note, User $user): array
    {
        // Validate input
        $validator = Validator::make([
            'link_id' => $linkId,
            'note' => $note,
        ], [
            'link_id' => 'required|integer|exists:links,id',
            'note' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Ensure user has this link bookmarked
        $userLink = UserLink::where('user_id', $user->id)
            ->where('link_id', $linkId)
            ->first();

        if (! $userLink) {
            throw ValidationException::withMessages([
                'link_id' => 'You must bookmark this link before adding notes.',
            ]);
        }

        // Create the note
        $linkNote = LinkNote::create([
            'user_id' => $user->id,
            'link_id' => $linkId,
            'note' => trim($note),
        ]);

        return [
            'note' => [
                'id' => $linkNote->id,
                'note' => $linkNote->note,
                'created_at' => $linkNote->created_at->toISOString(),
            ],
            'link_id' => $linkId,
        ];
    }
}
