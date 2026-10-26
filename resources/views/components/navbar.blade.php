@props(['page' => ''])

{{--
    Top bar.
    - Computer: a vertical bar on the RIGHT side (logo, name, photo, tools, log out).
    - Phone: the camp's horizontal bar at the top (it fits better on a small screen).
--}}

{{-- Phone (and Log In / Sign Up pages): horizontal bar on top --}}
<nav class="relative sticky top-0 z-50 border-b border-white/30 bg-[#10081F] shadow-sm backdrop-blur-md {{ in_array($page, ['Sign Up', 'Log In'], true) ? '' : 'lg:hidden' }}">
    <a href="{{ route('home') }}" class="site-name">QuoteIt</a>
    <ul class="mx-auto flex max-w-7xl flex-wrap items-center gap-3 px-4 py-1 sm:gap-6">

        @if (($page ?? '') === 'Sign Up')
        <li><a href="{{ route('login') }}" class="btn-primary">{{ __('Log In') }}</a></li>
        @elseif(($page ?? '') === 'Log In')
        <li><a href="{{ route('signup') }}" class="btn-primary">{{ __('Sign Up') }}</a></li>
        @else
        @auth
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-primary max-sm:px-3 max-sm:py-2 max-sm:text-sm" data-sound="close">{{ __('Log Out') }}</button>
            </form>
        </li>
        <li>
            <a href="{{ route('profile') }}" data-tour="profile"
                class="block rounded-full focus:outline-none focus-visible:ring-4 focus-visible:ring-tuwaiq-purple/30">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ __('Profile') }}"
                    class="h-10 w-10 rounded-full object-cover ring-2 ring-tuwaiq-purple ring-offset-2 transition hover:scale-105">
            </a>
        </li>
        <li class="hidden md:block">
            <p class="text-white">{{ auth()->user()->name }} </p>
        </li>
        @endauth
        @guest
        <li><a href="{{ route('login') }}" class="btn-primary">{{ __('Log In') }}</a></li>
        @endguest
        @endif

        <li class="ms-auto"><x-display-tools /></li>
        <li><img src="{{ asset('images/logo.png') }}" class=" h-12 w-auto sm:h-16" alt="Logo"></li>
    </ul>
</nav>

{{-- Computer: vertical bar on the right --}}
@unless (in_array($page, ['Sign Up', 'Log In'], true))
    <aside class="tool-rail fixed top-0 right-0 z-50 hidden h-screen w-24 flex-col items-center gap-4 overflow-y-auto border-l border-white/20 bg-[#10081F]/95 py-4 shadow-xl backdrop-blur-md lg:flex"
        aria-label="{{ __('Account and display') }}">
        <img src="{{ asset('images/logo.png') }}" class="w-20 rounded-lg" alt="Logo">
        <a href="{{ route('home') }}" class="site-name-rail" aria-label="QuoteIt">QuoteIt</a>

        @auth
            <a href="{{ route('profile') }}" data-tour="profile" class="flex flex-col items-center gap-1 text-center"
                data-tip="{{ __('My profile') }}">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ __('Profile') }}"
                    class="h-12 w-12 rounded-full object-cover ring-2 ring-tuwaiq-purple ring-offset-2 ring-offset-[#10081F] transition hover:scale-105">
                <span class="line-clamp-2 w-20 text-xs text-white">{{ auth()->user()->name }}</span>
            </a>
        @endauth

        <x-display-tools vertical />

        <div class="mt-auto">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex flex-col items-center gap-1 rounded-2xl bg-tuwaiq-purple px-3 py-2 text-xs font-medium text-white shadow hover:bg-tuwaiq-purple-light"
                        data-sound="close" data-tip="{{ __('Log Out') }}">
                        <x-icon name="logout" class="h-5 w-5" /> {{ __('Log Out') }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 rounded-2xl bg-tuwaiq-purple px-3 py-2 text-xs font-medium text-white shadow">
                    <x-icon name="user-plus" class="h-5 w-5" /> {{ __('Log In') }}
                </a>
            @endauth
        </div>
    </aside>
@endunless
