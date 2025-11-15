<?php

use App\Enums\UserStatusEnum;
use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('banned users cannot log in and are shown an error message', function () {
    $user = User::factory()->create([
        'status' => UserStatusEnum::BANNED,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Your account has been banned. Please contact an administrator to activate your account.');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('banned users cannot access authenticated routes', function () {
    $user = User::factory()->create([
        'status' => UserStatusEnum::BANNED,
    ]);

    // Try to access an authenticated route
    $response = $this->actingAs($user)->get('/profile');

    // Should be redirected to login
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Your account has been banned. Please contact an administrator to activate your account.');
    $this->assertGuest();
});
