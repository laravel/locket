<?php

declare(strict_types=1);

use App\Enums\LinkCategory;
use App\Enums\LinkStatus;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    Queue::fake();
});

it('validates category when adding a link', function () {
    $this->actingAs($this->user)
        ->post('/links', [
            'url' => 'https://example.com',
            'category' => 'invalid_category',
        ])
        ->assertSessionHasErrors(['category']);
});

it('accepts valid category when adding a link', function () {
    $this->actingAs($this->user)
        ->post('/links', [
            'url' => 'https://example.com',
            'category' => LinkCategory::READ->value,
        ])
        ->assertSessionHasNoErrors();
});

it('requires category when adding a link', function () {
    $this->actingAs($this->user)
        ->post('/links', [
            'url' => 'https://example.com',
        ])
        ->assertSessionHasErrors(['category']);
});

it('validates status when updating a link', function () {
    // First add a link to update
    $response = $this->actingAs($this->user)
        ->post('/links', [
            'url' => 'https://example.com',
            'category' => LinkCategory::READ->value,
        ]);

    // Get the user link ID from the response or database
    $userLink = $this->user->fresh()->userLinks()->first();

    $this->actingAs($this->user)
        ->patch("/user-links/{$userLink->id}", [
            'status' => 'invalid_status',
        ])
        ->assertSessionHasErrors(['status']);
});

it('validates category when updating a link', function () {
    // First add a link to update
    $this->actingAs($this->user)
        ->post('/links', [
            'url' => 'https://example.com',
            'category' => LinkCategory::READ->value,
        ]);

    $userLink = $this->user->fresh()->userLinks()->first();

    $this->actingAs($this->user)
        ->patch("/user-links/{$userLink->id}", [
            'category' => 'invalid_category',
        ])
        ->assertSessionHasErrors(['category']);
});

it('accepts valid status and category when updating a link', function () {
    // First add a link to update
    $this->actingAs($this->user)
        ->post('/links', [
            'url' => 'https://example.com',
            'category' => LinkCategory::READ->value,
        ]);

    $userLink = $this->user->fresh()->userLinks()->first();

    $this->actingAs($this->user)
        ->patch("/user-links/{$userLink->id}", [
            'status' => LinkStatus::READING->value,
            'category' => LinkCategory::REFERENCE->value,
        ])
        ->assertSessionHasNoErrors();
});

it('validates note requirements when adding a note', function () {
    // First add a link
    $this->actingAs($this->user)
        ->post('/links', [
            'url' => 'https://example.com',
            'category' => LinkCategory::READ->value,
        ]);

    $link = $this->user->fresh()->userLinks()->first()->link;

    $this->actingAs($this->user)
        ->post('/links/notes', [
            'link_id' => $link->id,
            'note' => '', // Empty note should fail
        ])
        ->assertSessionHasErrors(['note']);
});

it('validates link_id when adding a note', function () {
    $this->actingAs($this->user)
        ->post('/links/notes', [
            'link_id' => 999999, // Non-existent link
            'note' => 'This is a valid note',
        ])
        ->assertSessionHasErrors(['link_id']);
});

it('accepts valid note data', function () {
    // First add a link
    $this->actingAs($this->user)
        ->post('/links', [
            'url' => 'https://example.com',
            'category' => LinkCategory::READ->value,
        ]);

    $link = $this->user->fresh()->userLinks()->first()->link;

    $this->actingAs($this->user)
        ->post('/links/notes', [
            'link_id' => $link->id,
            'note' => 'This is a valid note about the link',
        ])
        ->assertSessionHasNoErrors();
});
