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
     * Demo data: users, technical quotes, reactions, friendships, messages and statuses.
     * Every demo password is: password
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

        // [name, username, specialty, interests, private?, quotes]
        $people = [
            ['ولايف الطويرقي', 'walaif', 'ai', ['ai', 'data'], false, [
                'نموذج تعلم الآلة جيد بقدر جودة البيانات التي تعلّم منها.',
                'الشبكات العصبية تتعلم الأنماط بتعديل ملايين الأوزان.',
            ]],
            ['صالحة الزهراني', 'saleha', 'data', ['data', 'ai'], false, [
                'البيانات النظيفة تتفوق غالبًا على النموذج المعقّد.',
            ]],
            ['دانه الحربي', 'danah', 'cloud', ['cloud', 'software'], false, [
                'التوسع التلقائي يجعل التطبيق السحابي يكبر مع الطلب ويصغر لتوفير التكلفة.',
            ]],
            ['وجد الحربي', 'wajd', 'data', ['data', 'ai'], false, [
                'لوحة المعلومات الجيدة تجيب عن سؤال واحد بوضوح قبل أن تعرض عشرة أرقام.',
                'الجملة JOIN تربط صفوفًا من جدولين عن طريق عمود مشترك مثل المفتاح الأجنبي.',
            ]],
            ['مي المطرفي', 'mai', 'security', ['security', 'cloud'], false, [
                'معظم الاختراقات تبدأ بكلمة مرور ضعيفة أو مكررة. استخدم مدير كلمات مرور والتحقق الثنائي.',
            ]],
            ['اميرة المطرفي', 'amirah', 'software', ['software', 'ai'], false, [
                'الكود يُقرأ أكثر بكثير مما يُكتب، لذلك سمِّ الأشياء بوضوح.',
            ]],
            ['ريماس العتيبي', 'remas', 'ai', ['ai', 'software'], false, [
                'آلية الانتباه تسمح للنموذج بالتركيز على أهم الكلمات في الجملة.',
            ]],
            ['خالد العتيبي', 'khalid', 'security', ['security', 'software'], false, [
                'التشفير يمكن عكسه بالمفتاح الصحيح، أما الـ Hashing فاتجاه واحد فقط.',
            ]],
            ['زياد الهذلي', 'ziyad', 'cloud', ['cloud', 'security'], false, [
                'الحوسبة بدون خادم لا تعني عدم وجود خوادم، بل تعني أنك لا تديرها بنفسك.',
            ]],
            ['مشعل الخالدي', 'mishal', 'software', ['software', 'cloud'], false, [
                'نمط MVC يقسم التطبيق إلى نموذج للبيانات وواجهة للعرض ومتحكم للمنطق.',
            ]],
            ['جمانه الحربي', 'jumanah', 'data', ['data', 'cloud'], false, [
                'الفهارس تسرّع القراءة من قاعدة البيانات لكنها تبطّئ الكتابة قليلًا.',
            ]],
            ['غلا القرشي', 'ghala', 'ai', ['ai', 'data'], false, [
                'الإفراط في التدريب يعني أن النموذج حفظ البيانات بدل أن يفهم النمط.',
            ]],
            ['اسرار المالكي', 'asrar', 'security', ['security', 'data'], true, [
                'حسابي خاص: أشارك هنا ملاحظاتي عن اختبار الاختراق مع أصدقائي فقط.',
            ]],
            ['احمد المقاطي', 'ahmed', 'cloud', ['cloud', 'software'], false, [
                'الحاويات (Containers) تضمن أن التطبيق يعمل بنفس الطريقة على أي جهاز.',
            ]],
            ['ايمن القحطاني', 'ayman', 'software', ['software', 'security'], false, [
                'الاختبارات الآلية هي شبكة الأمان التي تسمح لك بتغيير الكود بثقة.',
            ]],
            ['نهى الثقفي', 'noha', 'data', ['data', 'ai'], false, [
                'المتوسط وحده قد يخدعك، انظر دائمًا إلى توزيع البيانات.',
            ]],
            ['محمد المحمدي', 'mohammed', 'ai', ['ai', 'cloud'], false, [
                'الذكاء الاصطناعي التوليدي يتنبأ بالكلمة التالية، ومن هنا تأتي قوته وأخطاؤه.',
            ]],
        ];

        $users = collect();

        foreach ($people as [$name, $username, $specialty, $interests, $isPrivate, $quotes]) {
            $user = User::factory()->create([
                'name' => $name,
                'username' => $username,
                'email' => $username.'@example.com',
                'specialty' => Field::from($specialty),
                'interests' => $interests,
                'is_private' => $isPrivate,
            ]);

            foreach ($quotes as $content) {
                $user->quotes()->create(['content' => $content, 'field' => $specialty, 'published_at' => now()->subMinutes(random_int(5, 3000))]);
            }

            $users->push($user);
        }

        // General tech quotes (the community for everyone interested in technology)
        $users->firstWhere('username', 'mishal')->quotes()->create(['content' => 'أفضل طريقة لتعلّم التقنية: ابنِ مشروعًا صغيرًا كل أسبوع.', 'field' => 'general', 'published_at' => now()->subMinutes(30)]);
        $users->firstWhere('username', 'noha')->quotes()->create(['content' => 'Git يحفظ تاريخ مشروعك كله، فلا تخف من التجربة.', 'field' => 'general', 'published_at' => now()->subMinutes(45)]);

        // My own quotes, a draft and a scheduled quote
        $raghad->quotes()->create(['content' => 'الـ API يقف بين الواجهة الأمامية والخلفية، ينقل الطلبات والردود.', 'field' => 'software', 'published_at' => now()->subHour()]);
        $raghad->quotes()->create(['content' => 'مسودة: شرح الفرق بين REST و GraphQL بطريقة بسيطة.', 'field' => 'software', 'status' => Quote::Draft]);
        $raghad->quotes()->create(['content' => 'مجدول: المفتاح الأساسي يحدد كل سجل بشكل فريد.', 'field' => 'data', 'published_at' => now()->addDay()]);

        // A styled quote
        $users->firstWhere('username', 'remas')->quotes()->create([
            'content' => 'البيانات هي النفط الجديد، لكنها تحتاج إلى تكرير قبل الاستخدام.',
            'field' => 'data', 'font' => 'amiri', 'font_size' => 'xl', 'is_bold' => true,
            'text_align' => 'center', 'bg_color' => '#ae96f2', 'published_at' => now()->subMinutes(2),
        ]);

        // Likes and dislikes
        $everyone = $users->push($raghad);
        foreach (Quote::where('status', Quote::Published)->get() as $quote) {
            foreach ($everyone->random(6) as $user) {
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

        // Temporary statuses (24 hours)
        $users->firstWhere('username', 'walaif')->statuses()->create(['body' => 'أذاكر الـ Transformers اليوم!', 'bg_color' => '#6f3dbf', 'expires_at' => now()->addHours(20)]);
        $users->firstWhere('username', 'walaif')->statuses()->create(['body' => 'نصيحة: اقرأ التوثيق أولًا.', 'bg_color' => '#4fc9b0', 'expires_at' => now()->addHours(22)]);
        $users->firstWhere('username', 'danah')->statuses()->create(['body' => 'أول تطبيق لي على السحابة', 'bg_color' => '#2c2133', 'expires_at' => now()->addHours(23)]);
        $wajd->statuses()->create(['body' => 'خلصت داشبورد Power BI جديدة', 'bg_color' => '#f2a54a', 'expires_at' => now()->addHours(12)]);
        $users->firstWhere('username', 'mai')->statuses()->create(['body' => 'حالة لأصدقائي فقط', 'bg_color' => '#2c2133', 'visibility' => 'friends', 'expires_at' => now()->addHours(10)]);

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
