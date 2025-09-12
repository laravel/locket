<?php

declare(strict_types=1);

use App\Enums\LinkCategory;
use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it has fillable attributes', function () {
    $link = new Link;

    expect($link->getFillable())->toContain('url');
    expect($link->getFillable())->toContain('title');
    expect($link->getFillable())->toContain('description');
    expect($link->getFillable())->toContain('category');
    expect($link->getFillable())->toContain('submitted_by_user_id');
    expect($link->getFillable())->toContain('metadata');
});

test('it casts category to LinkCategory enum', function () {
    $link = Link::factory()->create(['category' => LinkCategory::READ]);

    expect($link->category)->toBeInstanceOf(LinkCategory::class);
    expect($link->category)->toBe(LinkCategory::READ);
});

test('it casts metadata to array', function () {
    $metadata = ['title' => 'Test', 'description' => 'Test description'];
    $link = Link::factory()->create(['metadata' => $metadata]);

    expect($link->metadata)->toBeArray();
    expect($link->metadata['title'])->toBe('Test');
});

test('it uses soft deletes', function () {
    $link = Link::factory()->create();

    $link->delete();

    expect($link->deleted_at)->not->toBeNull();
    expect(Link::count())->toBe(0);
    expect(Link::withTrashed()->count())->toBe(1);
});

test('it belongs to submitted by user', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create(['submitted_by_user_id' => $user->id]);

    expect($link->submittedBy)->toBeInstanceOf(User::class);
    expect($link->submittedBy->id)->toBe($user->id);
});

test('it has many user links', function () {
    $link = Link::factory()->create();
    UserLink::factory()->count(3)->create(['link_id' => $link->id]);

    expect($link->userLinks)->toHaveCount(3);
    expect($link->userLinks->first())->toBeInstanceOf(UserLink::class);
});

test('it has many notes', function () {
    $link = Link::factory()->create();
    LinkNote::factory()->count(2)->create(['link_id' => $link->id]);

    expect($link->notes)->toHaveCount(2);
    expect($link->notes->first())->toBeInstanceOf(LinkNote::class);
});

test('it has many to many relationship with users', function () {
    $link = Link::factory()->create();
    $users = User::factory()->count(2)->create();

    foreach ($users as $user) {
        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);
    }

    expect($link->users)->toHaveCount(2);
    expect($link->users->first())->toBeInstanceOf(User::class);
});

test('category scope filters by category', function () {
    Link::factory()->create(['category' => LinkCategory::READ]);
    Link::factory()->create(['category' => LinkCategory::WATCH]);
    Link::factory()->create(['category' => LinkCategory::READ]);

    $readLinks = Link::category(LinkCategory::READ)->get();

    expect($readLinks)->toHaveCount(2);
});

test('recent scope orders by created_at desc', function () {
    $oldLink = Link::factory()->create(['created_at' => now()->subDays(2)]);
    $newLink = Link::factory()->create(['created_at' => now()]);

    $recentLinks = Link::recent()->get();

    expect($recentLinks->first()->id)->toBe($newLink->id);
    expect($recentLinks->last()->id)->toBe($oldLink->id);
});

test('popular scope orders by user links count', function () {
    $popularLink = Link::factory()->create();
    $unpopularLink = Link::factory()->create();

    // Create more user links for popular link
    UserLink::factory()->count(3)->create(['link_id' => $popularLink->id]);
    UserLink::factory()->count(1)->create(['link_id' => $unpopularLink->id]);

    $popularLinks = Link::popular()->get();

    expect($popularLinks->first()->id)->toBe($popularLink->id);
});
