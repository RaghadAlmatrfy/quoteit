<?php

use App\Models\Friendship;
use App\Models\Message;
use App\Models\Quote;
use App\Models\User;

test('quotes of a private account are hidden from strangers', function () {
    $private = User::factory()->private()->create();
    $quote = Quote::factory()->for($private)->create(['content' => 'Secret insight']);
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->get('/')->assertDontSee('Secret insight');
    $this->actingAs($stranger)->get("/users/{$private->username}")->assertSee('This account is private');
    $this->actingAs($stranger)->get("/quotes/{$quote->id}")->assertForbidden();
});

test('friends can see a private account', function () {
    $private = User::factory()->private()->create();
    Quote::factory()->for($private)->create(['content' => 'Secret insight']);
    $friend = User::factory()->create();
    Friendship::create(['requester_id' => $friend->id, 'addressee_id' => $private->id, 'status' => Friendship::Accepted]);

    $this->actingAs($friend)->get('/')->assertSee('Secret insight');
    $this->actingAs($friend)->get("/users/{$private->username}")->assertSee('Secret insight');
});

test('adding a public account makes you friends straight away', function () {
    $sara = User::factory()->create();
    $noura = User::factory()->create(['is_private' => false]);

    $this->actingAs($sara)->post("/users/{$noura->username}/friend")->assertRedirect();

    expect(Friendship::first()->status)->toBe(Friendship::Accepted)
        ->and($sara->isFriendsWith($noura))->toBeTrue()
        ->and($noura->notifications()->first()->data['kind'])->toBe('friend_added');
});

test('a private account must accept the friend request first', function () {
    $sara = User::factory()->create();
    $noura = User::factory()->private()->create();

    $this->actingAs($sara)->post("/users/{$noura->username}/friend")->assertRedirect();
    $friendship = Friendship::first();
    expect($friendship->status)->toBe(Friendship::Pending);

    $this->actingAs($sara)->patch("/friendships/{$friendship->id}")->assertForbidden();
    $this->actingAs($noura)->patch("/friendships/{$friendship->id}")->assertRedirect();

    expect($sara->isFriendsWith($noura))->toBeTrue();
});

test('only friends can send private messages', function () {
    $sara = User::factory()->create();
    $noura = User::factory()->create();

    $this->actingAs($sara)->post("/messages/{$noura->username}", ['body' => 'Hi'])->assertForbidden();
    $this->actingAs($sara)->get("/messages/{$noura->username}")->assertForbidden();

    Friendship::create(['requester_id' => $sara->id, 'addressee_id' => $noura->id, 'status' => Friendship::Accepted]);

    $this->actingAs($sara)->post("/messages/{$noura->username}", ['body' => 'Hi Noura'])->assertRedirect();
    $this->actingAs($noura)->get("/messages/{$sara->username}")->assertSee('Hi Noura');
    expect(Message::first()->read_at)->not->toBeNull();
});

test('a user can make their account private from the profile page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/profile', [
        'name' => 'Raghad',
        'username' => 'raghad_dev',
        'bio' => 'Data engineering',
        'specialty' => 'data',
        'interests' => ['data', 'ai'],
        'is_private' => '1',
    ])->assertRedirect('/users/raghad_dev');

    $user->refresh();
    expect($user->is_private)->toBeTrue()
        ->and($user->interests)->toBe(['data', 'ai']);
});

test('a visitor can see someone’s page and interests', function () {
    $user = User::factory()->create(['interests' => ['cloud'], 'name' => 'Maha']);

    $this->get("/users/{$user->username}")->assertOk()->assertSee('Maha')->assertSee('Cloud Computing');
});

test('the language can be switched to Arabic', function () {
    $this->from('/')->get('/locale/ar')->assertRedirect('/');

    $this->get('/')->assertSee('dir="rtl"', false)->assertSee('اقتباسات طويق');
});

test('the language switches on the same page without a redirect', function () {
    $this->get('/communities?lang=ar')->assertOk()->assertSee('dir="rtl"', false)->assertCookie('locale', 'ar', false);

    $this->get('/communities')->assertSee('dir="rtl"', false);

    $this->get('/?lang=en')->assertOk()->assertSee('dir="ltr"', false);
});

test('Arabic counts use the right plural form', function () {
    app()->setLocale('ar');

    expect(trans_choice(':count interaction|:count interactions', 1))->toBe('تفاعل واحد')
        ->and(trans_choice(':count interaction|:count interactions', 9))->toBe('9 تفاعلات')
        ->and(trans_choice(':count quote|:count quotes', 25))->toBe('25 اقتباسًا');
});
