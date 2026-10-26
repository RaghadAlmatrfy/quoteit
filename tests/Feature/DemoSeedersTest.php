<?php

use App\Models\Comment;
use App\Models\Quote;
use App\Models\Reaction;
use App\Models\Status;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\DemoStatusesSeeder;
use Database\Seeders\MoreDemoContentSeeder;

test('the demo content can run many times without making copies', function () {
    foreach (range(1, 2) as $run) {
        $this->seed([DemoContentSeeder::class, MoreDemoContentSeeder::class, DemoStatusesSeeder::class]);
    }

    expect(User::count())->toBe(31)
        ->and(Comment::count())->toBe(20)
        ->and(Status::active()->count())->toBe(6)
        ->and(Reaction::count())->toBeGreaterThan(0)
        ->and(Quote::where('field', 'cs')->count())->toBeGreaterThan(0)
        ->and(Quote::where('field', 'hci')->count())->toBeGreaterThan(0);
});

test('every demo quote follows the camp rule of 3 to 155 characters', function () {
    $this->seed([DemoContentSeeder::class, MoreDemoContentSeeder::class]);

    Quote::all()->each(fn (Quote $quote) => expect(mb_strlen($quote->content))->toBeBetween(3, 155));
});

test('the demo content finishes what is missing after a half-finished run', function () {
    $this->seed(DemoContentSeeder::class);
    Reaction::query()->delete();
    Comment::query()->delete();

    $this->seed(DemoContentSeeder::class);

    expect(Reaction::count())->toBeGreaterThan(0)->and(Comment::count())->toBe(8);
});

test('fresh demo statuses are posted when the old ones have expired', function () {
    $this->seed([DemoContentSeeder::class, MoreDemoContentSeeder::class, DemoStatusesSeeder::class]);
    Status::query()->update(['expires_at' => now()->subMinute()]);

    $this->seed(DemoStatusesSeeder::class);

    expect(Status::active()->count())->toBe(6);
});

test('demo people cannot be logged into with a known password', function () {
    $this->seed(MoreDemoContentSeeder::class);

    $this->post('/login', ['email' => 'reem@example.com', 'password' => 'password'])->assertSessionHasErrors();
    $this->assertGuest();
});
