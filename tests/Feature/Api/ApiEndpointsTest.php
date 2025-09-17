<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\User;
use App\Models\UserLink;
use App\Models\UserStatus;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    // Create personal access client for testing
    Artisan::call('passport:client', ['--personal' => true, '--no-interaction' => true]);

    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('Test Token');
    $this->headers = ['Authorization' => 'Bearer '.$this->token->accessToken];
});

describe('Links API', function () {
    it('can get recent links', function () {
        // Create some links with users
        $users = User::factory()->count(3)->create();
        $links = [];

        foreach ($users as $user) {
            $link = Link::factory()->create();
            UserLink::factory()->create([
                'user_id' => $user->id,
                'link_id' => $link->id,
            ]);
            $links[] = $link;
        }

        $response = $this->getJson('/api/links/recent', $this->headers);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'url',
                    'title',
                    'description',
                    'category',
                    'created_at',
                ],
            ],
            'meta' => [
                'count',
                'limit',
            ],
        ]);
    });

    it('can get trending links', function () {
        // Create some links with multiple bookmarks today
        $link = Link::factory()->create();
        $users = User::factory()->count(5)->create();

        foreach ($users as $user) {
            UserLink::factory()->create([
                'user_id' => $user->id,
                'link_id' => $link->id,
                'created_at' => now(),
            ]);
        }

        $response = $this->getJson('/api/links/trending', $this->headers);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data',
            'meta' => [
                'count',
                'limit',
            ],
        ]);
    });

    it('can add a new link', function () {
        Queue::fake();
        $response = $this->postJson('/api/links', [
            'url' => 'https://example.com/article',
            'thoughts' => 'Great article about Laravel',
            'category_hint' => 'read',
        ], $this->headers);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'link' => [
                    'id',
                    'url',
                    'title',
                ],
                'user_link',
                'status',
            ],
            'meta' => [
                'already_bookmarked',
            ],
        ]);

        // Check database
        expect(Link::where('url', 'https://example.com/article')->exists())->toBeTrue();
        expect($this->user->userLinks()->count())->toBe(1);
        expect($this->user->statuses()->count())->toBe(1);
    });

    it('validates link creation input', function () {
        $response = $this->postJson('/api/links', [
            'url' => 'not-a-url',
            'category_hint' => 'invalid',
        ], $this->headers);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['url', 'category_hint']);
    });

    it('respects limit parameter for recent links', function () {
        // Create 10 links
        $users = User::factory()->count(10)->create();
        foreach ($users as $user) {
            $link = Link::factory()->create();
            UserLink::factory()->create([
                'user_id' => $user->id,
                'link_id' => $link->id,
            ]);
        }

        $response = $this->getJson('/api/links/recent?limit=5', $this->headers);

        $response->assertSuccessful();
        expect($response->json('data'))->toHaveCount(5);
        expect($response->json('meta.limit'))->toBe(5);
    });
});

describe('Statuses API', function () {
    it('can get recent statuses', function () {
        // Create some statuses
        $users = User::factory()->count(3)->create();
        foreach ($users as $user) {
            UserStatus::factory()->create(['user_id' => $user->id]);
        }

        $response = $this->getJson('/api/statuses/recent', $this->headers);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user',
                    'status',
                    'created_at',
                ],
            ],
            'meta' => [
                'count',
                'limit',
            ],
        ]);
    });

    it('respects limit parameter for recent statuses', function () {
        // Create 20 statuses
        $users = User::factory()->count(20)->create();
        foreach ($users as $user) {
            UserStatus::factory()->create(['user_id' => $user->id]);
        }

        $response = $this->getJson('/api/statuses/recent?limit=10', $this->headers);

        $response->assertSuccessful();
        expect($response->json('data'))->toHaveCount(10);
        expect($response->json('meta.limit'))->toBe(10);
    });
});

describe('API Authentication', function () {
    it('requires authentication for all endpoints', function () {
        $endpoints = [
            ['GET', '/api/links/recent'],
            ['GET', '/api/links/trending'],
            ['POST', '/api/links'],
            ['GET', '/api/statuses/recent'],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertUnauthorized();
        }
    });

    it('works with valid passport token', function () {
        $response = $this->getJson('/api/user', $this->headers);

        $response->assertSuccessful();
        expect($response->json('id'))->toBe($this->user->id);
    });

    it('rejects revoked tokens', function () {
        $this->token->token->revoke();

        $response = $this->getJson('/api/user', $this->headers);

        $response->assertUnauthorized();
    });
});
