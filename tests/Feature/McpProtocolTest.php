<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Passport\Passport;

test('MCP rejects untrusted origins on every HTTP method', function (string $method, string $origin) {
    config(['app.url' => 'https://locket.test']);
    Passport::actingAs(User::factory()->create());

    $this->json($method, '/mcp', [], ['Origin' => $origin])->assertForbidden();
})->with(['GET', 'POST', 'DELETE'])->with([
    'https://attacker.invalid',
    'https://locket.test.attacker.invalid',
    'http://locket.test',
    'https://locket.test:8443',
    'null',
]);

test('MCP accepts the configured origin but does not trust the request host', function () {
    config(['app.url' => 'https://locket.test/']);
    Passport::actingAs(User::factory()->create());

    $message = [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'server/discover',
        'params' => ['_meta' => [
            'io.modelcontextprotocol/protocolVersion' => '2026-07-28',
            'io.modelcontextprotocol/clientCapabilities' => (object) [],
        ]],
    ];
    $headers = [
        'MCP-Protocol-Version' => '2026-07-28',
        'Mcp-Method' => 'server/discover',
        'Origin' => 'https://locket.test',
    ];

    $this->postJson('/mcp', $message, $headers)->assertOk();
    $this->postJson('https://attacker.invalid/mcp', $message, array_replace($headers, [
        'Origin' => 'https://attacker.invalid',
    ]))->assertForbidden();
});

test('MCP GET streams are rejected without removing the browser information page', function () {
    $this->get('/mcp', ['Accept' => 'text/event-stream'])
        ->assertStatus(405)->assertHeader('Allow', 'POST');
    $this->get('/mcp', ['Accept' => 'text/html'])->assertOk();
});

test('MCP discovery requires authentication', function () {
    $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'server/discover',
        'params' => ['_meta' => [
            'io.modelcontextprotocol/protocolVersion' => '2026-07-28',
            'io.modelcontextprotocol/clientCapabilities' => (object) [],
        ]],
    ], [
        'MCP-Protocol-Version' => '2026-07-28',
        'Mcp-Method' => 'server/discover',
    ])->assertUnauthorized();
});

test('MCP discovery advertises the latest protocol and app extension', function () {
    Passport::actingAs(User::factory()->create());

    $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'server/discover',
        'params' => ['_meta' => [
            'io.modelcontextprotocol/protocolVersion' => '2026-07-28',
            'io.modelcontextprotocol/clientCapabilities' => (object) [],
        ]],
    ], [
        'MCP-Protocol-Version' => '2026-07-28',
        'Mcp-Method' => 'server/discover',
    ])->assertOk()
        ->assertJsonPath('result.supportedVersions', ['2026-07-28'])
        ->assertJsonStructure(['result' => ['capabilities' => ['extensions' => ['io.modelcontextprotocol/ui']]]]);
});

test('MCP tool calls validate headers and return the authenticated reading queue', function () {
    Passport::actingAs(User::factory()->create());

    $message = [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => [
            'name' => 'show-unread-queue',
            'arguments' => (object) [],
            '_meta' => [
                'io.modelcontextprotocol/protocolVersion' => '2026-07-28',
                'io.modelcontextprotocol/clientCapabilities' => (object) [],
            ],
        ],
    ];
    $headers = [
        'MCP-Protocol-Version' => '2026-07-28',
        'Mcp-Method' => 'tools/call',
        'Mcp-Name' => 'show-unread-queue',
    ];

    $this->postJson('/mcp', $message)
        ->assertStatus(400)
        ->assertJsonPath('error.code', -32020);

    $this->postJson('/mcp', $message, array_replace($headers, ['Mcp-Name' => 'start-reading']))
        ->assertStatus(400)
        ->assertJsonPath('error.code', -32020);

    $this->postJson('/mcp', $message, $headers)
        ->assertOk()
        ->assertJsonPath('result.structuredContent.items', [])
        ->assertJsonPath('result.structuredContent.message', 'Your reading queue is empty. Bookmark a link to get started.');
});

test('legacy MCP clients can initialize and call tools without modern headers', function () {
    Passport::actingAs(User::factory()->create());

    $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-11-25',
            'capabilities' => (object) [],
            'clientInfo' => ['name' => 'locket-test', 'version' => '1.0.0'],
        ],
    ])->assertOk()->assertJsonPath('result.protocolVersion', '2025-11-25');

    $this->postJson('/mcp', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => ['name' => 'show-unread-queue', 'arguments' => (object) []],
    ])->assertOk()->assertJsonPath('result.structuredContent.items', []);
});
