<?php

namespace Database\Seeders;

use App\Enums\Field;
use App\Models\Comment;
use App\Models\Friendship;
use App\Models\Quote;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * More demo people, the Computer Science and Human-Computer Interaction fields,
 * and English quotes about today's tech issues (AI, privacy, security, accessibility...).
 *
 * Famous quotes are written with the name of the person who said them.
 * SAFE FOR THE LIVE SITE: random passwords nobody knows, and it only runs once.
 *
 *   php artisan db:seed --class=MoreDemoContentSeeder --force
 */
class MoreDemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'reem@example.com')->exists()) {
            return; // already added
        }

        // All or nothing: if something fails half way, nothing is saved and it can run again
        DB::transaction(fn () => $this->addPeople());
    }

    private function addPeople(): void
    {
        // [name, username, specialty, interests, private?, [[quote, field], ...]]
        $people = [
            ['ريم الشهري', 'reem', 'cs', ['cs', 'ai', 'software'], false, [
                ['Simplicity is prerequisite for reliability. — Edsger W. Dijkstra', 'cs'],
                ['الخوارزمية الجيدة تتفوق على الجهاز السريع؛ O(n log n) تبقى أسرع من O(n²) مهما كبر المعالج.', 'cs'],
            ]],
            ['فيصل الغامدي', 'faisal', 'ai', ['ai', 'security'], false, [
                ['AI is the new electricity. — Andrew Ng', 'ai'],
                ['A chatbot that sounds confident is not the same as a chatbot that is correct. Always check the source.', 'ai'],
            ]],
            ['لمى السبيعي', 'lama', 'hci', ['hci', 'software'], false, [
                ['Design is not just what it looks like and feels like. Design is how it works. — Steve Jobs', 'hci'],
                ['If your app is hard to use with a screen reader, it is not finished yet. Accessibility is a feature, not a favor.', 'hci'],
            ]],
            ['عبدالله الدوسري', 'abdullah', 'security', ['security', 'cloud'], false, [
                ['Security is a process, not a product. — Bruce Schneier', 'security'],
                ['Deepfake voice calls are the new phishing. If "your manager" asks for an urgent transfer, hang up and call back.', 'security'],
            ]],
            ['سارة الشمري', 'sarah', 'software', ['software', 'ai'], false, [
                ['Talk is cheap. Show me the code. — Linus Torvalds', 'software'],
                ['AI can write the code in seconds, but you still own every bug it ships. Read what you merge.', 'software'],
            ]],
            ['ياسر العمري', 'yasser', 'cs', ['cs', 'data'], false, [
                ['There are only two hard things in Computer Science: cache invalidation and naming things. — Phil Karlton', 'cs'],
                ['Quantum computers are not here to break your passwords tomorrow, but "harvest now, decrypt later" is real today.', 'security'],
            ]],
            ['هيا القحطاني', 'haya', 'hci', ['hci', 'data'], false, [
                ['Dark patterns trick users into clicking "accept all". Good UX makes the honest choice the easy one.', 'hci'],
                ['التصميم الجيد لا تلاحظه؛ تلاحظ فقط التصميم السيئ عندما تضيع فيه.', 'hci'],
            ]],
            ['تركي الزهراني', 'turki', 'cloud', ['cloud', 'ai'], false, [
                ['Training one large AI model can use as much electricity as over a hundred homes use in a year. Efficient code is green code.', 'cloud'],
                ['The cloud is just someone else\'s computer, so read the shared responsibility model.', 'cloud'],
            ]],
            ['نوف العنزي', 'nouf', 'data', ['data', 'ai'], true, [
                ['Your data is the product when the app is free. Read the permissions before you tap "Allow".', 'data'],
            ]],
            ['بدر الشهراني', 'badr', 'software', ['software', 'security'], false, [
                ['Any fool can write code that a computer can understand. Good programmers write code that humans can understand. — Martin Fowler', 'software'],
                ['One poisoned package in your dependencies can open your whole app. Pin versions and audit what you install.', 'security'],
            ]],
            ['شهد الحارثي', 'shahad', 'ai', ['ai', 'hci'], false, [
                ['Prompt injection is the SQL injection of the AI era: never trust text that the model reads from outside.', 'ai'],
                ['الذكاء الاصطناعي لا يفهم مثلنا؛ هو يتنبأ بالنمط الأكثر احتمالًا، فلا تسلّمه قراراتك المهمة وحده.', 'ai'],
            ]],
            ['عمر باوزير', 'omar', 'general', ['general', 'software'], false, [
                ['The best way to predict the future is to invent it. — Alan Kay', 'general'],
                ['Software is eating the world. — Marc Andreessen', 'general'],
            ]],
            ['رهف المالكي', 'rahaf', 'cs', ['cs', 'hci'], false, [
                ['Programs must be written for people to read, and only incidentally for machines to execute. — Harold Abelson', 'cs'],
                ['A computer science degree teaches you how to think, not which framework to use. Frameworks change; fundamentals stay.', 'cs'],
            ]],
            ['سلمان العسيري', 'salman', 'security', ['security', 'general'], false, [
                ['Passkeys are replacing passwords: nothing to remember, nothing to steal in a phishing page.', 'security'],
                ['تحديث جهازك ليس إزعاجًا؛ أغلب التحديثات تسد ثغرات يعرفها المهاجمون مسبقًا.', 'security'],
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

            foreach ($quotes as [$content, $field]) {
                $user->quotes()->create(['content' => $content, 'field' => $field, 'published_at' => now()->subMinutes(random_int(5, 2500))]);
            }

            $users->put($username, $user);
        }

        // Everyone who can react: the new people plus the earlier demo people
        $everyone = User::where('email', 'like', '%@example.com')->where('email', '!=', 'test@example.com')->get();

        foreach (Quote::whereIn('user_id', $users->pluck('id'))->get() as $quote) {
            foreach ($everyone->random(min(7, $everyone->count())) as $user) {
                if ($user->id !== $quote->user_id) {
                    Reaction::firstOrCreate(
                        ['user_id' => $user->id, 'quote_id' => $quote->id],
                        ['type' => random_int(1, 100) <= 90 ? Reaction::Like : Reaction::Dislike],
                    );
                }
            }
        }

        // Comments (in Arabic and English, like a real timeline)
        $comments = [
            ['faisal', 'sarah', 'This! I caught a made-up library name in a chatbot answer last week.'],
            ['faisal', 'shahad', 'أوافقك، لازم نرجع للمصدر دائمًا.'],
            ['lama', 'haya', 'جربت قارئ الشاشة على مشروعي وانصدمت كم زر بدون اسم 😅'],
            ['abdullah', 'salman', 'Happened to a company I know. A call-back rule would have saved them.'],
            ['sarah', 'badr', 'Code review is still the best tool we have.'],
            ['yasser', 'reem', 'وعشان كذا بدأ الانتقال للتشفير المقاوم للكم.'],
            ['haya', 'lama', 'The "reject all" button hidden in grey text 🙄'],
            ['turki', 'omar', 'Never thought about the energy side. Great point.'],
            ['shahad', 'faisal', 'Great comparison. Sanitize inputs, even for AI.'],
            ['rahaf', 'yasser', 'من أجمل الاقتباسات في علوم الحاسب.'],
            ['salman', 'abdullah', 'Moved my accounts to passkeys this month, much easier.'],
            ['reem', 'rahaf', 'Abelson always nails it.'],
        ];
        foreach ($comments as [$author, $commenter, $body]) {
            Comment::create([
                'quote_id' => $users[$author]->quotes()->first()->id,
                'user_id' => $users[$commenter]->id,
                'body' => $body,
            ]);
        }

        // Friendships, including some with the earlier demo people
        $pairs = [['reem', 'rahaf'], ['lama', 'haya'], ['faisal', 'shahad'], ['abdullah', 'salman'], ['sarah', 'badr'], ['nouf', 'haya']];
        foreach ($pairs as [$first, $second]) {
            Friendship::create(['requester_id' => $users[$first]->id, 'addressee_id' => $users[$second]->id, 'status' => Friendship::Accepted]);
        }
        foreach ([['wajd', 'reem'], ['mai', 'abdullah'], ['walaif', 'faisal']] as [$old, $new]) {
            $oldUser = User::where('username', $old)->first();
            if ($oldUser !== null) {
                Friendship::firstOrCreate(['requester_id' => $oldUser->id, 'addressee_id' => $users[$new]->id], ['status' => Friendship::Accepted]);
            }
        }
    }
}
