<?php

namespace Database\Seeders;

use App\Enums\Field;
use App\Models\Comment;
use App\Models\Friendship;
use App\Models\Quote;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo people with technical quotes, comments, likes, friendships and statuses,
 * so the site does not look empty.
 *
 * SAFE FOR THE LIVE SITE: every demo account gets a long random password that nobody knows,
 * so nobody can log in as these people. Running it twice does nothing the second time.
 *
 *   php artisan db:seed --class=DemoContentSeeder --force
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'walaif@example.com')->exists()) {
            return; // already added
        }

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
            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $username.'@example.com',
                'password' => Str::random(40),
                'specialty' => Field::from($specialty),
                'interests' => $interests,
                'is_private' => $isPrivate,
            ]);

            foreach ($quotes as $content) {
                $user->quotes()->create(['content' => $content, 'field' => $specialty, 'published_at' => now()->subMinutes(random_int(60, 3000))]);
            }

            $users->put($username, $user);
        }

        // General tech quotes (the community for everyone interested in technology)
        $users['mishal']->quotes()->create(['content' => 'أفضل طريقة لتعلّم التقنية: ابنِ مشروعًا صغيرًا كل أسبوع.', 'field' => 'general', 'published_at' => now()->subMinutes(30)]);
        $users['noha']->quotes()->create(['content' => 'Git يحفظ تاريخ مشروعك كله، فلا تخف من التجربة.', 'field' => 'general', 'published_at' => now()->subMinutes(45)]);

        // A styled quote
        $users['remas']->quotes()->create([
            'content' => 'البيانات هي النفط الجديد، لكنها تحتاج إلى تكرير قبل الاستخدام.',
            'field' => 'data', 'font' => 'amiri', 'font_size' => 'xl', 'is_bold' => true,
            'text_align' => 'center', 'bg_color' => '#ae96f2', 'published_at' => now()->subMinutes(2),
        ]);

        // Likes and dislikes between them
        foreach (Quote::whereIn('user_id', $users->pluck('id'))->get() as $quote) {
            foreach ($users->random(5) as $user) {
                if ($user->id !== $quote->user_id) {
                    Reaction::firstOrCreate(
                        ['user_id' => $user->id, 'quote_id' => $quote->id],
                        ['type' => fake()->boolean(88) ? Reaction::Like : Reaction::Dislike],
                    );
                }
            }
        }

        // Comments
        $comments = [
            ['walaif', 'wajd', 'صحيح، وجودة البيانات تفرق كثير في النتائج.'],
            ['walaif', 'ghala', 'شكرًا على الشرح الواضح!'],
            ['saleha', 'noha', 'أتفق، التنظيف ياخذ أغلب الوقت.'],
            ['danah', 'ziyad', 'جربت التوسع التلقائي في مشروعي ووفّر علي كثير.'],
            ['mai', 'khalid', 'وأضيفي: لا تستخدمين نفس كلمة المرور في أكثر من موقع.'],
            ['amirah', 'ayman', 'أهم نصيحة في البرمجة 👏'],
            ['mishal', 'jumanah', 'بديت أطبقها، شكرًا!'],
            ['mohammed', 'remas', 'عشان كذا لازم نتأكد من المعلومة دائمًا.'],
        ];
        foreach ($comments as [$author, $commenter, $body]) {
            $quote = $users[$author]->quotes()->first();
            Comment::create(['quote_id' => $quote->id, 'user_id' => $users[$commenter]->id, 'body' => $body]);
        }

        // Some friendships between them
        foreach ([['wajd', 'mai'], ['wajd', 'amirah'], ['walaif', 'ghala'], ['khalid', 'ziyad'], ['asrar', 'mai'], ['noha', 'jumanah']] as [$first, $second]) {
            Friendship::create(['requester_id' => $users[$first]->id, 'addressee_id' => $users[$second]->id, 'status' => Friendship::Accepted]);
        }

        // Temporary statuses (24 hours)
        $users['walaif']->statuses()->create(['body' => 'أذاكر الـ Transformers اليوم!', 'bg_color' => '#6f3dbf', 'expires_at' => now()->addHours(20)]);
        $users['walaif']->statuses()->create(['body' => 'نصيحة: اقرأ التوثيق أولًا.', 'bg_color' => '#4fc9b0', 'expires_at' => now()->addHours(22)]);
        $users['danah']->statuses()->create(['body' => 'أول تطبيق لي على السحابة', 'bg_color' => '#2c2133', 'expires_at' => now()->addHours(23)]);
        $users['wajd']->statuses()->create(['body' => 'خلصت داشبورد Power BI جديدة', 'bg_color' => '#f2a54a', 'expires_at' => now()->addHours(12)]);
        $users['mai']->statuses()->create(['body' => 'حالة لأصدقائي فقط', 'bg_color' => '#2c2133', 'visibility' => 'friends', 'expires_at' => now()->addHours(10)]);
    }
}
