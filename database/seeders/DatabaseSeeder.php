<?php

namespace Database\Seeders;

use App\Enums\Field;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\Quote;
use App\Models\QuoteShare;
use App\Models\Reaction;
use App\Models\User;
use App\Notifications\InteractionNotification;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Local demo data. Log in with test@example.com / password (Raghad).
     * The other demo people come from DemoContentSeeder (random passwords).
     */
    public function run(): void
    {
        // The main account you log in with
        $raghad = User::factory()->create([
            'name' => 'رغد المطرفي',
            'username' => 'raghad',
            'email' => 'test@example.com',
            'bio' => 'مطوّرة مشروع اقتباسات طويق.',
            'specialty' => Field::Software,
            'interests' => ['general', 'software', 'data', 'ai'],
        ]);

        // The 17 demo people (with quotes, comments, likes, statuses)
        $this->call(DemoContentSeeder::class);
        $users = User::whereKeyNot($raghad->id)->get();

        // My own quotes, a draft and a scheduled quote
        $raghad->quotes()->create(['content' => 'الـ API يقف بين الواجهة الأمامية والخلفية، ينقل الطلبات والردود.', 'field' => 'software', 'published_at' => now()->subHour()]);
        $raghad->quotes()->create(['content' => 'مسودة: شرح الفرق بين REST و GraphQL بطريقة بسيطة.', 'field' => 'software', 'status' => Quote::Draft]);
        $raghad->quotes()->create(['content' => 'مجدول: المفتاح الأساسي يحدد كل سجل بشكل فريد.', 'field' => 'data', 'published_at' => now()->addDay()]);

        // Likes on Raghad's quotes, and Raghad's likes on others
        foreach (Quote::where('status', Quote::Published)->get() as $quote) {
            foreach ($users->push($raghad)->random(5) as $user) {
                if ($user->id !== $quote->user_id) {
                    Reaction::firstOrCreate(
                        ['user_id' => $user->id, 'quote_id' => $quote->id],
                        ['type' => fake()->boolean(88) ? Reaction::Like : Reaction::Dislike],
                    );
                }
            }
        }

        // Friends with Raghad, plus a pending request
        foreach (['wajd', 'mai', 'amirah', 'walaif', 'asrar'] as $username) {
            Friendship::create(['requester_id' => $raghad->id, 'addressee_id' => $users->firstWhere('username', $username)->id, 'status' => Friendship::Accepted]);
        }
        Friendship::create(['requester_id' => $users->firstWhere('username', 'khalid')->id, 'addressee_id' => $raghad->id, 'status' => Friendship::Pending]);

        // A conversation
        $wajd = $users->firstWhere('username', 'wajd');
        Message::create(['sender_id' => $wajd->id, 'receiver_id' => $raghad->id, 'body' => 'السلام عليكم! شفتي اقتباسي عن الـ JOIN؟']);
        Message::create(['sender_id' => $raghad->id, 'receiver_id' => $wajd->id, 'body' => 'وعليكم السلام، إيه واضح جدًا. شكرًا!']);

        $raghad->favorites()->attach(Quote::where('field', 'cloud')->first());

        // My own status, with people who saw it and liked it
        $myStatus = $raghad->statuses()->create(['body' => 'أشتغل على مشروع اقتباسات طويق', 'bg_color' => '#6f3dbf', 'expires_at' => now()->addHours(18)]);
        foreach (['wajd' => true, 'mai' => true, 'amirah' => false, 'walaif' => false] as $username => $liked) {
            $myStatus->views()->create(['user_id' => $users->firstWhere('username', $username)->id, 'liked' => $liked]);
        }

        // Some things Raghad copied / shared (for "My interests")
        foreach (Quote::where('field', 'ai')->take(2)->get() as $quote) {
            QuoteShare::create(['user_id' => $raghad->id, 'quote_id' => $quote->id, 'type' => QuoteShare::Share]);
        }

        // Notifications for Raghad
        $firstQuote = $raghad->quotes()->published()->first();
        InteractionNotification::send($raghad, InteractionNotification::Liked, $wajd, $firstQuote);
        InteractionNotification::send($raghad, InteractionNotification::Saved, $users->firstWhere('username', 'mai'), $firstQuote);
        InteractionNotification::send($raghad, InteractionNotification::FriendRequest, $users->firstWhere('username', 'khalid'));
        InteractionNotification::send($raghad, InteractionNotification::Message, $wajd);
    }
}
