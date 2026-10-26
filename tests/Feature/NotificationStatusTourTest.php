<?php

use App\Models\Quote;
use App\Models\Status;
use App\Models\User;

test('liking someone’s quote notifies them, and they can mark it read', function () {
    $quote = Quote::factory()->create();
    $fan = User::factory()->create(['name' => 'Noura']);

    $this->actingAs($fan)->postJson("/quotes/{$quote->id}/reaction", ['type' => 'like']);

    expect($quote->user->unreadNotifications)->toHaveCount(1);
    $this->actingAs($quote->user)->get('/')->assertSee('Noura liked your quote');

    $this->actingAs($quote->user)->postJson('/notifications/read')->assertOk();
    expect($quote->user->fresh()->unreadNotifications)->toHaveCount(0);
});

test('liking your own quote does not notify you', function () {
    $quote = Quote::factory()->create();

    $this->actingAs($quote->user)->postJson("/quotes/{$quote->id}/reaction", ['type' => 'like']);

    expect($quote->user->notifications)->toHaveCount(0);
});

test('a status lasts 24 hours', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/statuses', ['body' => 'Hello status', 'bg_color' => '#432a80'])->assertRedirect(route('home'));
    $this->get('/')->assertSee('Hello status');

    $this->travel(25)->hours();
    $this->get('/')->assertDontSee('Hello status');

    $this->artisan('model:prune', ['--model' => [Status::class]]);
    expect(Status::count())->toBe(0);
});

test('statuses of private accounts are hidden from strangers', function () {
    $private = User::factory()->private()->create();
    $private->statuses()->create(['body' => 'Private status', 'expires_at' => now()->addDay()]);

    $this->actingAs(User::factory()->create())->get('/')->assertDontSee('Private status');
});

test('the tour starts once after log in and can be completed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')->assertSee('data-auto-start="true"', false);
    $this->actingAs($user)->postJson('/tour/complete')->assertOk();
    $this->actingAs($user->fresh())->get('/')->assertSee('data-auto-start="false"', false);
});
