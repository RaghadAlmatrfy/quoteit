<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'My Website') · {{ __('Tuwaiq Quotes') }}</title>

    {{-- New: apply saved theme + font size before the page is drawn (no flash) --}}
    <script>
        try {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light' || savedTheme === 'dark') document.documentElement.dataset.theme = savedTheme;
            const savedFont = localStorage.getItem('fontSize');
            if (['sm', 'md', 'lg', 'xl'].includes(savedFont)) document.documentElement.dataset.font = savedFont;
        } catch (error) {}
    </script>

    {{-- Fonts you can choose for a quote (Arabic + English) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Cairo:wght@400;700&family=Noto+Naskh+Arabic:wght@400;700&family=Reem+Kufi:wght@400;700&family=Tajawal:wght@400;700&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="flex min-h-screen flex-col bg-center bg-fixed bg-no-repeat pb-20 lg:pb-0"
    style="
        background-image: url('{{ asset('images/background.png') }}');
        background-size: cover;
    "
    data-auth="{{ auth()->check() ? 'true' : 'false' }}"
    @auth data-user-name="{{ auth()->user()->name }}" data-user-email="{{ auth()->user()->email }}" @endauth
    data-login-url="{{ route('login') }}"
    data-translate-url="{{ route('translate') }}"
    {{-- Your own click sounds: any .mp3 in public/sounds (click.mp3 is used for every button) --}}
    data-sound-files="{{ json_encode(collect(glob(public_path('sounds/*.mp3')) ?: [])->mapWithKeys(fn ($file) => [pathinfo($file, PATHINFO_FILENAME) => asset('sounds/'.basename($file))])) }}"
>
<x-navbar :page="$page ?? ''" />
<x-subnav :page="$page ?? ''" />

    {{-- Messages after an action (saved, deleted, ...) --}}
    @if (session('status'))
        <div class="mx-auto mt-4 w-full max-w-3xl px-8">
            <p class="glass-panel py-3 text-sm" role="status">{{ session('status') }}</p>
        </div>
    @endif

    <main class="flex-1 lg:pr-28 lg:pl-56 xl:pl-0">
        @yield('content')
    </main>

    {{-- New: the "+" (new quote) is available on every page --}}
    @unless (in_array($page ?? '', ['Sign Up', 'Log In'], true))
        <x-quote-composer :field="$composerField ?? null" />
    @endunless

<x-footer />

{{-- New: Welcome splash (shown once per visit) --}}
<div id="splash" class="fixed inset-0 z-[90] flex items-center justify-center bg-black/70 p-6 backdrop-blur-sm is-hidden"
    role="dialog" aria-modal="true" aria-labelledby="splash-title">
    <div class="splash-card w-full max-w-md rounded-3xl border border-line bg-panel p-8 text-center text-ink shadow-2xl">
        <img src="{{ asset('images/logo.png') }}" alt="" class="mx-auto h-16 w-auto">
        <h1 id="splash-title" class="mt-5 text-3xl font-bold">{{ __('Welcome to Tuwaiq Quotes') }}</h1>
        <p class="mt-2 text-ink-muted" data-splash-greeting
            data-greeting-back="{{ __('Welcome back, :name', ['name' => '__NAME__']) }}"
            data-greeting-new="{{ __('Share what you know about tech, one quote at a time.') }}">
            @auth
                {{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}
            @else
                {{ __('Share what you know about tech, one quote at a time.') }}
            @endauth
        </p>
        <button type="button" class="panel-btn mt-6" data-splash-close data-sound="open">{{ __('Continue') }}</button>
    </div>
</div>

{{-- New: after the splash, guests choose: log in or create an account --}}
@guest
    <div id="auth-choice" popover
        class="m-auto w-[90%] max-w-md rounded-3xl border border-line bg-panel p-0 text-ink shadow-2xl backdrop:bg-black/50 backdrop:backdrop-blur-sm">
        <div class="space-y-4 p-7 text-center">
            <h2 class="text-2xl font-bold" data-auth-choice-title
                data-title-back="{{ __('Good to see you again, :name', ['name' => '__NAME__']) }}">{{ __('Join the community') }}</h2>
            <p class="text-sm text-ink-muted">{{ __('Log in to like, save, share and post your own quotes.') }}</p>
            <div class="grid gap-3">
                <a href="{{ route('login') }}" class="panel-btn py-3 text-base" data-sound="open">{{ __('Log In') }}</a>
                <a href="{{ route('signup') }}" class="panel-btn-ghost py-3 text-base" data-sound="open">{{ __('Create an account') }}</a>
                <button type="button" popovertarget="auth-choice" popovertargetaction="hide"
                    class="text-sm text-ink-muted underline-offset-4 hover:underline">{{ __('Just browse for now') }}</button>
            </div>
        </div>
    </div>
@endguest

{{-- New: Share menu (filled by JavaScript for the quote you clicked) --}}
<div id="share-menu" popover class="share-menu" aria-labelledby="share-title">
    <div class="mb-4 flex items-center justify-between">
        <h2 id="share-title" class="text-lg font-bold">{{ __('Share this quote') }}</h2>
        <button type="button" popovertarget="share-menu" popovertargetaction="hide" aria-label="{{ __('Close') }}"
            class="rounded-full p-1 text-ink-muted hover:bg-tile hover:text-ink"><x-icon name="close" /></button>
    </div>
    <div class="grid grid-cols-4 gap-2">
        <a class="share-option" data-share-target="x" target="_blank" rel="noopener"><span class="app-logo" style="background:#000">
                <x-brand-icon name="x" class="h-5 w-5" /></span><span>X</span></a>
        <a class="share-option" data-share-target="whatsapp" target="_blank" rel="noopener"><span class="app-logo" style="background:linear-gradient(180deg,#5FFC7B,#28D146)">
                <x-brand-icon name="whatsapp" class="h-7 w-7" /></span><span>WhatsApp</span></a>
        <a class="share-option" data-share-target="telegram" target="_blank" rel="noopener"><span class="app-logo" style="background:#fff">
                <x-brand-icon name="telegram" class="h-9 w-9 text-[#2AABEE]" /></span><span>Telegram</span></a>
        <a class="share-option" data-share-target="linkedin" target="_blank" rel="noopener"><span class="app-logo" style="background:#0A66C2">
                <x-brand-icon name="linkedin" class="h-6 w-6" /></span><span>LinkedIn</span></a>
        <a class="share-option" data-share-target="facebook" target="_blank" rel="noopener"><span class="app-logo" style="background:#0866FF">
                <x-brand-icon name="facebook" class="h-7 w-7 translate-y-1" /></span><span>Facebook</span></a>
        <a class="share-option" data-share-target="tiktok" target="_blank" rel="noopener"><span class="app-logo tiktok-logo" style="background:#000">
                <x-brand-icon name="tiktok" class="h-6 w-6 text-[#25F4EE]" />
                <x-brand-icon name="tiktok" class="h-6 w-6 text-[#FE2C55]" />
                <x-brand-icon name="tiktok" class="h-6 w-6 text-white" /></span><span>TikTok</span></a>
        <a class="share-option" data-share-target="snapchat" target="_blank" rel="noopener"><span class="app-logo snap-logo" style="background:#FFFC00">
                <x-brand-icon name="snapchat" class="h-7 w-7" /></span><span>Snapchat</span></a>
        <a class="share-option" data-share-target="email"><span class="app-logo" style="background:linear-gradient(180deg,#1E9BFF,#0A60E0)">
                <x-brand-icon name="email" class="h-6 w-6" /></span><span>{{ __('Email') }}</span></a>
        <button type="button" class="share-option" data-share-target="copy"><span style="background:#432a80">
                <x-icon name="link" /></span><span>{{ __('Copy link') }}</span></button>
        <button type="button" class="share-option" data-share-target="native" hidden><span style="background:#53398f">
                <x-icon name="device" /></span><span>{{ __('More') }}</span></button>
    </div>
</div>

@auth
    <x-notifications-panel />
@endauth

{{-- New: choose the click sound --}}
<div id="sound-menu" popover class="m-0 w-64 rounded-2xl border border-line bg-panel p-2 text-ink shadow-2xl">
    <p class="px-2 py-1 text-sm font-bold">{{ __('Click sound') }}</p>
    <div class="grid gap-1" role="radiogroup">
        @foreach (['star' => 'Star twinkle', 'bubble' => 'Bubble', 'soft' => 'Soft tap', 'chime' => 'Chime', 'piano' => 'Piano note', 'custom' => 'My music file', 'off' => 'No sound'] as $key => $label)
            <label class="flex items-center gap-2 rounded-xl px-2 py-2 text-sm hover:bg-tile has-checked:bg-accent has-checked:text-on-accent"
                @if ($key === 'custom') data-sound-custom-option hidden @endif>
                <input type="radio" name="sound-style" value="{{ $key }}" class="sr-only" data-sound-style>
                {{ __($label) }}
            </label>
        @endforeach
    </div>
</div>

{{-- Small message at the bottom ("Link copied") --}}
<div id="toast" role="status"
    class="pointer-events-none fixed bottom-28 left-1/2 z-[95] -translate-x-1/2 rounded-full border border-line bg-panel px-5 py-2 text-sm font-semibold text-ink opacity-0 shadow-lg transition-opacity duration-300">
</div>

@auth
    {{-- New: welcome tour (main tools only). Shown once after the first log in; "?" shows it again --}}
    <script type="application/json" id="tour-steps"
        data-auto-start="{{ auth()->user()->tour_completed_at === null && ($page ?? '') === 'Home' ? 'true' : 'false' }}"
        data-complete-url="{{ route('tour.complete') }}">
        {!! json_encode([
            ['target' => 'compose', 'title' => __('Add a post'), 'text' => __('Tap + to write a tech quote. Choose the font, size, bold, italic, alignment and background, then publish, save a draft, or schedule it.')],
            ['target' => 'statuses', 'title' => __('Statuses'), 'text' => __('Share a quick status. Everyone sees it for 10 seconds, and it disappears after 24 hours.')],
            ['target' => 'actions', 'title' => __('Interact'), 'text' => __('Like, dislike, save to favorites, copy the link, or share a quote on other apps.')],
            ['target' => 'subnav', 'title' => __('Get around'), 'text' => __('Communities for every field, people, friends, private messages, favorites and your drafts.')],
            ['target' => 'notifications', 'title' => __('Notifications'), 'text' => __('See who liked or saved your quotes, sent you a friend request, or messaged you.')],
            ['target' => 'tools', 'title' => __('Display'), 'text' => __('Make the text bigger or smaller, switch light / dark, change the language, and turn sounds on or off.')],
            ['target' => 'profile', 'title' => __('Your profile'), 'text' => __('Edit your photo, bio, specialty and interests, and make your account private or public.')],
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>

    <div id="tour" class="fixed inset-0 z-[97] hidden" aria-live="polite">
        <div class="tour-spotlight" data-tour-spotlight></div>
        <div class="tour-card" role="dialog" aria-modal="true" aria-labelledby="tour-title" data-tour-card>
            <p class="text-xs font-semibold text-ink-muted" data-tour-count></p>
            <h2 id="tour-title" class="mt-1 text-lg font-bold" data-tour-title></h2>
            <p class="mt-1 text-sm" data-tour-text></p>
            <div class="mt-4 flex items-center gap-2">
                <button type="button" class="text-sm text-ink-muted underline-offset-4 hover:underline" data-tour-skip data-sound="close">{{ __('Skip') }}</button>
                <button type="button" class="panel-btn-ghost ms-auto px-3 py-1.5" data-tour-back data-sound="tap">{{ __('Back') }}</button>
                <button type="button" class="panel-btn px-4 py-1.5" data-tour-next data-sound="tap"
                    data-label-next="{{ __('Next') }}" data-label-done="{{ __('Done') }}">{{ __('Next') }}</button>
            </div>
        </div>
    </div>
@endauth

{{-- Texts used by JavaScript, in the current language --}}
<script type="application/json" id="i18n">
    {!! json_encode([
        'copied' => __('Link copied'),
        'copyFailed' => __('Could not copy'),
        'tiktok' => __('Link copied. Paste it in TikTok.'),
        'loginFirst' => __('Please log in first'),
        'saved' => __('Saved to favorites'),
        'removed' => __('Removed from favorites'),
        'notSaved' => __('Could not save. Check your connection and try again.'),
        'translateFailed' => __('Translation is not available right now.'),
    ], JSON_UNESCAPED_UNICODE) !!}
</script>

</body>

</html>
