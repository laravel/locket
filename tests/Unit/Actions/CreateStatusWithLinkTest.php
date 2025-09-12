<?php

declare(strict_types=1);

use App\Actions\CreateStatusWithLink;
use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;
use App\Models\UserStatus;
use Illuminate\Validation\ValidationException;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it creates link and status update', function () {
    $user = User::factory()->create();
    $action = new CreateStatusWithLink(new \App\Actions\AddLink, new \App\Actions\AddLinkNote);

    $result = $action->handle('https://example.com/article', 'This is interesting!', $user);

    expect($result['link']['url'])->toBe('https://example.com/article');
    expect($result['status']['status'])->toBe("This is interesting!\n\nSaved link: https://example.com/article");
    expect($result['already_bookmarked'])->toBeFalse();

    expect(Link::count())->toBe(1);
    expect(UserLink::count())->toBe(1);
    expect(UserStatus::count())->toBe(1);

    // Verify the status is linked to the link
    $status = UserStatus::first();
    expect($status->link_id)->toBe($result['link']['id']);
});

test('it creates status update without thoughts', function () {
    $user = User::factory()->create();
    $action = new CreateStatusWithLink(new \App\Actions\AddLink, new \App\Actions\AddLinkNote);

    $result = $action->handle('https://example.com/article', null, $user);

    expect($result['status']['status'])->toBe('Saved link: https://example.com/article');
});

test('it handles already bookmarked link', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create(['url' => 'https://example.com/article']);
    UserLink::factory()->create(['user_id' => $user->id, 'link_id' => $link->id]);

    $action = new CreateStatusWithLink(new \App\Actions\AddLink, new \App\Actions\AddLinkNote);
    $result = $action->handle('https://example.com/article', 'Already seen this', $user);

    expect($result['status']['status'])->toBe("Already seen this\n\nBookmarked link: https://example.com/article");
    expect($result['already_bookmarked'])->toBeTrue();
});

test('it validates URL', function () {
    $user = User::factory()->create();
    $action = new CreateStatusWithLink(new \App\Actions\AddLink, new \App\Actions\AddLinkNote);

    expect(fn () => $action->handle('invalid-url', 'thoughts', $user))
        ->toThrow(ValidationException::class);
});
