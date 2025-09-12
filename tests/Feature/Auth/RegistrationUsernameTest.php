<?php

use App\Models\User;

test('username must be lowercase', function () {
    $response = $this->post('/register', [
        'name' => 'TestUser',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['name']);
    expect(session('errors')->first('name'))->toContain('lowercase');
});

test('username cannot contain spaces', function () {
    $response = $this->post('/register', [
        'name' => 'test user',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['name']);
    expect(session('errors')->first('name'))->toContain('letters, numbers, dashes, and underscores');
});

test('username cannot contain special characters', function () {
    $response = $this->post('/register', [
        'name' => 'test@user',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['name']);
    expect(session('errors')->first('name'))->toContain('letters, numbers, dashes, and underscores');
});

test('username must be at least 3 characters', function () {
    $response = $this->post('/register', [
        'name' => 'ab',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['name']);
    expect(session('errors')->first('name'))->toContain('at least 3 characters');
});

test('username cannot be longer than 30 characters', function () {
    $response = $this->post('/register', [
        'name' => str_repeat('a', 31),
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['name']);
    expect(session('errors')->first('name'))->toContain('may not be greater than 30 characters');
});

test('username must be unique', function () {
    User::factory()->create(['name' => 'existinguser']);

    $response = $this->post('/register', [
        'name' => 'existinguser',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors(['name']);
    expect(session('errors')->first('name'))->toContain('already taken');
});

test('valid usernames are accepted', function (string $username) {
    $response = $this->post('/register', [
        'name' => $username,
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('home'));
    $this->assertAuthenticated();
    expect(User::where('name', $username)->exists())->toBeTrue();
})->with([
    'lowercase_letters' => 'testuser',
    'with_numbers' => 'test123',
    'with_underscores' => 'test_user',
    'with_dashes' => 'test-user',
    'mixed_valid_chars' => 'test_user-123',
    'minimum_length' => 'abc',
    'maximum_length' => str_repeat('a', 30),
]);
