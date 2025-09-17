<?php

declare(strict_types=1);

use App\Mcp\Resources\LastAddedLink;
use App\Mcp\Servers\Locket;
use App\Models\Link;
use App\Models\LinkNote;
use App\Models\User;
use App\Models\UserLink;

uses(\Tests\TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('basic functionality', function () {
    test('returns last added link with all details', function () {
        $user = User::factory()->create(['name' => 'John Doe']);

        $link = Link::factory()->create([
            'url' => 'https://example.com/article',
            'title' => 'Interesting Article',
            'description' => 'A very interesting article about technology',
        ]);

        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'category' => 'read',
            'status' => 'unread',
        ]);

        $response = Locket::actingAs($user)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee(['📖 **Your Last Added Link**', '**Interesting Article**',
                'URL: https://example.com/article',
                'Category: Read',
                'Status: Unread',
                'Description: A very interesting article about technology',
                '*No notes attached to this link.*']);
    });

    test('returns link with notes when notes exist', function () {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'title' => 'Article with Notes',
            'url' => 'https://example.com/notes',
        ]);

        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        // Add some notes
        LinkNote::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'note' => 'This is my first note',
        ]);

        LinkNote::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'note' => 'This is my second note',
        ]);

        $response = Locket::actingAs($user)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee(['**Article with Notes**',
                '**📝 Your Notes:**',
                '• This is my first note',
                '• This is my second note']);
    });

    test('returns most recent link when user has multiple links', function () {
        $user = User::factory()->create();

        // Create older link
        $olderLink = Link::factory()->create([
            'title' => 'Older Article',
            'url' => 'https://example.com/older',
        ]);

        $olderUserLink = UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $olderLink->id,
            'created_at' => now()->subDays(2),
        ]);

        // Create newer link
        $newerLink = Link::factory()->create([
            'title' => 'Newer Article',
            'url' => 'https://example.com/newer',
        ]);

        $newerUserLink = UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $newerLink->id,
            'created_at' => now()->subDay(),
        ]);

        $response = Locket::actingAs($user)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee('**Newer Article**')
            ->assertSee('URL: https://example.com/newer');
    });

    test('handles link without description', function () {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'title' => 'Article Without Description',
            'url' => 'https://example.com/no-desc',
            'description' => null,
        ]);

        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        $response = Locket::actingAs($user)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee('**Article Without Description**')
            ->assertSee('URL: https://example.com/no-desc');
    });

    test('handles different categories and statuses', function () {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'title' => 'Reference Document',
            'url' => 'https://docs.example.com',
        ]);

        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'category' => 'reference',
            'status' => 'read',
        ]);

        $response = Locket::actingAs($user)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee('Category: Reference')
            ->assertSee('Status: Read');
    });
});

describe('edge cases', function () {
    test('returns no links message when user has no links', function () {
        $user = User::factory()->create();

        $response = Locket::actingAs($user)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee('⚠️ **No Links Found**')
            ->assertSee("You haven't added any links to your Locket yet. Try adding your first link!");
    });

    test('only shows notes for the authenticated user', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $link = Link::factory()->create([
            'title' => 'Shared Article',
            'url' => 'https://example.com/shared',
        ]);

        // Both users add the same link
        UserLink::factory()->create([
            'user_id' => $user1->id,
            'link_id' => $link->id,
        ]);

        UserLink::factory()->create([
            'user_id' => $user2->id,
            'link_id' => $link->id,
        ]);

        // User1 adds a note
        LinkNote::factory()->create([
            'user_id' => $user1->id,
            'link_id' => $link->id,
            'note' => 'User 1 note',
        ]);

        // User2 adds a different note
        LinkNote::factory()->create([
            'user_id' => $user2->id,
            'link_id' => $link->id,
            'note' => 'User 2 note',
        ]);

        // User1 should only see their own note
        $response = Locket::actingAs($user1)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee('• User 1 note');

        // User2 should only see their own note
        $response = Locket::actingAs($user2)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee('• User 2 note');
    });
});

describe('authentication', function () {
    test('requires authentication', function () {
        $response = Locket::resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee('❌ **Authentication Required**')
            ->assertSee('You must be authenticated to view your last added link.');
    });

    test('works with authenticated user', function () {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'title' => 'Authenticated Test',
            'url' => 'https://example.com/auth',
        ]);

        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        $response = Locket::actingAs($user)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee('📖 **Your Last Added Link**')
            ->assertSee('**Authenticated Test**');
    });
});

describe('content formatting', function () {
    test('formats response correctly with all sections', function () {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'title' => 'Complete Article',
            'url' => 'https://example.com/complete',
            'description' => 'A complete article with description',
        ]);

        $userLink = UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'category' => 'tools',
            'status' => 'archived',
        ]);

        LinkNote::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'note' => 'My thoughts on this',
        ]);

        $response = Locket::actingAs($user)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee(['📖 **Your Last Added Link**',
                '**Complete Article**',
                'URL: https://example.com/complete',
                'Category: Tools',
                'Status: Archived',
                'Added:',
                'Description: A complete article with description',
                '**📝 Your Notes:**',
                '• My thoughts on this']);
    });

    test('handles special characters in content', function () {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'title' => 'Article with "quotes" & special <chars>',
            'url' => 'https://example.com/special?param=value&other=data',
            'description' => 'Description with [brackets] and símböls',
        ]);

        UserLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        LinkNote::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
            'note' => 'Note with émojis 🎉 and "special" chars',
        ]);

        $response = Locket::actingAs($user)->resource(LastAddedLink::class);

        $response->assertOk()
            ->assertSee('**Article with "quotes" & special <chars>**')
            ->assertSee('https://example.com/special?param=value&other=data')
            ->assertSee('Description with [brackets] and símböls')
            ->assertSee('• Note with émojis 🎉 and "special" chars');
    });
});

describe('resource metadata', function () {
    test('has correct metadata', function () {
        $response = Locket::resource(LastAddedLink::class);

        $response->assertName('last-added-link')
            ->assertDescription('The user\'s most recently added link with any attached notes.');
    });
});
