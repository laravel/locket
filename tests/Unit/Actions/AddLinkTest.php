<?php

declare(strict_types=1);

use App\Actions\AddLink;
use App\Enums\LinkCategory;
use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;
use Illuminate\Validation\ValidationException;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it creates new link and user bookmark', function () {
    $user = User::factory()->create();
    $action = new AddLink;

    $result = $action->handle('https://example.com/article', $user);

    expect($result['link']['url'])->toBe('https://example.com/article');
    expect($result['user_link']['status'])->toBe('unread');
    expect($result['already_bookmarked'])->toBeFalse();

    expect(Link::count())->toBe(1);
    expect(UserLink::count())->toBe(1);
});

test('it finds existing link and creates user bookmark', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $link = Link::factory()->create(['url' => 'https://example.com/article']);

    $action = new AddLink;
    $result = $action->handle('https://example.com/article', $user2);

    expect($result['link']['id'])->toBe($link->id);
    expect($result['already_bookmarked'])->toBeFalse();

    expect(Link::count())->toBe(1); // No new link created
    expect(UserLink::count())->toBe(1); // New user bookmark
});

test('it returns existing bookmark if user already bookmarked', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create(['url' => 'https://example.com/article']);
    UserLink::factory()->create(['user_id' => $user->id, 'link_id' => $link->id]);

    $action = new AddLink;
    $result = $action->handle('https://example.com/article', $user);

    expect($result['already_bookmarked'])->toBeTrue();
    expect(UserLink::count())->toBe(1); // No new bookmark
});

test('it validates URL format', function () {
    $user = User::factory()->create();
    $action = new AddLink;

    expect(fn () => $action->handle('invalid-url', $user))
        ->toThrow(ValidationException::class);
});

test('it suggests category from URL patterns', function () {
    $user = User::factory()->create();
    $action = new AddLink;

    // YouTube should be categorized as WATCH
    $result = $action->handle('https://www.youtube.com/watch?v=123', $user);
    $link = Link::find($result['link']['id']);
    expect($link->category)->toBe(LinkCategory::WATCH);

    // GitHub should be categorized as TOOLS
    $result2 = $action->handle('https://github.com/user/repo', $user);
    $link2 = Link::find($result2['link']['id']);
    expect($link2->category)->toBe(LinkCategory::TOOLS);
});

test('it uses category hint when provided', function () {
    $user = User::factory()->create();
    $action = new AddLink;

    $result = $action->handle(
        'https://example.com/article',
        $user,
        'reference'
    );

    $link = Link::find($result['link']['id']);
    expect($link->category)->toBe(LinkCategory::REFERENCE);
});
