<?php

use App\Models\Quote;
use App\Models\User;

test('like toggles and switches to dislike', function () {
    $quote = Quote::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->postJson("/quotes/{$quote->id}/reaction", ['type' => 'like'])
        ->assertOk()->assertJson(['reaction' => 'like', 'likes' => 1, 'dislikes' => 0]);

    $this->actingAs($user)->postJson("/quotes/{$quote->id}/reaction", ['type' => 'dislike'])
        ->assertJson(['reaction' => 'dislike', 'likes' => 0, 'dislikes' => 1]);

    $this->actingAs($user)->postJson("/quotes/{$quote->id}/reaction", ['type' => 'dislike'])
        ->assertJson(['reaction' => null, 'likes' => 0, 'dislikes' => 0]);
});

test('a guest gets 401 when liking', function () {
    $quote = Quote::factory()->create();

    $this->postJson("/quotes/{$quote->id}/reaction", ['type' => 'like'])->assertUnauthorized();
});

test('a user can save a quote to favorites and remove it', function () {
    $quote = Quote::factory()->create(['content' => 'Worth saving']);
    $user = User::factory()->create();

    $this->actingAs($user)->postJson("/quotes/{$quote->id}/favorite")->assertJson(['saved' => true]);
    $this->actingAs($user)->get('/favorites')->assertSee('Worth saving');

    $this->actingAs($user)->postJson("/quotes/{$quote->id}/favorite")->assertJson(['saved' => false]);
    $this->actingAs($user)->get('/favorites')->assertDontSee('Worth saving');
});

test('each quote has its own page for sharing', function () {
    $quote = Quote::factory()->create(['content' => 'Share me']);

    $this->get("/quotes/{$quote->id}")->assertOk()->assertSee('Share me')->assertSee('data-share', false);
});

test('every published quote has a translate button', function () {
    Quote::factory()->create(['content' => 'Hashing is one-way']);

    $this->get('/')->assertSee('data-translate="Hashing is one-way"', false);
});

test('your own mp3 files in public/sounds are used for the click sounds', function () {
    $file = public_path('sounds/testclip.mp3');
    file_put_contents($file, 'fake');

    try {
        $this->get('/')->assertSee('testclip', false);
    } finally {
        unlink($file);
    }
});
