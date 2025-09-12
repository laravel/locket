<?php

declare(strict_types=1);

use App\Actions\AddLinkNote;
use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;
use Illuminate\Validation\ValidationException;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it creates note for bookmarked link', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create();
    UserLink::factory()->create(['user_id' => $user->id, 'link_id' => $link->id]);

    $action = new AddLinkNote;
    $result = $action->handle($link->id, 'This is a great article!', $user);

    expect($result['note']['note'])->toBe('This is a great article!');
    expect($result['link_id'])->toBe($link->id);
    expect(LinkNote::count())->toBe(1);
});

test('it requires user to have link bookmarked', function () {
    $user = User::factory()->create();
    $link = Link::factory()->create();

    $action = new AddLinkNote;

    expect(fn () => $action->handle($link->id, 'Note', $user))
        ->toThrow(ValidationException::class);
});
