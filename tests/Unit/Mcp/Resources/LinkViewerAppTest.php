<?php

declare(strict_types=1);

use App\Mcp\Resources\LinkViewerApp;
use App\Mcp\Servers\Locket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('basic functionality', function () {
    test('renders the link viewer app', function () {
        Locket::resource(LinkViewerApp::class)
            ->assertSee('Link Viewer');
    });

    test('includes loading skeleton', function () {
        Locket::resource(LinkViewerApp::class)
            ->assertSee('animate-pulse');
    });

    test('listens for tool result via onToolResult', function () {
        Locket::resource(LinkViewerApp::class)
            ->assertSee('app.onToolResult');
    });

    test('handles both recent and trending link metadata', function () {
        $response = Locket::resource(LinkViewerApp::class);

        $response->assertSee('link.submitted_by')
            ->assertSee('link.bookmark_count');
    });
});

describe('resource metadata', function () {
    test('has correct uri scheme', function () {
        expect((new LinkViewerApp)->uri())->toStartWith('ui://');
    });

    test('has correct mime type', function () {
        $data = (new LinkViewerApp)->toArray();

        expect($data['mimeType'])->toBe('text/html;profile=mcp-app')
            ->and($data['_meta']['ui'])->toBeArray();
    });

    test('has correct description', function () {
        Locket::resource(LinkViewerApp::class)
            ->assertDescription('Browse and discover links shared on Locket.');
    });

    test('configures app meta with resource domains', function () {
        $meta = (new LinkViewerApp)->resolvedAppMeta();

        expect($meta['csp']['resourceDomains'])->toContain('https://cdn.tailwindcss.com')
            ->and($meta['csp']['resourceDomains'])->toContain('https://cdn.jsdelivr.net');
    });
});
