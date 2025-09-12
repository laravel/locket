<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it has fillable attributes', function () {
    $linkNote = new LinkNote;

    expect($linkNote->getFillable())->toContain('user_id');
    expect($linkNote->getFillable())->toContain('link_id');
    expect($linkNote->getFillable())->toContain('note');
});

test('it uses soft deletes', function () {
    $linkNote = LinkNote::factory()->create();

    $linkNote->delete();

    expect($linkNote->deleted_at)->not->toBeNull();
    expect(LinkNote::count())->toBe(0);
    expect(LinkNote::withTrashed()->count())->toBe(1);
});

test('it belongs to user', function () {
    $user = User::factory()->create();
    $linkNote = LinkNote::factory()->create(['user_id' => $user->id]);

    expect($linkNote->user)->toBeInstanceOf(User::class);
    expect($linkNote->user->id)->toBe($user->id);
});

test('it belongs to link', function () {
    $link = Link::factory()->create();
    $linkNote = LinkNote::factory()->create(['link_id' => $link->id]);

    expect($linkNote->link)->toBeInstanceOf(Link::class);
    expect($linkNote->link->id)->toBe($link->id);
});

test('forUser scope filters notes for specific user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    LinkNote::factory()->create(['user_id' => $user1->id]);
    LinkNote::factory()->create(['user_id' => $user2->id]);
    LinkNote::factory()->create(['user_id' => $user1->id]);

    $user1Notes = LinkNote::forUser($user1->id)->get();

    expect($user1Notes)->toHaveCount(2);
});

test('forLink scope filters notes for specific link', function () {
    $link1 = Link::factory()->create();
    $link2 = Link::factory()->create();

    LinkNote::factory()->create(['link_id' => $link1->id]);
    LinkNote::factory()->create(['link_id' => $link2->id]);
    LinkNote::factory()->create(['link_id' => $link1->id]);

    $link1Notes = LinkNote::forLink($link1->id)->get();

    expect($link1Notes)->toHaveCount(2);
});

test('search scope filters notes by content', function () {
    LinkNote::factory()->create(['note' => 'This is about Laravel framework']);
    LinkNote::factory()->create(['note' => 'This is about React components']);
    LinkNote::factory()->create(['note' => 'Laravel is awesome']);

    $laravelNotes = LinkNote::search('Laravel')->get();

    expect($laravelNotes)->toHaveCount(2);
});

test('recent scope orders by created_at desc', function () {
    $oldNote = LinkNote::factory()->create(['created_at' => now()->subDays(2)]);
    $newNote = LinkNote::factory()->create(['created_at' => now()]);

    $recentNotes = LinkNote::recent()->get();

    expect($recentNotes->first()->id)->toBe($newNote->id);
    expect($recentNotes->last()->id)->toBe($oldNote->id);
});
