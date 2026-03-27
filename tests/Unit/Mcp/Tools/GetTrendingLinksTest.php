<?php

declare(strict_types=1);

use App\Mcp\Servers\Locket;
use App\Mcp\Tools\GetTrendingLinks;
use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('basic functionality', function () {
    test('returns trending links with default limit', function () {
        $user = User::factory()->create(['name' => 'John Doe']);
        $link = Link::factory()->create([
            'url' => 'https://trending.com',
            'title' => 'Trending Article',
            'description' => 'A very popular article',
            'category' => 'read',
        ]);

        // Create multiple bookmarks for today to make it trending
        UserLink::factory()->count(3)->create([
            'link_id' => $link->id,
            'created_at' => Carbon::today()->addHours(2),
        ]);

        Locket::tool(GetTrendingLinks::class)
            ->assertOk()
            ->assertSee('Trending Article')
            ->assertSee('https://trending.com')
            ->assertSee('A very popular article');
    });

    test('respects custom limit parameter', function () {
        $user = User::factory()->create();

        // Create 5 links with different bookmark counts
        for ($i = 1; $i <= 5; $i++) {
            $link = Link::factory()->create([
                'title' => "Trending Link {$i}",
                'url' => "https://trending{$i}.com",
            ]);

            // Create varying numbers of bookmarks for today
            UserLink::factory()->count($i)->create([
                'link_id' => $link->id,
                'created_at' => Carbon::today()->addMinutes($i * 10),
            ]);
        }

        Locket::tool(GetTrendingLinks::class, ['limit' => 3])
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links', 3)
                    ->has('message')
                    ->where('links.0.title', 'Trending Link 5')
                    ->where('links.1.title', 'Trending Link 4')
                    ->where('links.2.title', 'Trending Link 3');
            });
    });

    test('returns empty message when no trending links exist', function () {
        Locket::tool(GetTrendingLinks::class)
            ->assertOk()
            ->assertSee('No trending links found today. Be the first to add some links to Locket!');
    });

    test('returns structured json with links array', function () {
        $link = Link::factory()->create(['title' => 'Test Link']);
        UserLink::factory()->create([
            'link_id' => $link->id,
            'created_at' => Carbon::today(),
        ]);

        Locket::tool(GetTrendingLinks::class)
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links')
                    ->has('message')
                    ->has('links.0', function ($link) {
                        $link->hasAll(['id', 'url', 'title', 'description', 'category', 'bookmark_count']);
                    });
            });
    });

    test('returns empty links array when no links exist', function () {
        Locket::tool(GetTrendingLinks::class)
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links')
                    ->has('message')
                    ->where('links', []);
            });
    });

    test('only shows links bookmarked today', function () {
        $user = User::factory()->create();

        // Create link bookmarked yesterday
        $oldLink = Link::factory()->create(['title' => 'Yesterday Link']);
        UserLink::factory()->count(5)->create([
            'link_id' => $oldLink->id,
            'created_at' => Carbon::yesterday(),
        ]);

        // Create link bookmarked today
        $todayLink = Link::factory()->create(['title' => 'Today Link']);
        UserLink::factory()->count(2)->create([
            'link_id' => $todayLink->id,
            'created_at' => Carbon::today()->addHours(1),
        ]);

        Locket::tool(GetTrendingLinks::class)
            ->assertOk()
            ->assertSee('Today Link')
            ->assertDontSee('Yesterday Link');
    });

    test('orders links by bookmark count descending', function () {
        $user = User::factory()->create();

        // Create link with fewer bookmarks
        $lessPopular = Link::factory()->create(['title' => 'Less Popular']);
        UserLink::factory()->count(2)->create([
            'link_id' => $lessPopular->id,
            'created_at' => Carbon::today(),
        ]);

        // Create link with more bookmarks
        $morePopular = Link::factory()->create(['title' => 'More Popular']);
        UserLink::factory()->count(5)->create([
            'link_id' => $morePopular->id,
            'created_at' => Carbon::today(),
        ]);

        Locket::tool(GetTrendingLinks::class)
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links', 2)
                    ->has('message')
                    ->where('links.0.title', 'More Popular')
                    ->where('links.1.title', 'Less Popular');
            });
    });
});

describe('validation', function () {
    test('validates limit parameter', function () {
        // Test limit too high
        $response = Locket::tool(GetTrendingLinks::class, ['limit' => 50]);
        $response->assertHasErrors(['Invalid limit, must be numeric, minimum of 1, and maximum of 25']);

        // Test limit too low
        $response = Locket::tool(GetTrendingLinks::class, ['limit' => 0]);
        $response->assertHasErrors(['Invalid limit, must be numeric, minimum of 1, and maximum of 25']);

        // Test non-numeric limit
        $response = Locket::tool(GetTrendingLinks::class, ['limit' => 'invalid']);
        $response->assertHasErrors(['Invalid limit, must be numeric, minimum of 1, and maximum of 25']);
    });
});

describe('content formatting', function () {
    test('displays links without description', function () {
        $link = Link::factory()->create([
            'url' => 'https://nodesc.com',
            'title' => 'No Description Site',
            'description' => null,
            'category' => 'tools',
        ]);

        UserLink::factory()->create([
            'link_id' => $link->id,
            'created_at' => Carbon::today(),
        ]);

        Locket::tool(GetTrendingLinks::class)
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
        $categories = ['read', 'reference', 'watch', 'tools'];

        foreach ($categories as $category) {
            $link = Link::factory()->create([
                'title' => ucfirst($category).' Link',
                'category' => $category,
            ]);

            UserLink::factory()->create([
                'link_id' => $link->id,
                'created_at' => Carbon::today(),
            ]);
        }

        $response = Locket::tool(GetTrendingLinks::class);

        foreach ($categories as $category) {
            $response->assertSee($category);
        }
    });

    test('includes bookmark count', function () {
        $user = User::factory()->create();

        $link = Link::factory()->create(['title' => 'Popular Link']);
        UserLink::factory()->count(3)->create([
            'link_id' => $link->id,
            'created_at' => Carbon::today(),
        ]);

        Locket::tool(GetTrendingLinks::class)
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links.0', function ($link) {
                    $link->where('bookmark_count', 3)->etc();
                })->etc();
            });
    });

    test('handles special characters in content', function () {
        $link = Link::factory()->create([
            'url' => 'https://example.com?test=1&foo=bar',
            'title' => 'Title with "quotes" & special <chars>',
            'description' => 'Description with [brackets] and (parentheses)',
        ]);

        UserLink::factory()->create([
            'link_id' => $link->id,
            'created_at' => Carbon::today(),
        ]);

        Locket::tool(GetTrendingLinks::class)
            ->assertOk()
            ->assertStructuredContent(function ($json) {
                $json->has('links.0', function ($link) {
                    $link->where('title', 'Title with "quotes" & special <chars>')
                        ->where('url', 'https://example.com?test=1&foo=bar')
                        ->where('description', 'Description with [brackets] and (parentheses)')
                        ->etc();
                })->etc();
            });
    });
});

describe('tool metadata', function () {
    test('has correct metadata', function () {
        Locket::tool(GetTrendingLinks::class)
            ->assertName('get-trending-links')
            ->assertTitle('Get Trending Links')
            ->assertDescription('Get trending links that are popular today based on how many users have bookmarked them. Shows what the Locket community is reading right now.');
    });
});
