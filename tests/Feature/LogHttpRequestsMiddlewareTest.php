<?php

declare(strict_types=1);

it('logs GET requests with all required details', function () {
    $response = $this->get('/?test=value', [
        'User-Agent' => 'Test Browser/1.0',
    ]);

    $response->assertStatus(200);

    // Check that the log file contains our log entry
    $logContent = file_get_contents(storage_path('logs/laravel.log'));
    expect($logContent)->toContain('HTTP Request/Response');
    expect($logContent)->toContain('GET');
    expect($logContent)->toContain('http://localhost/?test=value');
    expect($logContent)->toContain('Test Browser/1.0');
    expect($logContent)->toContain('"status_code":200');
});

it('logs POST requests with body data', function () {
    $postData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];

    $response = $this->postJson('/api/test', $postData, [
        'User-Agent' => 'API Client/1.0',
    ]);

    $logContent = file_get_contents(storage_path('logs/laravel.log'));
    expect($logContent)->toContain('HTTP Request/Response');
    expect($logContent)->toContain('POST');
    expect($logContent)->toContain('API Client/1.0');
    expect($logContent)->toContain('John Doe');
    expect($logContent)->toContain('john@example.com');
});

it('filters sensitive data from request body', function () {
    $postData = [
        'name' => 'John Doe',
        'password' => 'secret123',
        'api_key' => 'abc123',
        'email' => 'john@example.com',
    ];

    $this->postJson('/api/test', $postData);

    $logContent = file_get_contents(storage_path('logs/laravel.log'));
    expect($logContent)->toContain('HTTP Request/Response');
    expect($logContent)->toContain('John Doe');
    expect($logContent)->toContain('john@example.com');
    expect($logContent)->not->toContain('secret123');
    expect($logContent)->not->toContain('abc123');
});

it('filters sensitive headers', function () {
    $this->get('/', [
        'Authorization' => 'Bearer token123',
        'Cookie' => 'session=abc123',
        'User-Agent' => 'Test Browser',
    ]);

    $logContent = file_get_contents(storage_path('logs/laravel.log'));
    expect($logContent)->toContain('HTTP Request/Response');
    expect($logContent)->toContain('Test Browser');
    expect($logContent)->not->toContain('Bearer token123');
    expect($logContent)->not->toContain('session=abc123');
});

it('logs response body for small responses', function () {
    $response = $this->get('/');

    $logContent = file_get_contents(storage_path('logs/laravel.log'));
    expect($logContent)->toContain('HTTP Request/Response');
    expect($logContent)->toContain('"body":');
});

it('includes request duration in logs', function () {
    $this->get('/');

    $logContent = file_get_contents(storage_path('logs/laravel.log'));
    expect($logContent)->toContain('HTTP Request/Response');
    expect($logContent)->toContain('"duration_ms":');
});
