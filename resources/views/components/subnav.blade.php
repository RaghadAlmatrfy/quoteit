{{--
    Main menu.
    - Computer: a vertical menu on the left, with a "+ New quote" button on top.
    - Phone: a bar at the bottom of the screen, with + in the middle.
--}}
@props(['page' => ''])

@php
    $pendingRequests = 0;
    $unreadMessages = 0;

    if (auth()->check()) {
        $pendingRequests = auth()->user()->receivedFriendRequests()->where('status', \App\Models\Friendship::Pending)->count();
        $unreadMessages = \App\Models\Message::where('receiver_id', auth()->id())->whereNull('read_at')->count();
    }

    $links = [
        ['route' => 'home', 'page' => 'Home', 'icon' => 'home', 'label' => __('Home')],
        ['route' => 'communities.index', 'page' => 'Communities', 'icon' => 'grid', 'label' => __('Communities')],
        ['route' => 'users.index', 'page' => 'People', 'icon' => 'search', 'label' => __('People')],
    ];

    if (auth()->check()) {
        $links[] = ['route' => 'friends.index', 'page' => 'Friends', 'icon' => 'users', 'label' => __('Friends'), 'badge' => $pendingRequests];
        $links[] = ['route' => 'messages.index', 'page' => 'Messages', 'icon' => 'chat', 'label' => __('Messages'), 'badge' => $unreadMessages];
        $links[] = ['route' => 'favorites.index', 'page' => 'Favorites', 'icon' => 'bookmark', 'label' => __('Favorites')];
        $links[] = ['route' => 'drafts.index', 'page' => 'Drafts', 'icon' => 'edit', 'label' => __('Drafts')];
    }

    // On the phone bar: the most used links, + in the middle
    $phoneLinks = collect($links)->whereIn('page', ['Home', 'Communities', 'Messages', 'Friends'])->values();
@endphp

@unless (in_array($page, ['Sign Up', 'Log In'], true))
    {{-- Computer: vertical menu on the left --}}
    <aside class="fixed top-6 left-4 z-40 hidden w-52 lg:block">
        <nav aria-label="{{ __('Main') }}" data-tour="subnav"
            class="flex flex-col gap-1 rounded-3xl border border-line bg-panel p-2 shadow-lg backdrop-blur-md">
            @auth
                <button type="button" popovertarget="quote-popup" data-sound="open" data-tour="compose"
                    class="mb-1 flex items-center justify-center gap-2 rounded-2xl bg-tuwaiq-orange px-3 py-2.5 text-sm font-bold text-tuwaiq-purple shadow transition hover:scale-[1.02]">
                    <x-icon name="plus" class="h-5 w-5" /> {{ __('New quote') }}
                </button>
            @endauth
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}" class="side-link" @if ($page === $link['page']) aria-current="page" @endif>
                    <x-icon :name="$link['icon']" class="h-5 w-5 shrink-0" />
                    <span>{{ $link['label'] }}</span>
                    @if (($link['badge'] ?? 0) > 0)
                        <span class="badge ms-auto">{{ $link['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
    </aside>

    {{-- Phone: bar at the bottom --}}
    <nav aria-label="{{ __('Main') }}" data-tour="subnav"
        class="fixed inset-x-0 bottom-0 z-40 flex items-end gap-1 border-t border-line bg-panel px-2 pt-1 pb-[max(0.5rem,env(safe-area-inset-bottom))] backdrop-blur-md lg:hidden">
        @foreach ($phoneLinks as $index => $link)
            @if ($index === 2)
                @auth
                    <button type="button" popovertarget="quote-popup" data-sound="open" data-tour="compose" aria-label="{{ __('New quote') }}"
                        class="-mt-6 mx-1 flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-tuwaiq-orange text-tuwaiq-purple shadow-xl ring-4 ring-[#10081F]">
                        <x-icon name="plus" class="h-7 w-7" />
                    </button>
                @endauth
            @endif
            <a href="{{ route($link['route']) }}" class="bottom-link" @if ($page === $link['page']) aria-current="page" @endif>
                <x-icon :name="$link['icon']" class="h-6 w-6" />
                <span>{{ $link['label'] }}</span>
                @if (($link['badge'] ?? 0) > 0)
                    <span class="badge absolute top-0 end-[22%]">{{ $link['badge'] }}</span>
                @endif
            </a>
        @endforeach
        @guest
            <a href="{{ route('login') }}" class="bottom-link"><x-icon name="user-plus" class="h-6 w-6" /><span>{{ __('Log In') }}</span></a>
        @endguest
    </nav>
@endunless
