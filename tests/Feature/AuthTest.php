<?php

use App\Models\User;

test('a visitor can sign up and is logged in', function () {
    $this->post('/signup', [
        'name' => 'Raghad',
        'email' => 'raghad@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticated();
    expect(User::first()->username)->toBe('raghad');
});

test('the same email cannot sign up twice', function () {
    User::factory()->create(['email' => 'raghad@example.com']);

    $this->post('/signup', [
        'name' => 'Raghad',
        'email' => 'raghad@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors('email');
});

test('a user can log in and log out, and their data stays saved', function () {
    $user = User::factory()->create(['password' => 'password123']);
    $user->quotes()->create(['content' => 'Saved before logout', 'field' => 'ai']);

    $this->post('/login', ['email' => $user->email, 'password' => 'password123'])->assertRedirect(route('home'));
    $this->assertAuthenticatedAs($user);

    $this->post('/logout')->assertRedirect(route('home'));
    $this->assertGuest();

    $this->post('/login', ['email' => $user->email, 'password' => 'password123']);
    $this->get('/')->assertSee('Saved before logout');
});

test('a wrong password shows an error', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});
