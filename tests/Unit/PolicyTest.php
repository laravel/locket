<?php

declare(strict_types=1);

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;
use App\Models\UserStatus;
use App\Policies\LinkNotePolicy;
use App\Policies\UserLinkPolicy;
use App\Policies\UserStatusPolicy;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('UserLinkPolicy', function () {
    beforeEach(function () {
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->link = Link::factory()->create();
        $this->userLink = UserLink::factory()->create([
            'user_id' => $this->user1->id,
            'link_id' => $this->link->id,
            'category' => LinkCategory::READ,
            'status' => LinkStatus::UNREAD,
        ]);
        $this->policy = new UserLinkPolicy;
    });

    it('allows owner to view their user link', function () {
        expect($this->policy->view($this->user1, $this->userLink))->toBeTrue();
    });

    it('prevents non-owner from viewing user link', function () {
        expect($this->policy->view($this->user2, $this->userLink))->toBeFalse();
    });

    it('allows owner to update their user link', function () {
        expect($this->policy->update($this->user1, $this->userLink))->toBeTrue();
    });

    it('prevents non-owner from updating user link', function () {
        expect($this->policy->update($this->user2, $this->userLink))->toBeFalse();
    });

    it('allows owner to delete their user link', function () {
        expect($this->policy->delete($this->user1, $this->userLink))->toBeTrue();
    });

    it('prevents non-owner from deleting user link', function () {
        expect($this->policy->delete($this->user2, $this->userLink))->toBeFalse();
    });

    it('allows owner to add notes to their user link', function () {
        expect($this->policy->addNote($this->user1, $this->userLink))->toBeTrue();
    });

    it('prevents non-owner from adding notes to user link', function () {
        expect($this->policy->addNote($this->user2, $this->userLink))->toBeFalse();
    });
});

describe('LinkNotePolicy', function () {
    beforeEach(function () {
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->link = Link::factory()->create();
        $this->linkNote = LinkNote::factory()->create([
            'user_id' => $this->user1->id,
            'link_id' => $this->link->id,
            'note' => 'Test note',
        ]);
        $this->policy = new LinkNotePolicy;
    });

    it('allows owner to view their link note', function () {
        expect($this->policy->view($this->user1, $this->linkNote))->toBeTrue();
    });

    it('prevents non-owner from viewing link note', function () {
        expect($this->policy->view($this->user2, $this->linkNote))->toBeFalse();
    });

    it('allows owner to update their link note', function () {
        expect($this->policy->update($this->user1, $this->linkNote))->toBeTrue();
    });

    it('prevents non-owner from updating link note', function () {
        expect($this->policy->update($this->user2, $this->linkNote))->toBeFalse();
    });

    it('allows owner to delete their link note', function () {
        expect($this->policy->delete($this->user1, $this->linkNote))->toBeTrue();
    });

    it('prevents non-owner from deleting link note', function () {
        expect($this->policy->delete($this->user2, $this->linkNote))->toBeFalse();
    });
});

describe('UserStatusPolicy', function () {
    beforeEach(function () {
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->userStatus = UserStatus::factory()->create([
            'user_id' => $this->user1->id,
            'status' => 'Test status',
        ]);
        $this->policy = new UserStatusPolicy;
    });

    it('allows anyone to view user statuses', function () {
        expect($this->policy->view($this->user1, $this->userStatus))->toBeTrue();
        expect($this->policy->view($this->user2, $this->userStatus))->toBeTrue();
    });

    it('allows owner to update their user status', function () {
        expect($this->policy->update($this->user1, $this->userStatus))->toBeTrue();
    });

    it('prevents non-owner from updating user status', function () {
        expect($this->policy->update($this->user2, $this->userStatus))->toBeFalse();
    });

    it('allows owner to delete their user status', function () {
        expect($this->policy->delete($this->user1, $this->userStatus))->toBeTrue();
    });

    it('prevents non-owner from deleting user status', function () {
        expect($this->policy->delete($this->user2, $this->userStatus))->toBeFalse();
    });
});

describe('Gate Integration', function () {
    beforeEach(function () {
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->link = Link::factory()->create();
        $this->userLink = UserLink::factory()->create([
            'user_id' => $this->user1->id,
            'link_id' => $this->link->id,
            'category' => LinkCategory::READ,
            'status' => LinkStatus::UNREAD,
        ]);
    });

    it('integrates UserLink policy with Gate facade', function () {
        expect(\Gate::forUser($this->user1)->allows('update', $this->userLink))->toBeTrue();
        expect(\Gate::forUser($this->user2)->allows('update', $this->userLink))->toBeFalse();
    });

    it('can be used with authorize helper', function () {
        $this->actingAs($this->user1);
        expect(fn () => \Gate::authorize('update', $this->userLink))->not->toThrow(\Exception::class);

        $this->actingAs($this->user2);
        expect(fn () => \Gate::authorize('update', $this->userLink))
            ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
    });
});
