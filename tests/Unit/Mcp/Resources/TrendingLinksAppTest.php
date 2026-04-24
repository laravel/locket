<?php

declare(strict_types=1);

use App\Mcp\Resources\TrendingLinksApp;
use App\Mcp\Servers\Locket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('rendering', function () {
    test('renders the trending links app shell', function () {
        Locket::resource(TrendingLinksApp::class)
            ->assertSee('Trending today')
            ->assertSee('Bookmark')
            ->assertSee('Summarise');
    });

    test('wires the add-link tool call for bookmarking', function () {
        Locket::resource(TrendingLinksApp::class)
            ->assertSee('add-link');
    });
});

describe('resource metadata', function () {
    test('uses the ui:// URI scheme', function () {
        expect((new TrendingLinksApp)->uri())->toStartWith('ui://');
    });

    test('advertises the mcp-app MIME type and ui meta', function () {
        $data = (new TrendingLinksApp)->toArray();

        expect($data['mimeType'])->toBe('text/html;profile=mcp-app')
            ->and($data['_meta']['ui'])->toBeArray();
    });

    test('declares library CSP domains for Tailwind and Alpine', function () {
        $meta = (new TrendingLinksApp)->resolvedAppMeta();

        expect($meta['csp']['resourceDomains'])->toContain('https://cdn.tailwindcss.com')
            ->and($meta['csp']['resourceDomains'])->toContain('https://cdn.jsdelivr.net');
    });
});
