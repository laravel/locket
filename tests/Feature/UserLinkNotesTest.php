<?php

declare(strict_types=1);

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;

uses()->group('notes');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->link = Link::factory()->create();
    $this->userLink = UserLink::factory()->create([
        'user_id' => $this->user->id,
        'link_id' => $this->link->id,
        'category' => LinkCategory::READ,
        'status' => LinkStatus::UNREAD,
    ]);
});

it('loads notes for a user link', function () {
    // Create some notes for this user and link
    $note1 = LinkNote::factory()->create([
        'user_id' => $this->user->id,
        'link_id' => $this->link->id,
        'note' => 'This is note 1',
    ]);

    $note2 = LinkNote::factory()->create([
        'user_id' => $this->user->id,
        'link_id' => $this->link->id,
        'note' => 'This is note 2',
    ]);

    // Create a note for a different user (should not be included)
    $otherUser = User::factory()->create();
    LinkNote::factory()->create([
        'user_id' => $otherUser->id,
        'link_id' => $this->link->id,
        'note' => 'This is a note from another user',
    ]);

    // Load the UserLink with notes (filtered by user)
    $userLinkWithNotes = UserLink::with(['notes' => function ($query) {
        $query->where('user_id', $this->user->id);
    }])->find($this->userLink->id);

    expect($userLinkWithNotes->notes)->toHaveCount(2);
    expect($userLinkWithNotes->notes->pluck('note'))->toContain('This is note 1');
    expect($userLinkWithNotes->notes->pluck('note'))->toContain('This is note 2');
    expect($userLinkWithNotes->notes->pluck('note'))->not->toContain('This is a note from another user');
});

it('loads notes correctly in dashboard route format', function () {
    // Create a note
    $note = LinkNote::factory()->create([
        'user_id' => $this->user->id,
        'link_id' => $this->link->id,
        'note' => 'Test note content',
    ]);

    // Simulate the dashboard query
    $userLinks = $this->user->userLinks()
        ->with(['link', 'notes'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($userLink) {
            return [
                'id' => $userLink->id,
                'status' => $userLink->status->value,
                'category' => $userLink->category->value,
                'created_at' => $userLink->created_at->toISOString(),
                'link' => [
                    'id' => $userLink->link->id,
                    'url' => $userLink->link->url,
                    'title' => $userLink->link->title,
                    'description' => $userLink->link->description,
                ],
                'notes' => $userLink->notes->map(function ($note) {
                    return [
                        'id' => $note->id,
                        'note' => $note->note,
                        'created_at' => $note->created_at->toISOString(),
                    ];
                })->toArray(),
            ];
        })->toArray();

    expect($userLinks)->toHaveCount(1);
    expect($userLinks[0]['notes'])->toHaveCount(1);
    expect($userLinks[0]['notes'][0]['note'])->toBe('Test note content');
});
