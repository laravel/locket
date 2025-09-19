<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;
use App\Models\UserStatus;

it('deletes user account and cascades all related data', function () {
    // Arrange
    $userToDelete = User::factory()->create();
    $otherUser = User::factory()->create();

    $userLinks = Link::factory()->count(3)->create([
        'submitted_by_user_id' => $userToDelete->id,
    ]);

    $otherUserLinks = Link::factory()->count(2)->create([
        'submitted_by_user_id' => $otherUser->id,
    ]);

    $savedLinks = collect();
    foreach ($userLinks->take(3) as $link) {
        $savedLinks->push(UserLink::factory()->create([
            'user_id' => $userToDelete->id,
            'link_id' => $link->id,
        ]));
    }

    // Create user_links for other user (should not be deleted)
    $otherSavedLinks = collect();
    foreach ($otherUserLinks->take(2) as $link) {
        $otherSavedLinks->push(UserLink::factory()->create([
            'user_id' => $otherUser->id,
            'link_id' => $link->id,
        ]));
    }

    $userNotes = LinkNote::factory()->count(5)->create([
        'user_id' => $userToDelete->id,
        'link_id' => $userLinks->random()->id,
    ]);

    $otherUserNotes = LinkNote::factory()->count(2)->create([
        'user_id' => $otherUser->id,
        'link_id' => $otherUserLinks->first()->id,
    ]);

    $userStatus = UserStatus::factory()->create([
        'user_id' => $userToDelete->id,
        'link_id' => $userLinks->first()->id,
    ]);

    // Create user status for other user (should not be deleted)
    $otherUserStatus = UserStatus::factory()->create([
        'user_id' => $otherUser->id,
        'link_id' => $otherUserLinks->first()->id,
    ]);

    // Record the IDs for later assertions
    $userToDeleteId = $userToDelete->id;
    $userLinkIds = $userLinks->pluck('id')->toArray();
    $savedLinkIds = $savedLinks->pluck('id')->toArray();
    $userNoteIds = $userNotes->pluck('id')->toArray();
    $userStatusId = $userStatus->id;

    // Act
    $response = $this->actingAs($userToDelete)->delete(route('profile.destroy'));

    // Assert
    $response->assertRedirect('/');
    $this->assertDatabaseMissing('users', ['id' => $userToDeleteId]);

    foreach ($userLinkIds as $linkId) {
        $this->assertDatabaseHas('links', ['id' => $linkId, 'submitted_by_user_id' => null]);
    }

    foreach ($savedLinkIds as $savedLinkId) {
        $this->assertDatabaseMissing('user_links', ['id' => $savedLinkId]);
    }

    foreach ($userNoteIds as $noteId) {
        $this->assertDatabaseMissing('link_notes', ['id' => $noteId]);
    }

    $this->assertDatabaseMissing('user_statuses', ['id' => $userStatusId]);
    $this->assertDatabaseHas('users', ['id' => $otherUser->id]);

    foreach ($otherUserLinks as $link) {
        $this->assertDatabaseHas('links', ['id' => $link->id]);
    }

    foreach ($otherSavedLinks as $savedLink) {
        $this->assertDatabaseHas('user_links', ['id' => $savedLink->id]);
    }

    foreach ($otherUserNotes as $note) {
        $this->assertDatabaseHas('link_notes', ['id' => $note->id]);
    }

    $this->assertDatabaseHas('user_statuses', ['id' => $otherUserStatus->id]);

    $this->assertGuest();
});

it('handles deletion when user has no related data', function () {
    $user = User::factory()->create();
    $userId = $user->id;

    $response = $this->actingAs($user)->delete(route('profile.destroy'));

    $response->assertRedirect('/');
    $this->assertDatabaseMissing('users', ['id' => $userId]);
    $this->assertGuest();
});

it('anonymizes links when user deletes account, preserving other users bookmarks', function () {
    // This tests a complex scenario where:
    // User A creates a link
    // User B saves that link (creates user_link)
    // User A deletes their account
    // The link should be anonymized (submitted_by_user_id set to null), and User B keeps their bookmark

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // User A creates a link
    $link = Link::factory()->create([
        'submitted_by_user_id' => $userA->id,
    ]);

    // User B saves User A's link
    $userBSavedLink = UserLink::factory()->create([
        'user_id' => $userB->id,
        'link_id' => $link->id,
    ]);

    // User B adds a note to User A's link
    $userBNote = LinkNote::factory()->create([
        'user_id' => $userB->id,
        'link_id' => $link->id,
    ]);

    // User B sets their status with User A's link
    $userBStatus = UserStatus::factory()->create([
        'user_id' => $userB->id,
        'link_id' => $link->id,
    ]);

    $linkId = $link->id;
    $userBSavedLinkId = $userBSavedLink->id;
    $userBNoteId = $userBNote->id;
    $userBStatusId = $userBStatus->id;

    // User A deletes their account
    $this->actingAs($userA)->delete(route('profile.destroy'));

    // Assert User A is deleted
    $this->assertDatabaseMissing('users', ['id' => $userA->id]);

    // Assert the link is anonymized (submitted_by_user_id set to null)
    $this->assertDatabaseHas('links', ['id' => $linkId, 'submitted_by_user_id' => null]);

    // Assert User B's saved link is preserved
    $this->assertDatabaseHas('user_links', ['id' => $userBSavedLinkId]);

    // Assert User B's note on that link is preserved
    $this->assertDatabaseHas('link_notes', ['id' => $userBNoteId]);

    // Assert User B's status referencing that link is preserved
    $this->assertDatabaseHas('user_statuses', ['id' => $userBStatusId]);

    // Assert User B still exists
    $this->assertDatabaseHas('users', ['id' => $userB->id]);
});
