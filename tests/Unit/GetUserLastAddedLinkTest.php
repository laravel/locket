<?php

declare(strict_types=1);

use App\Actions\GetUserLastAddedLink;
use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it returns null when user has no links', function () {
    $user = User::factory()->create();
    $action = new GetUserLastAddedLink;

    $result = $action->handle($user);

    expect($result)->toBeNull();
});

test('it returns the most recently added link', function () {
    $user = User::factory()->create();
    $link1 = Link::factory()->create(['title' => 'First Link']);
    $link2 = Link::factory()->create(['title' => 'Second Link']);

    // Create older link first
    UserLink::factory()->create([
        'user_id' => $user->id,
        'link_id' => $link1->id,
        'created_at' => now()->subHour(),
    ]);

    // Create newer link
    UserLink::factory()->create([
        'user_id' => $user->id,
        'link_id' => $link2->id,
        'created_at' => now(),
    ]);

    $action = new GetUserLastAddedLink;
    $result = $action->handle($user);

    expect($result)->not()->toBeNull();
    expect($result['link']['title'])->toBe('Second Link');
    expect($result['link']['id'])->toBe($link2->id);
});

test('it includes user link details', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create();
    $userLink = UserLink::factory()->create([
        'user_id' => $user->id,
        'link_id' => $link->id,
        'category' => LinkCategory::REFERENCE,
        'status' => LinkStatus::READING,
    ]);

    $action = new GetUserLastAddedLink;
    $result = $action->handle($user);

    expect($result['user_link'])->toHaveKeys(['id', 'category', 'status', 'created_at']);
    expect($result['user_link']['category'])->toBe('reference');
    expect($result['user_link']['status'])->toBe('reading');
    expect($result['user_link']['id'])->toBe($userLink->id);
});

test('it includes link details', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create([
        'title' => 'Test Article',
        'url' => 'https://example.com/test',
        'description' => 'A test article',
        'category' => LinkCategory::READ,
    ]);
    UserLink::factory()->create(['user_id' => $user->id, 'link_id' => $link->id]);

    $action = new GetUserLastAddedLink;
    $result = $action->handle($user);

    expect($result['link'])->toHaveKeys(['id', 'url', 'title', 'description', 'category']);
    expect($result['link']['title'])->toBe('Test Article');
    expect($result['link']['url'])->toBe('https://example.com/test');
    expect($result['link']['description'])->toBe('A test article');
    expect($result['link']['category'])->toBe('read');
});

test('it includes notes for the link', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create();
    $userLink = UserLink::factory()->create(['user_id' => $user->id, 'link_id' => $link->id]);

    $note1 = LinkNote::factory()->create([
        'user_id' => $user->id,
        'link_id' => $link->id,
        'note' => 'First note',
        'created_at' => now()->subHour(),
    ]);

    $note2 = LinkNote::factory()->create([
        'user_id' => $user->id,
        'link_id' => $link->id,
        'note' => 'Second note',
        'created_at' => now(),
    ]);

    $action = new GetUserLastAddedLink;
    $result = $action->handle($user);

    expect($result['notes'])->toHaveCount(2);
    expect($result['notes'][0]['note'])->toBe('Second note'); // Most recent first
    expect($result['notes'][1]['note'])->toBe('First note');
});

test('it only includes notes from the current user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $link = Link::factory()->create();

    UserLink::factory()->create(['user_id' => $user1->id, 'link_id' => $link->id]);

    LinkNote::factory()->create([
        'user_id' => $user1->id,
        'link_id' => $link->id,
        'note' => 'User 1 note',
    ]);

    LinkNote::factory()->create([
        'user_id' => $user2->id,
        'link_id' => $link->id,
        'note' => 'User 2 note',
    ]);

    $action = new GetUserLastAddedLink;
    $result = $action->handle($user1);

    expect($result['notes'])->toHaveCount(1);
    expect($result['notes'][0]['note'])->toBe('User 1 note');
});

test('it returns empty notes array when no notes exist', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create();
    UserLink::factory()->create(['user_id' => $user->id, 'link_id' => $link->id]);

    $action = new GetUserLastAddedLink;
    $result = $action->handle($user);

    expect($result['notes'])->toBeArray();
    expect($result['notes'])->toBeEmpty();
});

test('it only returns links for the specified user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $link1 = Link::factory()->create(['title' => 'User 1 Link']);
    $link2 = Link::factory()->create(['title' => 'User 2 Link']);

    UserLink::factory()->create([
        'user_id' => $user1->id,
        'link_id' => $link1->id,
        'created_at' => now()->subHour(),
    ]);

    UserLink::factory()->create([
        'user_id' => $user2->id,
        'link_id' => $link2->id,
        'created_at' => now(),
    ]);

    $action = new GetUserLastAddedLink;
    $result = $action->handle($user1);

    expect($result['link']['title'])->toBe('User 1 Link');
});
