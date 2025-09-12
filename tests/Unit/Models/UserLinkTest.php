<?php

declare(strict_types=1);

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it has fillable attributes', function () {
    $userLink = new UserLink;

    expect($userLink->getFillable())->toContain('user_id');
    expect($userLink->getFillable())->toContain('link_id');
    expect($userLink->getFillable())->toContain('category');
    expect($userLink->getFillable())->toContain('status');
});

test('it casts category to LinkCategory enum', function () {
    $userLink = UserLink::factory()->create(['category' => LinkCategory::READ]);

    expect($userLink->category)->toBeInstanceOf(LinkCategory::class);
    expect($userLink->category)->toBe(LinkCategory::READ);
});

test('it casts status to LinkStatus enum', function () {
    $userLink = UserLink::factory()->create(['status' => LinkStatus::UNREAD]);

    expect($userLink->status)->toBeInstanceOf(LinkStatus::class);
    expect($userLink->status)->toBe(LinkStatus::UNREAD);
});

test('it uses soft deletes', function () {
    $userLink = UserLink::factory()->create();

    $userLink->delete();

    expect($userLink->deleted_at)->not->toBeNull();
    expect(UserLink::count())->toBe(0);
    expect(UserLink::withTrashed()->count())->toBe(1);
});

test('it belongs to user', function () {
    $user = User::factory()->create();
    $userLink = UserLink::factory()->create(['user_id' => $user->id]);

    expect($userLink->user)->toBeInstanceOf(User::class);
    expect($userLink->user->id)->toBe($user->id);
});

test('it belongs to link', function () {
    $link = Link::factory()->create();
    $userLink = UserLink::factory()->create(['link_id' => $link->id]);

    expect($userLink->link)->toBeInstanceOf(Link::class);
    expect($userLink->link->id)->toBe($link->id);
});

test('category scope filters by category', function () {
    UserLink::factory()->create(['category' => LinkCategory::READ]);
    UserLink::factory()->create(['category' => LinkCategory::WATCH]);
    UserLink::factory()->create(['category' => LinkCategory::READ]);

    $readUserLinks = UserLink::category(LinkCategory::READ)->get();

    expect($readUserLinks)->toHaveCount(2);
});

test('status scope filters by status', function () {
    UserLink::factory()->create(['status' => LinkStatus::UNREAD]);
    UserLink::factory()->create(['status' => LinkStatus::READ]);
    UserLink::factory()->create(['status' => LinkStatus::UNREAD]);

    $unreadUserLinks = UserLink::status(LinkStatus::UNREAD)->get();

    expect($unreadUserLinks)->toHaveCount(2);
});

test('unread scope filters unread items', function () {
    UserLink::factory()->create(['status' => LinkStatus::UNREAD]);
    UserLink::factory()->create(['status' => LinkStatus::READ]);
    UserLink::factory()->create(['status' => LinkStatus::UNREAD]);

    $unreadUserLinks = UserLink::unread()->get();

    expect($unreadUserLinks)->toHaveCount(2);
});

test('reading scope filters reading items', function () {
    UserLink::factory()->create(['status' => LinkStatus::READING]);
    UserLink::factory()->create(['status' => LinkStatus::READ]);
    UserLink::factory()->create(['status' => LinkStatus::READING]);

    $readingUserLinks = UserLink::reading()->get();

    expect($readingUserLinks)->toHaveCount(2);
});

test('read scope filters read items', function () {
    UserLink::factory()->create(['status' => LinkStatus::UNREAD]);
    UserLink::factory()->create(['status' => LinkStatus::READ]);
    UserLink::factory()->create(['status' => LinkStatus::READ]);

    $readUserLinks = UserLink::read()->get();

    expect($readUserLinks)->toHaveCount(2);
});

test('active scope excludes archived items', function () {
    UserLink::factory()->create(['status' => LinkStatus::UNREAD]);
    UserLink::factory()->create(['status' => LinkStatus::ARCHIVED]);
    UserLink::factory()->create(['status' => LinkStatus::READ]);

    $activeUserLinks = UserLink::active()->get();

    expect($activeUserLinks)->toHaveCount(2);
});

test('canTransitionTo checks valid status transitions', function () {
    $userLink = UserLink::factory()->create(['status' => LinkStatus::UNREAD]);

    expect($userLink->canTransitionTo(LinkStatus::READING))->toBeTrue();
    expect($userLink->canTransitionTo(LinkStatus::READ))->toBeTrue();
    expect($userLink->canTransitionTo(LinkStatus::ARCHIVED))->toBeTrue();
});

test('transitionTo changes status when allowed', function () {
    $userLink = UserLink::factory()->create(['status' => LinkStatus::UNREAD]);

    $result = $userLink->transitionTo(LinkStatus::READING);

    expect($result)->toBeTrue();
    expect($userLink->fresh()->status)->toBe(LinkStatus::READING);
});

test('transitionTo fails when transition not allowed', function () {
    $userLink = UserLink::factory()->create(['status' => LinkStatus::READ]);

    $result = $userLink->transitionTo(LinkStatus::UNREAD);

    expect($result)->toBeFalse();
    expect($userLink->fresh()->status)->toBe(LinkStatus::READ);
});
