<?php

use App\Models\Comment;
use App\Models\Quote;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Support\Facades\Hash;

test('a user can comment on a quote and the owner is notified', function () {
    $quote = Quote::factory()->create();
    $fan = User::factory()->create(['name' => 'Ghala']);

    $this->actingAs($fan)->post("/quotes/{$quote->id}/comments", ['body' => 'Great point!'], ['X-Requested-With' => 'XMLHttpRequest'])
        ->assertOk()->assertSee('Great point!')->assertSee('data-count="1"', false);

    expect($quote->user->unreadNotifications)->toHaveCount(1);
    $this->get("/quotes/{$quote->id}/comments")->assertSee('Great point!');
});

test('a guest can read comments but not write them', function () {
    $quote = Quote::factory()->create();

    $this->get("/quotes/{$quote->id}/comments")->assertOk()->assertSee('to add a comment');
    $this->post("/quotes/{$quote->id}/comments", ['body' => 'Hi'])->assertRedirect(route('login'));
});

test('a comment must be 1 to 300 characters', function () {
    $quote = Quote::factory()->create();

    $this->actingAs(User::factory()->create())->post("/quotes/{$quote->id}/comments", ['body' => str_repeat('a', 301)])
        ->assertSessionHasErrors('body');
});

test('only the comment author or the quote owner can delete a comment', function () {
    $comment = Comment::factory()->create();
    $stranger = User::factory()->create();

    $this->actingAs($stranger)->delete("/comments/{$comment->id}")->assertForbidden();
    $this->actingAs($comment->quote->user)->delete("/comments/{$comment->id}")->assertRedirect();

    expect(Comment::count())->toBe(0);
});

test('you cannot comment on a private account you are not friends with', function () {
    $quote = Quote::factory()->for(User::factory()->private())->create();

    $this->actingAs(User::factory()->create())->post("/quotes/{$quote->id}/comments", ['body' => 'Hi'])->assertForbidden();
});

test('the demo people are safe for the live site: unknown passwords, added only once', function () {
    $this->seed(DemoContentSeeder::class);
    $this->seed(DemoContentSeeder::class);

    expect(User::count())->toBe(17)
        ->and(Comment::count())->toBeGreaterThan(0)
        ->and(Hash::check('password', User::first()->password))->toBeFalse();
});

test('the commenter can choose a font for their comment', function () {
    $quote = Quote::factory()->create();
    $sara = User::factory()->create();

    $this->actingAs($sara)->post("/quotes/{$quote->id}/comments", ['body' => 'خط جميل', 'font' => 'cairo'])->assertRedirect();
    expect(Comment::first()->font)->toBe('cairo');

    $this->get("/quotes/{$quote->id}/comments")->assertSee('font-family: &#039;Cairo&#039;', false);

    $this->actingAs($sara)->post("/quotes/{$quote->id}/comments", ['body' => 'x', 'font' => 'comic-sans'])->assertSessionHasErrors('font');
});
