<?php

use App\Jobs\FetchLinkTitle;
use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('it fetches and updates link title from HTML', function () {
    // Arrange
    $user = User::factory()->create();
    $link = Link::factory()->create([
        'url' => 'https://example.com',
        'title' => '',
        'submitted_by_user_id' => $user->id,
    ]);

    $htmlResponse = '<html><head><title>Awesome Article About Testing</title></head><body></body></html>';

    Http::fake([
        'https://example.com' => Http::response($htmlResponse, 200),
    ]);

    // Act
    $job = new FetchLinkTitle($link);
    $job->handle();

    // Assert
    $link->refresh();
    expect($link->title)->toBe('Awesome Article About Testing');
});

test('it cleans up title with extra whitespace and entities', function () {
    // Arrange
    $user = User::factory()->create();
    $link = Link::factory()->create([
        'url' => 'https://example.com',
        'title' => '',
        'submitted_by_user_id' => $user->id,
    ]);

    $htmlResponse = '<html><head><title>  Testing &amp; Development
    with   Multiple   Spaces  </title></head><body></body></html>';

    Http::fake([
        'https://example.com' => Http::response($htmlResponse, 200),
    ]);

    // Act
    $job = new FetchLinkTitle($link);
    $job->handle();

    // Assert
    $link->refresh();
    expect($link->title)->toBe('Testing & Development with Multiple Spaces');
});

test('it handles HTTP errors gracefully', function () {
    // Arrange
    $user = User::factory()->create();
    $link = Link::factory()->create([
        'url' => 'https://example.com',
        'title' => '',
        'submitted_by_user_id' => $user->id,
    ]);

    Http::fake([
        'https://example.com' => Http::response('', 404),
    ]);

    // Act
    $job = new FetchLinkTitle($link);
    $job->handle();

    // Assert - title should remain empty, no exception should be thrown
    $link->refresh();
    expect($link->title)->toBe('');
});

test('it handles pages without title tags', function () {
    // Arrange
    $user = User::factory()->create();
    $link = Link::factory()->create([
        'url' => 'https://example.com',
        'title' => '',
        'submitted_by_user_id' => $user->id,
    ]);

    $htmlResponse = '<html><head></head><body><h1>No Title Tag</h1></body></html>';

    Http::fake([
        'https://example.com' => Http::response($htmlResponse, 200),
    ]);

    // Act
    $job = new FetchLinkTitle($link);
    $job->handle();

    // Assert
    $link->refresh();
    expect($link->title)->toBe('');
});
