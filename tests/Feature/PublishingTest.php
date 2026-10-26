<?php

use App\Models\Quote;
use App\Models\User;

test('a draft is only visible to its author', function () {
    $author = User::factory()->create();
    $this->actingAs($author)->post('/quotes', ['content' => 'My secret draft', 'field' => 'ai', 'intent' => 'draft'])
        ->assertRedirect(route('drafts.index'));

    $quote = Quote::first();
    expect($quote->isDraft())->toBeTrue();

    $this->actingAs($author)->get('/drafts')->assertSee('My secret draft');
    $this->actingAs($author)->get('/')->assertDontSee('My secret draft');
    $this->actingAs(User::factory()->create())->get("/quotes/{$quote->id}")->assertForbidden();
});

test('a scheduled quote appears only when its time comes', function () {
    $author = User::factory()->create();
    $this->actingAs($author)->post('/quotes', [
        'content' => 'Posted tomorrow',
        'field' => 'cloud',
        'intent' => 'schedule',
        'publish_at' => now()->addDay()->toIso8601String(),
    ]);

    $this->get('/')->assertDontSee('Posted tomorrow');

    $this->travel(25)->hours();
    $this->get('/')->assertSee('Posted tomorrow');
});

test('a schedule time in the past is rejected', function () {
    $this->actingAs(User::factory()->create())->post('/quotes', [
        'content' => 'Too late', 'field' => 'ai', 'intent' => 'schedule', 'publish_at' => now()->subHour()->toIso8601String(),
    ])->assertSessionHasErrors('publish_at');
});

test('a draft can be published now', function () {
    $quote = Quote::factory()->create(['status' => Quote::Draft, 'content' => 'Ready now']);

    $this->actingAs($quote->user)->post("/quotes/{$quote->id}/publish")->assertRedirect(route('home'));
    $this->get('/')->assertSee('Ready now');
});

test('the font, size, bold, italic, alignment and background are saved and shown', function () {
    $this->actingAs(User::factory()->create())->post('/quotes', [
        'content' => 'Styled quote', 'field' => 'data', 'intent' => 'publish',
        'font' => 'amiri', 'font_size' => 'xl', 'is_bold' => '1', 'is_italic' => '1',
        'text_align' => 'center', 'bg_color' => '#4fc9b0',
    ]);

    $quote = Quote::first();
    expect($quote->font)->toBe('amiri')
        ->and($quote->is_bold)->toBeTrue()
        ->and($quote->text_align)->toBe('center');

    $this->get('/')->assertSee('background-color: #4fc9b0', false)->assertSee("font-family: 'Amiri', serif")
        ->assertSee('text-2xl font-bold italic text-center', false);
});

test('an invalid background color is rejected', function () {
    $this->actingAs(User::factory()->create())
        ->post('/quotes', ['content' => 'Hello', 'field' => 'ai', 'bg_color' => 'red; display:none'])
        ->assertSessionHasErrors('bg_color');
});
