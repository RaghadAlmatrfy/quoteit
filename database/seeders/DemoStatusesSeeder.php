<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Five real-looking 24-hour statuses from the demo people, with viewers and likes.
 *
 * Statuses disappear after 24 hours, so the live site runs this every time the server starts:
 * when the demo statuses have expired, it posts fresh ones. When they are still showing, it does nothing.
 *
 *   php artisan db:seed --class=DemoStatusesSeeder --force
 */
class DemoStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $people = User::where('email', 'like', '%@example.com')
            ->where('email', '!=', 'test@example.com')
            ->get()
            ->keyBy('username');

        if ($people->isEmpty() || Status::active()->whereIn('user_id', $people->pluck('id'))->exists()) {
            return;
        }

        // [username, text, background, visibility, hours left]
        $statuses = [
            ['walaif', 'أذاكر الـ Transformers اليوم، ورقة Attention Is All You Need أخيرًا صارت واضحة', '#6f3dbf', Status::Public, 20],
            ['danah', 'أول تطبيق لي على السحابة اشتغل 🎉 التوسع التلقائي يشتغل بدون ما ألمسه', '#2c2133', Status::Public, 23],
            ['wajd', 'خلصت داشبورد Power BI جديدة، بكرة أرفعها على GitHub', '#f2a54a', Status::Public, 12],
            ['lama', 'Testing our app with a screen reader today. 14 buttons had no label 😅 fixing them now', '#4fc9b0', Status::Public, 18],
            ['faisal', 'Turned on passkeys for all my accounts. Goodbye passwords 🔐', '#432a80', Status::Public, 9],
            ['mai', 'حالة لأصدقائي فقط: مسابقة CTF الليلة، مين معي؟', '#2c2133', Status::FriendsOnly, 10],
        ];

        foreach ($statuses as [$username, $body, $background, $visibility, $hoursLeft]) {
            $author = $people->get($username);
            if ($author === null) {
                continue;
            }

            $status = $author->statuses()->create([
                'body' => $body,
                'bg_color' => $background,
                'visibility' => $visibility,
                'expires_at' => now()->addHours($hoursLeft),
            ]);

            // Some people saw it, and some of them liked it
            foreach ($people->except($username)->random(min(6, $people->count() - 1)) as $viewer) {
                $status->views()->create(['user_id' => $viewer->id, 'liked' => random_int(1, 100) <= 45]);
            }
        }
    }
}
