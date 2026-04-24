<?php

declare(strict_types=1);

use App\Mcp\Resources\UnreadQueueApp;
use App\Mcp\Servers\Locket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('rendering', function () {
    test('renders the unread queue app shell', function () {
        Locket::resource(UnreadQueueApp::class)
            ->assertSee('Unread queue')
            ->assertSee('Start reading');
    });

    test('wires the start-reading tool call in the view', function () {
        Locket::resource(UnreadQueueApp::class)
            ->assertSee('start-reading');
    });
});

describe('resource metadata', function () {
    test('uses the ui:// URI scheme', function () {
        expect((new UnreadQueueApp)->uri())->toStartWith('ui://');
    });

    test('advertises the mcp-app MIME type and ui meta', function () {
        $data = (new UnreadQueueApp)->toArray();

        expect($data['mimeType'])->toBe('text/html;profile=mcp-app')
            ->and($data['_meta']['ui'])->toBeArray();
    });

    test('declares library CSP domains for Tailwind and Alpine', function () {
        $meta = (new UnreadQueueApp)->resolvedAppMeta();

        expect($meta['csp']['resourceDomains'])->toContain('https://cdn.tailwindcss.com')
            ->and($meta['csp']['resourceDomains'])->toContain('https://cdn.jsdelivr.net');
    });

    test('has a description', function () {
        Locket::resource(UnreadQueueApp::class)
            ->assertDescription('Unread reading queue with one-click start-reading action.');
    });
});
