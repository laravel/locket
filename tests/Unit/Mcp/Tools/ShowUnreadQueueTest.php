<?php

declare(strict_types=1);

use App\Enums\LinkStatus;
use App\Mcp\Servers\Locket;
use App\Mcp\Tools\ShowUnreadQueue;
use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('authentication', function () {
    test('returns an error when unauthenticated', function () {
        Locket::tool(ShowUnreadQueue::class)
            ->assertHasErrors();
    });
});

describe('basic functionality', function () {
    test('returns only the signed-in user unread items', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $mine = Link::factory()->create(['title' => 'Mine Unread', 'url' => 'https://me.test']);
        $theirs = Link::factory()->create(['title' => 'Theirs Unread', 'url' => 'https://other.test']);

        UserLink::factory()->unread()->create(['user_id' => $user->id, 'link_id' => $mine->id]);
        UserLink::factory()->unread()->create(['user_id' => $other->id, 'link_id' => $theirs->id]);

        Locket::actingAs($user)->tool(ShowUnreadQueue::class)
            ->assertOk()
            ->assertSee('Mine Unread')
            ->assertDontSee('Theirs Unread');
    });

    test('excludes non-unread statuses', function () {
        $user = User::factory()->create();

        $unread = Link::factory()->create(['title' => 'Still Unread']);
        $reading = Link::factory()->create(['title' => 'In Progress']);
        $read = Link::factory()->create(['title' => 'Already Read']);
        $archived = Link::factory()->create(['title' => 'Old One']);

        UserLink::factory()->unread()->create(['user_id' => $user->id, 'link_id' => $unread->id]);
        UserLink::factory()->reading()->create(['user_id' => $user->id, 'link_id' => $reading->id]);
        UserLink::factory()->read()->create(['user_id' => $user->id, 'link_id' => $read->id]);
        UserLink::factory()->archived()->create(['user_id' => $user->id, 'link_id' => $archived->id]);

        Locket::actingAs($user)->tool(ShowUnreadQueue::class)
            ->assertOk()
            ->assertSee('Still Unread')
            ->assertDontSee('In Progress')
            ->assertDontSee('Already Read')
            ->assertDontSee('Old One');
    });

    test('returns an empty-state message when the queue is empty', function () {
        $user = User::factory()->create();

        Locket::actingAs($user)->tool(ShowUnreadQueue::class)
            ->assertOk()
            ->assertSee('Your reading queue is empty');
    });

    test('returns a message and populated items when unread links exist', function () {
        $user = User::factory()->create();
        $link = Link::factory()->create(['title' => 'Queued']);
        UserLink::factory()->unread()->create(['user_id' => $user->id, 'link_id' => $link->id]);

        Locket::actingAs($user)->tool(ShowUnreadQueue::class)
            ->assertOk()
            ->assertSee('Your unread reading queue.')
            ->assertSee('Queued');
    });
});
