<?php

use App\Models\Friendship;
use App\Models\Quote;
use App\Models\Status;
use App\Models\User;

test('a friends-only status is hidden from strangers but shown to friends', function () {
    $owner = User::factory()->create();
    $this->actingAs($owner)->post('/statuses', ['body' => 'Only for friends', 'visibility' => 'friends']);

    $this->actingAs(User::factory()->create())->get('/')->assertDontSee('Only for friends');

    $friend = User::factory()->create();
    Friendship::create(['requester_id' => $friend->id, 'addressee_id' => $owner->id, 'status' => Friendship::Accepted]);
    $this->actingAs($friend)->get('/')->assertSee('Only for friends');
});

test('the owner sees who viewed and liked their status, others do not', function () {
    $owner = User::factory()->create();
    $status = $owner->statuses()->create(['body' => 'Hello', 'expires_at' => now()->addDay()]);
    $fan = User::factory()->create(['name' => 'Ghala']);

    $this->actingAs($fan)->postJson("/statuses/{$status->id}/view")->assertOk();
    $this->actingAs($fan)->postJson("/statuses/{$status->id}/like")->assertJson(['liked' => true]);

    expect($status->views()->first()->liked)->toBeTrue()
        ->and($owner->unreadNotifications)->toHaveCount(1);

    $this->actingAs($owner)->get('/')->assertSee('"viewers":[{"name":"Ghala"', false);
    $this->actingAs($fan)->get('/')->assertSee('"viewers":null', false);
});

test('you cannot see or like a status you are not allowed to see', function () {
    $owner = User::factory()->create();
    $status = $owner->statuses()->create(['body' => 'Secret', 'visibility' => Status::FriendsOnly, 'expires_at' => now()->addDay()]);

    $this->actingAs(User::factory()->create())->postJson("/statuses/{$status->id}/like")->assertForbidden();
});

test('my interests only show what I interacted with', function () {
    $me = User::factory()->create();
    $aiQuote = Quote::factory()->create(['field' => 'ai']);
    $cloudQuote = Quote::factory()->create(['field' => 'cloud']);

    $this->actingAs($me)->get('/interests')->assertSee('your interests will appear here');

    $this->actingAs($me)->postJson("/quotes/{$aiQuote->id}/reaction", ['type' => 'like']);
    $this->actingAs($me)->postJson("/quotes/{$aiQuote->id}/favorite");
    $this->actingAs($me)->postJson("/quotes/{$cloudQuote->id}/track", ['type' => 'share'])->assertOk();

    $this->actingAs($me)->get('/interests')
        ->assertSee('Artificial Intelligence')->assertSee('67%')
        ->assertSee('Cloud Computing')->assertSee('33%')
        ->assertDontSee('Cybersecurity');
});

test('only copy and share can be tracked', function () {
    $quote = Quote::factory()->create();

    $this->actingAs(User::factory()->create())->postJson("/quotes/{$quote->id}/track", ['type' => 'hack'])->assertUnprocessable();
});

test('liking a status works even right after it was viewed, and a second click removes the like', function () {
    $owner = User::factory()->create(['is_private' => false]);
    $status = $owner->statuses()->create(['body' => 'Hello', 'expires_at' => now()->addDay()]);
    $sara = User::factory()->create();

    $this->actingAs($sara)->postJson("/statuses/{$status->id}/view")->assertOk();
    $this->actingAs($sara)->postJson("/statuses/{$status->id}/like")->assertOk()->assertJson(['liked' => true]);
    $this->actingAs($sara)->postJson("/statuses/{$status->id}/like")->assertOk()->assertJson(['liked' => false]);

    expect($status->views()->count())->toBe(1);
});
