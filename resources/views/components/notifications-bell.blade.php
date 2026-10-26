{{-- New: notifications bell — people who interacted with me --}}
@php
    $me = auth()->user();
    ['notifications' => $notifications, 'unreadCount' => $unreadCount, 'actors' => $actors] = \App\Support\NotificationFeed::for($me);

    $texts = [
        'liked' => __(':name liked your quote'),
        'saved' => __(':name saved your quote'),
        'friend_request' => __(':name sent you a friend request'),
        'friend_accepted' => __(':name accepted your friend request'),
        'message' => __(':name sent you a message'),
        'status_liked' => __(':name liked your status'),
    ];
@endphp

<button type="button" class="tool-btn relative" popovertarget="notifications-panel" data-notifications-button
    data-read-url="{{ route('notifications.read') }}" data-unread="{{ $unreadCount }}" data-tour="notifications"
    aria-label="{{ __('Notifications') }}" data-tip="{{ __('Notifications') }}" data-sound="open">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
    </svg>
    @if ($unreadCount > 0)
        <span class="badge absolute -end-1.5 -top-1.5" data-notifications-count>{{ $unreadCount }}</span>
    @endif
</button>

