<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login redirects to GitHub OAuth', function () {
    $response = $this->get(route('login'));

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('github.com/login/oauth/authorize');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('home'));
});
