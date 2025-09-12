<?php

use App\Actions\AddLink;
use App\Jobs\FetchLinkTitle;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('it dispatches FetchLinkTitle job when creating a new link', function () {
    // Arrange
    Queue::fake();
    $user = User::factory()->create();
    $addLink = new AddLink;

    // Act
    $result = $addLink->handle('https://example.com/new-article', $user);

    // Assert
    Queue::assertPushed(FetchLinkTitle::class, function ($job) use ($result) {
        return $job->link->id === $result['link']['id'];
    });
});

test('it does not dispatch FetchLinkTitle job when link already exists', function () {
    // Arrange
    Queue::fake();
    $user = User::factory()->create();
    $existingLink = Link::factory()->create([
        'url' => 'https://example.com/existing-article',
        'title' => 'Existing Article',
    ]);

    $addLink = new AddLink;

    // Act
    $addLink->handle('https://example.com/existing-article', $user);

    // Assert
    Queue::assertNotPushed(FetchLinkTitle::class);
});
