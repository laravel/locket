<?php

declare(strict_types=1);

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;
use App\Models\UserStatus;

uses()->group('authorization');

beforeEach(function () {
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();

    // Create some data for user1
    $this->link = Link::factory()->create();
    $this->userLink1 = UserLink::factory()->create([
        'user_id' => $this->user1->id,
        'link_id' => $this->link->id,
        'category' => LinkCategory::READ,
        'status' => LinkStatus::UNREAD,
    ]);

    $this->linkNote1 = LinkNote::factory()->create([
        'user_id' => $this->user1->id,
        'link_id' => $this->link->id,
        'note' => 'User1 note',
    ]);

    $this->userStatus1 = UserStatus::factory()->create([
        'user_id' => $this->user1->id,
        'status' => 'User1 status update',
    ]);
});

describe('UserLink Authorization', function () {
    it('prevents users from updating other users\' links', function () {
        $response = $this->actingAs($this->user2)
            ->patch("/user-links/{$this->userLink1->id}", [
                'status' => LinkStatus::READ->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // Verify the link wasn't updated
        expect($this->userLink1->fresh()->status)->toBe(LinkStatus::UNREAD);
    });

    it('allows users to update their own links', function () {
        $response = $this->actingAs($this->user1)
            ->patch("/user-links/{$this->userLink1->id}", [
                'status' => LinkStatus::READ->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        expect($this->userLink1->fresh()->status)->toBe(LinkStatus::READ);
    });

    it('prevents users from accessing non-existent user links', function () {
        $response = $this->actingAs($this->user2)
            ->patch('/user-links/999999', [
                'status' => LinkStatus::READ->value,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
    });
});

describe('LinkNote Authorization', function () {
    it('prevents users from adding notes to links they haven\'t bookmarked', function () {
        // Create a link that user2 hasn't bookmarked
        $unbookmarkedLink = Link::factory()->create();

        $response = $this->actingAs($this->user2)
            ->post('/links/notes', [
                'link_id' => $unbookmarkedLink->id,
                'note' => 'Trying to add note to unbookmarked link',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['link_id']);

        // Verify no note was created
        expect(LinkNote::where('user_id', $this->user2->id)->count())->toBe(0);
    });

    it('allows users to add notes to their bookmarked links', function () {
        // First, user2 needs to bookmark the link
        UserLink::factory()->create([
            'user_id' => $this->user2->id,
            'link_id' => $this->link->id,
            'category' => LinkCategory::READ,
            'status' => LinkStatus::UNREAD,
        ]);

        $response = $this->actingAs($this->user2)
            ->post('/links/notes', [
                'link_id' => $this->link->id,
                'note' => 'User2 note on bookmarked link',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        expect(LinkNote::where('user_id', $this->user2->id)->count())->toBe(1);
    });
});

describe('Dashboard Data Isolation', function () {
    it('only shows user\'s own links and notes in dashboard', function () {
        // Create some data for user2 as well
        $user2Link = Link::factory()->create();
        UserLink::factory()->create([
            'user_id' => $this->user2->id,
            'link_id' => $user2Link->id,
            'category' => LinkCategory::REFERENCE,
            'status' => LinkStatus::READ,
        ]);

        LinkNote::factory()->create([
            'user_id' => $this->user2->id,
            'link_id' => $user2Link->id,
            'note' => 'User2 note',
        ]);

        // User1 should only see their own data
        $response = $this->actingAs($this->user1)->get('/dashboard');

        $response->assertSuccessful();

        // Get the userLinks prop from the response
        $userLinks = $response->viewData('page')['props']['userLinks'];

        expect($userLinks)->toHaveCount(1);
        expect($userLinks[0]['id'])->toBe($this->userLink1->id);
        expect($userLinks[0]['notes'])->toHaveCount(1);
        expect($userLinks[0]['notes'][0]['note'])->toBe('User1 note');
    });
});

describe('Status Authorization', function () {
    it('only allows authenticated users to create statuses', function () {
        $response = $this->post('/status', [
            'status' => 'Unauthenticated status',
        ]);

        $response->assertRedirect('/login');

        expect(UserStatus::where('status', 'Unauthenticated status')->count())->toBe(0);
    });

    it('allows authenticated users to create their own statuses', function () {
        $response = $this->actingAs($this->user1)
            ->post('/status', [
                'status' => 'My new status',
            ]);

        $response->assertSuccessful();

        $createdStatus = UserStatus::where('user_id', $this->user1->id)
            ->where('status', 'My new status')
            ->first();

        expect($createdStatus)->not->toBeNull();
    });
});

describe('API Endpoint Authorization', function () {
    it('protects bookmark endpoint from unauthorized access', function () {
        $response = $this->post("/links/{$this->link->id}/bookmark");

        $response->assertRedirect('/login');
    });

    it('allows authenticated users to bookmark links', function () {
        $newLink = Link::factory()->create();

        $response = $this->actingAs($this->user2)
            ->post("/links/{$newLink->id}/bookmark");

        $response->assertSuccessful();

        // Verify the user now has this link bookmarked
        expect(UserLink::where('user_id', $this->user2->id)
            ->where('link_id', $newLink->id)
            ->exists())->toBeTrue();
    });
});

describe('Route Model Binding Security', function () {
    it('prevents access to other users\' resources via route parameters', function () {
        // Try to access user1's userLink as user2 using direct ID
        $response = $this->actingAs($this->user2)
            ->patch("/user-links/{$this->userLink1->id}", [
                'status' => LinkStatus::ARCHIVED->value,
            ]);

        // Should fail validation and not update the link
        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // Verify the original status is unchanged
        expect($this->userLink1->fresh()->status)->toBe(LinkStatus::UNREAD);
    });
});
