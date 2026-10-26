{{-- New: notifications bell — people who interacted with me --}}
@php
    $me = auth()->user();
    ['notifications' => $notifications, 'unreadCount' => $unreadCount, 'actors' => $actors] = \App\Support\NotificationFeed::for($me);

    $texts = [
        'liked' => __(':name liked your quote'),
        'saved' => __(':name saved your quote'),
        'friend_request' => __(':name sent you a friend request'),
        'friend_accepted' => __(':name accepted your friend request'),
        'friend_added' => __(':name added you as a friend'),
        'message' => __(':name sent you a message'),
        'status_liked' => __(':name liked your status'),
        'commented' => __(':name commented on your quote'),
    ];
@endphp

<div id="notifications-panel" popover
    class="m-0 mt-2 max-h-[70vh] w-[min(24rem,92vw)] overflow-y-auto rounded-2xl border border-line bg-panel p-2 text-ink shadow-2xl"
 data-anchored-to="notifications-button">
    <div class="flex items-center justify-between px-2 py-1">
        <h2 class="font-bold">{{ __('Notifications') }}</h2>
    </div>
    <ul class="divide-y divide-line">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data;
                $actor = $actors->get($data['actor_id'] ?? 0);
                $link = match ($data['kind'] ?? '') {
                    'liked', 'saved', 'commented' => isset($data['quote_id']) ? route('quotes.show', $data['quote_id']) : route('home'),
                    'friend_request' => route('friends.index'),
                    'message' => $actor ? route('messages.show', $actor) : route('messages.index'),
                    default => $actor ? route('users.show', $actor) : route('home'),
                };
            @endphp
            <li>
                <a href="{{ $link }}" class="flex items-start gap-3 rounded-xl p-2 hover:bg-tile {{ $notification->read_at ? '' : 'bg-tile/60' }}">
                    <img src="{{ $actor?->avatarUrl() ?? asset('images/profile.jpeg') }}" alt="" class="h-9 w-9 shrink-0 rounded-full object-cover">
                    <span class="min-w-0 text-sm">
                        <span class="block">{{ str_replace(':name', $data['actor_name'] ?? '', $texts[$data['kind'] ?? ''] ?? '') }}</span>
                        @if (! empty($data['excerpt']))
                            <span class="block truncate text-xs text-ink-muted" dir="auto">“{{ $data['excerpt'] }}”</span>
                        @endif
                        <span class="block text-xs text-ink-muted">{{ $notification->created_at->diffForHumans() }}</span>
                    </span>
                    @unless ($notification->read_at)
                        <span class="ms-auto mt-1 h-2 w-2 shrink-0 rounded-full bg-tuwaiq-orange" aria-label="{{ __('New') }}"></span>
                    @endunless
                </a>
            </li>
        @empty
            <li class="p-4 text-center text-sm text-ink-muted">{{ __('No notifications yet. When people like or save your quotes, you will see them here.') }}</li>
        @endforelse
    </ul>
</div>
