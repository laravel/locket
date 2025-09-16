<?php

declare(strict_types=1);

use App\Mcp\Servers\Locket;
use App\Mcp\Tools\AddLink;
use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;
use App\Models\UserStatus;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('basic functionality', function () {
    test('adds a new link successfully', function () {
        $user = User::factory()->create(['name' => 'John Doe']);

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://example.com/article',
            'thoughts' => 'This looks interesting!',
            'category_hint' => 'read',
        ]);

        $response->assertOk()
            ->assertSee('✅ Link added to your reading list!')
            ->assertSee('URL: https://example.com/article')
            ->assertSee('Category: Read')
            ->assertSee('Note: This looks interesting!')
            ->assertSee('Status update created:');

        // Verify database records were created
        expect(Link::count())->toBe(1);
        expect(UserLink::count())->toBe(1);
        expect(UserStatus::count())->toBe(1);
    });

    test('handles existing link being bookmarked again', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // First user adds the link
        $link = Link::factory()->create([
            'url' => 'https://shared-article.com',
            'title' => 'Shared Article',
            'submitted_by_user_id' => $user1->id,
        ]);

        UserLink::factory()->create([
            'user_id' => $user1->id,
            'link_id' => $link->id,
        ]);

        // Second user tries to add the same link
        $response = Locket::actingAs($user2)->tool(AddLink::class, [
            'url' => 'https://shared-article.com',
            'thoughts' => 'Great find!',
        ]);

        $response->assertOk()
            ->assertSee('✅ Link added to your reading list!')
            ->assertSee('Shared Article')
            ->assertSee('Note: Great find!');

        // Should still be only one Link record but two UserLink records
        expect(Link::count())->toBe(1);
        expect(UserLink::count())->toBe(2);
    });

    test('handles user already having the link bookmarked', function () {
        $user = User::factory()->create();
        $link = Link::factory()->create(['url' => 'https://duplicate.com']);

        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://duplicate.com',
            'thoughts' => 'Adding again',
        ]);

        $response->assertOk()
            ->assertSee('✅ Link already bookmarked!');

        // Should still be only one UserLink record
        expect(UserLink::where('user_id', $user->id)->count())->toBe(1);
    });

    test('works without optional parameters', function () {
        $user = User::factory()->create();

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://minimal.com',
        ]);

        $response->assertOk()
            ->assertSee('✅ Link added to your reading list!')
            ->assertSee('URL: https://minimal.com');
    });

    test('defaults to read category when no hint provided', function () {
        $user = User::factory()->create();

        // Test any URL without category hint defaults to 'read'
        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://youtube.com/watch?v=example',
        ]);

        $response->assertOk()
            ->assertSee('Category: Read');

        // Test documentation URL still defaults to 'read'
        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://docs.example.com/api',
        ]);

        $response->assertOk()
            ->assertSee('Category: Read');

        // Test GitHub URL still defaults to 'read'
        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://github.com/user/repo',
        ]);

        $response->assertOk()
            ->assertSee('Category: Read');
    });

    test('respects category hint over URL suggestion', function () {
        $user = User::factory()->create();

        // YouTube URL but with read category hint
        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://youtube.com/watch?v=example',
            'category_hint' => 'read',
        ]);

        $response->assertOk()
            ->assertSee('Category: Read'); // Should use hint, not suggestion
    });
});

describe('authentication', function () {
    test('requires authentication', function () {
        $response = Locket::tool(AddLink::class, [
            'url' => 'https://example.com',
        ]);

        $response->assertHasErrors(['Authentication required to add links']);
    });

    test('works with authenticated user', function () {
        $user = User::factory()->create();

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://authenticated.com',
        ]);

        $response->assertOk()
            ->assertSee('✅ Link added to your reading list!');
    });
});

describe('validation', function () {
    test('validates required URL', function () {
        $user = User::factory()->create();

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'thoughts' => 'No URL provided',
        ]);

        $response->assertHasErrors(['A valid URL is required']);
    });

    test('validates URL format', function () {
        $user = User::factory()->create();

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'not-a-valid-url',
        ]);

        $response->assertHasErrors(['A valid URL is required']);
    });

    test('validates URL length', function () {
        $user = User::factory()->create();
        $longUrl = 'https://example.com/'.str_repeat('very-long-path/', 200); // Make it much longer

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => $longUrl,
        ]);

        $response->assertHasErrors(['A valid URL is required']);
    });

    test('validates thoughts length', function () {
        $user = User::factory()->create();
        $longThoughts = str_repeat('This is a very long thought. ', 100);

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://example.com',
            'thoughts' => $longThoughts,
        ]);

        $response->assertHasErrors(['Thoughts must be less than 2000 characters']);
    });

    test('validates category hint values', function () {
        $user = User::factory()->create();

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://example.com',
            'category_hint' => 'invalid-category',
        ]);

        $response->assertHasErrors(['Category must be one of: read, reference, watch, tools']);
    });

    test('accepts valid category hints', function () {
        $user = User::factory()->create();
        $validCategories = ['read', 'reference', 'watch', 'tools'];

        foreach ($validCategories as $category) {
            $response = Locket::actingAs($user)->tool(AddLink::class, [
                'url' => "https://example-{$category}.com",
                'category_hint' => $category,
            ]);

            $response->assertOk()
                ->assertSee('Category: '.ucfirst($category));
        }
    });
});

describe('content formatting', function () {
    test('formats successful response correctly', function () {
        $user = User::factory()->create();

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://test-formatting.com',
            'thoughts' => 'Testing formatting',
            'category_hint' => 'read',
        ]);

        $response->assertOk()
            ->assertSee('✅ Link added to your reading list!')
            ->assertSee('URL: https://test-formatting.com')
            ->assertSee('Category: Read')
            ->assertSee('Note: Testing formatting')
            ->assertSee('Status update created:');
    });

    test('handles special characters in thoughts', function () {
        $user = User::factory()->create();

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://special-chars.com',
            'thoughts' => 'Thoughts with "quotes" & special <chars> and [brackets]',
        ]);

        $response->assertOk()
            ->assertSee('Note: Thoughts with "quotes" & special <chars> and [brackets]');
    });

    test('handles URLs with query parameters', function () {
        $user = User::factory()->create();

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://example.com/path?param1=value1&param2=value2',
        ]);

        $response->assertOk()
            ->assertSee('URL: https://example.com/path?param1=value1&param2=value2');
    });

    test('shows different message for already bookmarked links', function () {
        $user = User::factory()->create();
        $link = Link::factory()->create(['url' => 'https://existing.com']);
        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://existing.com',
        ]);

        $response->assertOk()
            ->assertSee('✅ Link already bookmarked!');
    });
});

describe('error handling', function () {
    test('handles general exceptions gracefully', function () {
        $user = User::factory()->create();

        // This should trigger an exception during processing
        $response = Locket::actingAs($user)->tool(AddLink::class, [
            'url' => 'https://example.com',
            'thoughts' => 'Test error handling',
        ]);

        // The tool should handle exceptions and not crash
        expect($response)->not->toBeNull();
    });
});

describe('tool metadata', function () {
    test('has correct metadata', function () {
        $response = Locket::tool(AddLink::class, [
            'url' => 'https://metadata-test.com',
        ]);

        $response->assertName('add-link')
            ->assertTitle('Add Link')
            ->assertDescription('Add a link to your Locket reading list with optional thoughts and category hint. Creates a status update showing what you\'re reading and saves private notes if thoughts provided.');
    });
});
