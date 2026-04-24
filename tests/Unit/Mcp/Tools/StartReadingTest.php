<?php

declare(strict_types=1);

use App\Enums\LinkStatus;
use App\Mcp\Servers\Locket;
use App\Mcp\Tools\StartReading;
use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('authentication', function () {
    test('returns an error when unauthenticated', function () {
        Locket::tool(StartReading::class, ['user_link_id' => 1])
            ->assertHasErrors();
    });
});

describe('basic functionality', function () {
    test('transitions an unread link to reading', function () {
        $user = User::factory()->create();
        $link = Link::factory()->create();
        $userLink = UserLink::factory()->unread()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        Locket::actingAs($user)->tool(StartReading::class, [
            'user_link_id' => $userLink->id,
        ])->assertOk();

        expect($userLink->fresh()->status)->toBe(LinkStatus::READING);
    });

    test('returns the refreshed queue after transition', function () {
        $user = User::factory()->create();
        $linkA = Link::factory()->create(['title' => 'Starting Now']);
        $linkB = Link::factory()->create(['title' => 'Still Queued']);

        $starting = UserLink::factory()->unread()->create(['user_id' => $user->id, 'link_id' => $linkA->id]);
        UserLink::factory()->unread()->create(['user_id' => $user->id, 'link_id' => $linkB->id]);

        Locket::actingAs($user)->tool(StartReading::class, [
            'user_link_id' => $starting->id,
        ])
            ->assertOk()
            ->assertSee('Reading started.')
            ->assertSee('Still Queued')
            ->assertDontSee('Starting Now');
    });

    test('returns the clear-queue message when nothing unread remains', function () {
        $user = User::factory()->create();
        $link = Link::factory()->create();
        $userLink = UserLink::factory()->unread()->create(['user_id' => $user->id, 'link_id' => $link->id]);

        Locket::actingAs($user)->tool(StartReading::class, [
            'user_link_id' => $userLink->id,
        ])
            ->assertOk()
            ->assertSee('your queue is clear');
    });
});

describe('authorization', function () {
    test('rejects a user_link_id that belongs to another user', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $link = Link::factory()->create();
        $userLink = UserLink::factory()->unread()->create(['user_id' => $owner->id, 'link_id' => $link->id]);

        Locket::actingAs($attacker)->tool(StartReading::class, [
            'user_link_id' => $userLink->id,
        ])->assertHasErrors();

        expect($userLink->fresh()->status)->toBe(LinkStatus::UNREAD);
    });

    test('rejects a transition from a status that cannot reach Reading', function () {
        $user = User::factory()->create();
        $link = Link::factory()->create();
        $userLink = UserLink::factory()->reference()->create(['user_id' => $user->id, 'link_id' => $link->id]);

        Locket::actingAs($user)->tool(StartReading::class, [
            'user_link_id' => $userLink->id,
        ])->assertHasErrors();

        expect($userLink->fresh()->status)->toBe(LinkStatus::REFERENCE);
    });
});

