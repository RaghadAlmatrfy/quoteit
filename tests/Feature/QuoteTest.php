<?php

use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('a guest cannot post a quote', function () {
    $this->post('/quotes', ['content' => 'Hello world', 'field' => 'ai'])->assertRedirect(route('login'));
    expect(Quote::count())->toBe(0);
});

test('a user can post a technical quote with a field and an image', function () {
    Storage::fake('uploads');
    $user = User::factory()->create();

    $this->actingAs($user)->post('/quotes', [
        'content' => 'Hashing is one-way.',
        'field' => 'security',
        // a real 1x1 PNG, so the test does not need the GD extension
        'media' => UploadedFile::fake()->createWithContent('diagram.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        )),
    ])->assertRedirect();

    $quote = Quote::first();
    expect($quote->field->value)->toBe('security')
        ->and($quote->media_type)->toBe('image');
    Storage::disk('uploads')->assertExists($quote->media_path);
});

test('a quote needs a valid field', function () {
    $this->actingAs(User::factory()->create())
        ->post('/quotes', ['content' => 'Hello world', 'field' => 'cooking'])
        ->assertSessionHasErrors('field');
});

test('only the owner can edit or delete a quote', function () {
    $quote = Quote::factory()->create();
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->put("/quotes/{$quote->id}", ['content' => 'Changed', 'field' => 'ai'])->assertForbidden();
    $this->actingAs($stranger)->delete("/quotes/{$quote->id}")->assertForbidden();

    $this->actingAs($quote->user)->put("/quotes/{$quote->id}", ['content' => 'Changed by owner', 'field' => 'cloud'])->assertRedirect();
    expect($quote->fresh()->content)->toBe('Changed by owner');

    $this->actingAs($quote->user)->delete("/quotes/{$quote->id}")->assertRedirect();
    expect(Quote::count())->toBe(0);
});

test('deleting a user deletes their quotes (cascade)', function () {
    $quote = Quote::factory()->create();
    $quote->user->delete();

    expect(Quote::count())->toBe(0);
});

test('the home page can be filtered by field', function () {
    Quote::factory()->create(['content' => 'About the cloud', 'field' => 'cloud']);
    Quote::factory()->create(['content' => 'About AI', 'field' => 'ai']);

    $this->get('/?field=cloud')->assertSee('About the cloud')->assertDontSee('About AI');
    $this->get('/communities/ai')->assertSee('About AI')->assertDontSee('About the cloud');
});

test('there is a General Tech community that everyone belongs to', function () {
    User::factory()->count(3)->create(['interests' => ['ai']]);
    Quote::factory()->create(['content' => 'Learn by building', 'field' => 'general']);

    $this->get('/communities/general')->assertOk()->assertSee('General Tech')->assertSee('Learn by building');
    $this->get('/communities')->assertSee('4 members');
});

test('a quote must be 3 to 155 characters (camp rule)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/quotes', ['content' => str_repeat('a', 156), 'field' => 'ai'])->assertSessionHasErrors('content');
    $this->actingAs($user)->post('/quotes', ['content' => 'ab', 'field' => 'ai'])->assertSessionHasErrors('content');
    $this->actingAs($user)->post('/quotes', ['content' => str_repeat('a', 155), 'field' => 'ai'])->assertSessionDoesntHaveErrors();
});

test('a visitor sees the quote text, the publisher name and the date without logging in', function () {
    $quote = Quote::factory()->create(['content' => 'Visible to visitors']);

    $this->get('/')->assertOk()->assertSee('Visible to visitors')->assertSee($quote->user->name)
        ->assertSee($quote->published_at?->format('Y-m-d') ?? $quote->created_at->format('Y-m-d'));
});

test('signup needs a name of 2 to 50 characters and a password of at least 8', function () {
    $this->post('/signup', ['name' => 'A', 'email' => 'a@example.com', 'password' => 'password123'])->assertSessionHasErrors('name');
    $this->post('/signup', ['name' => str_repeat('a', 51), 'email' => 'a@example.com', 'password' => 'password123'])->assertSessionHasErrors('name');
    $this->post('/signup', ['name' => 'Raghad', 'email' => 'a@example.com', 'password' => 'short'])->assertSessionHasErrors('password');
});

test('passwords are stored hashed, never as plain text', function () {
    $this->post('/signup', ['name' => 'Raghad', 'email' => 'raghad@example.com', 'password' => 'password123']);

    $stored = User::first()->getRawOriginal('password');
    expect($stored)->not->toBe('password123')->and(Hash::check('password123', $stored))->toBeTrue();
});

test('the quotes page loads fast (well under 3 seconds)', function () {
    Quote::factory()->count(100)->create();

    $start = microtime(true);
    $this->get('/')->assertOk();

    expect(microtime(true) - $start)->toBeLessThan(3);
});
