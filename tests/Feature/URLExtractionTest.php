<?php

use App\Models\User;

it('can handle URL with thoughts in status form', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/status-with-link', [
        'url' => 'https://example.com',
        'thoughts' => 'This is a great article about testing',
    ]);

    $response->assertRedirect();
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can handle URL extraction when URL is provided correctly', function () {
    $user = User::factory()->create();

    // Test with a valid URL - should work as before
    $response = $this->actingAs($user)->post('/status-with-link', [
        'url' => 'https://laravel.com',
        'thoughts' => '',
    ]);

    $response->assertRedirect();
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('rejects empty URL', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/status-with-link', [
        'url' => '',
        'thoughts' => 'Just some thoughts',
    ]);

    $response->assertSessionHasErrors(['url']);
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('rejects invalid URL', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/status-with-link', [
        'url' => 'not-a-valid-url',
        'thoughts' => 'Some thoughts',
    ]);

    $response->assertSessionHasErrors(['url']);
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('rejects URL without protocol', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/status-with-link', [
        'url' => 'example.com',
        'thoughts' => 'Some thoughts',
    ]);

    $response->assertSessionHasErrors(['url']);
})->uses(Illuminate\Foundation\Testing\RefreshDatabase::class);
