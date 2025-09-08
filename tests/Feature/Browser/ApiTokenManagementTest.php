<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can create and revoke tokens through the ui', function () {
    $user = User::factory()->create();

    actingAs($user);

    $page = visit('/settings/profile');

    // Should see the API tokens section
    $page->assertSee('API Tokens');
    $page->assertSee('No API tokens yet');

    // Create a new token
    $page->click('Create Token')
        ->wait(1)
        ->type('token-name', 'My Test Token')
        ->press('Create Token') // Use press for form submission
        ->wait(2);

    // Should see the created token
    $page->assertSee('Your new API token');
    $page->click('Done');

    // Should see the token in the list
    $page->assertSee('My Test Token');
    $page->assertSee('Unused'); // Token hasn't been used yet

    // Revoke the token (confirm dialog will be accepted automatically in headless mode)
    $page->click('Revoke')
        ->wait(1);

    // Should be back to empty state
    $page->assertSee('No API tokens yet');
});

it('validates token name', function () {
    $user = User::factory()->create();

    actingAs($user);

    $page = visit('/settings/profile');

    // Try to create a token without a name
    $page->click('Create Token')
        ->wait(1)
        ->press('Create Token') // Use press for form submission
        ->wait(1);

    $page->assertSee('Please provide a name for your API token.');
});

it('displays existing tokens', function () {
    $user = User::factory()->create();
    $user->createToken('Existing Token');

    actingAs($user);

    $page = visit('/settings/profile');

    $page->assertSee('API Tokens');
    $page->assertSee('Existing Token');
    $page->assertSee('Unused'); // Since it hasn't been used
});
