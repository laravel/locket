<?php

declare(strict_types=1);

use App\Mcp\Servers\Locket;
use App\Mcp\Tools\GetRecentLinks;
use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('basic functionality', function () {
    test('returns recent links with default limit', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        Link::factory()->create([
            'url' => 'https://example.com',
            'title' => 'Example Site',
            'description' => 'An example website',
            'category' => 'read',
            'submitted_by_user_id' => $user->id,
        ]);

        Locket::tool(GetRecentLinks::class)
            ->assertOk()
            ->assertSee('Example Site')
            ->assertSee('https://example.com')
            ->assertSee('read')
            ->assertSee('John Doe')
            ->assertSee('An example website');
    });

    test('respects custom limit parameter', function () {
        $user = User::factory()->create(['name' => 'Test User']);

        // Create 5 links
        for ($i = 1; $i <= 5; $i++) {
            $link = Link::factory()->create([
                'url' => "https://example{$i}.com",
                'title' => "Example Site {$i}",
            ]);
            UserLink::factory()->create([
                'user_id' => $user->id,
                'link_id' => $link->id,
                'category' => 'read',
                'created_at' => Carbon::now()->subMinutes($i),
            ]);
        }

        $response = Locket::tool(GetRecentLinks::class, ['limit' => 3]);

        $response->assertOk()
            ->assertSee('Example Site 1')
            ->assertSee('Example Site 2')
            ->assertSee('Example Site 3');
    });

    test('returns empty message when no links exist', function () {
        Locket::tool(GetRecentLinks::class, [])
            ->assertOk()
            ->assertSee('No recent links found. Be the first to add some links to Locket!');
    });

    test('returns structured json with links array', function () {
        $user = User::factory()->create(['name' => 'Jane']);
        Link::factory()->create([
            'url' => 'https://example.com',
            'title' => 'Test Link',
            'category' => 'read',
            'submitted_by_user_id' => $user->id,
        ]);

        Locket::tool(GetRecentLinks::class)
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links')
                    ->has('message')
                    ->has('links.0', function ($link) {
                        $link->hasAll(['id', 'url', 'title', 'description', 'category', 'submitted_by', 'created_at']);
                    });
            });
    });

    test('returns empty links array when no links exist', function () {
        Locket::tool(GetRecentLinks::class)
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links')
                    ->has('message')
                    ->where('links', []);
            });
    });

    test('orders links by most recent first', function () {
        $user = User::factory()->create();

        $oldLink = Link::factory()->create([
            'title' => 'Old Link',
            'created_at' => Carbon::now()->subHours(2),
        ]);
        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $oldLink->id,
        ]);

        $newLink = Link::factory()->create([
            'title' => 'New Link',
            'created_at' => Carbon::now()->subMinutes(5),
        ]);
        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $newLink->id,
        ]);

        Locket::tool(GetRecentLinks::class, ['limit' => 2])
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links', 2)
                    ->has('message')
                    ->where('links.0.title', 'New Link')
                    ->where('links.1.title', 'Old Link');
            });
    });
});

describe('validation', function () {
    test('validates limit parameter', function () {
        // Test limit too high
        $response = Locket::tool(GetRecentLinks::class, ['limit' => 50]);
        $response->assertHasErrors(['Invalid limit, must be numeric, minimum of 1, and maximum of 25']);

        // Test limit too low
        $response = Locket::tool(GetRecentLinks::class, ['limit' => 0]);
        $response->assertHasErrors(['Invalid limit, must be numeric, minimum of 1, and maximum of 25']);

        // Test non-numeric limit
        $response = Locket::tool(GetRecentLinks::class, ['limit' => 'invalid']);
        $response->assertHasErrors(['Invalid limit, must be numeric, minimum of 1, and maximum of 25']);
    });
});

describe('content formatting', function () {
    test('displays links without description', function () {
        $user = User::factory()->create(['name' => 'Jane Doe']);
        Link::factory()->create([
            'url' => 'https://nodesc.com',
            'title' => 'No Description Site',
            'description' => null,
            'category' => 'tools',
            'submitted_by_user_id' => $user->id,
        ]);

        Locket::tool(GetRecentLinks::class)
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links.0', function ($link) {
                    $link->where('title', 'No Description Site')
                        ->where('url', 'https://nodesc.com')
                        ->where('category', 'tools')
                        ->where('description', null)
                        ->etc();
                })->etc();
            });
    });

    test('displays all link categories', function () {
        $user = User::factory()->create();
        $categories = ['read', 'reference', 'watch', 'tools'];

        foreach ($categories as $category) {
            Link::factory()->create([
                'title' => ucfirst($category).' Link',
                'category' => $category,
                'submitted_by_user_id' => $user->id,
            ]);
        }

        $response = Locket::tool(GetRecentLinks::class);

        foreach ($categories as $category) {
            $response->assertSee($category);
        }
    });

    test('includes relative timestamps', function () {
        $user = User::factory()->create();
        $link = Link::factory()->create();
        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'created_at' => Carbon::now()->subMinutes(30),
        ]);

        Locket::tool(GetRecentLinks::class)
            ->assertOk()
            ->assertSee('ago');
    });

    test('handles special characters in content', function () {
        $user = User::factory()->create(['name' => 'User & <Special>']);
        Link::factory()->create([
            'url' => 'https://example.com?test=1&foo=bar',
            'title' => 'Title with "quotes" & special <chars>',
            'description' => 'Description with [brackets] and (parentheses)',
            'submitted_by_user_id' => $user->id,
        ]);

        Locket::tool(GetRecentLinks::class)
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links.0', function ($link) {
                    $link->where('title', 'Title with "quotes" & special <chars>')
                        ->where('url', 'https://example.com?test=1&foo=bar')
                        ->where('submitted_by', 'User & <Special>')
                        ->where('description', 'Description with [brackets] and (parentheses)')
                        ->etc();
                })->etc();
            });
    });
});

describe('tool metadata', function () {
    test('has correct metadata', function () {
        Locket::tool(GetRecentLinks::class, [])
            ->assertName('get-recent-links')
            ->assertTitle('Get Recent Links')
            ->assertDescription('Get the most recently added links to Locket. Shows what new content the community has discovered and shared.');
    });
});
