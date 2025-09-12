<?php

declare(strict_types=1);

use App\Enums\LinkCategory;
use App\Models\User;

it('displays status and category controls in the link header', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Add a link first
    $this->post('/links', [
        'url' => 'https://example.com/article',
        'category' => LinkCategory::READ->value,
    ]);

    $page = visit('/dashboard');

    // Verify the link is displayed
    $page->assertSee('https://example.com/article')
        ->assertNoJavascriptErrors();

    // Check that status and category selects are present in the header area
    // They should be compact with specific styling
    $page->assertSeeIn('[class*="h-8 w-24 text-xs"]', 'Unread') // Default status
        ->assertSeeIn('[class*="h-8 w-24 text-xs"]', 'Read'); // Default category
});

it('can change status from the header controls', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Add a link
    $this->post('/links', [
        'url' => 'https://example.com/test-article',
        'category' => LinkCategory::READ->value,
    ]);

    $page = visit('/dashboard');

    // Find the status select and change it
    $page->assertSee('test-article')
        ->click('[class*="h-8 w-24 text-xs"]') // Click status select
        ->pause(500) // Wait for dropdown
        ->click('Reading') // Select reading status
        ->pause(1000) // Wait for form submission
        ->assertSee('Reading'); // Verify change
});

it('can change category from the header controls', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Add a link
    $this->post('/links', [
        'url' => 'https://example.com/reference-doc',
        'category' => LinkCategory::READ->value,
    ]);

    $page = visit('/dashboard');

    // Test category change
    $page->assertSee('reference-doc')
        ->pause(500)
        // We need a more specific selector since there are two selects
        ->click('[class*="h-8 w-24 text-xs"]:nth-of-type(2)') // Click category select (second one)
        ->pause(500)
        ->click('Reference') // Change to reference category
        ->pause(1000)
        ->assertSee('Reference');
});

it('header controls do not interfere with collapsible behavior', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    // Add a link
    $this->post('/links', [
        'url' => 'https://example.com/expandable-link',
        'category' => LinkCategory::READ->value,
    ]);

    $page = visit('/dashboard');

    // Click on the link title area to expand (not on the controls)
    $page->assertSee('expandable-link')
        ->click('h3') // Click the title to expand
        ->pause(500)
        ->assertSee('Add Note') // Should see the note form when expanded
        ->assertNoJavascriptErrors();
});
