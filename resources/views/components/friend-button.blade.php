{{-- Add friend / Request sent / Accept / Friends + Message --}}
@props(['user', 'friendship' => null])

@auth
    @if (! auth()->user()->is($user))
        <div class="flex flex-wrap items-center gap-2">
            @if ($friendship === null)
                <form method="POST" action="{{ route('friends.store', $user) }}">
                    @csrf
                    <button type="submit" class="panel-btn" data-burst data-sound="like">
                        <x-icon name="user-plus" class="h-4 w-4" /> {{ $user->is_private ? __('Send friend request') : __('Add friend') }}
                    </button>
                </form>
            @elseif ($friendship->status === \App\Models\Friendship::Pending && $friendship->addressee_id === auth()->id())
                <form method="POST" action="{{ route('friends.accept', $friendship) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="panel-btn" data-burst data-sound="like">
                        <x-icon name="check" class="h-4 w-4" /> {{ __('Accept request') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('friends.destroy', $friendship) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="panel-btn-ghost" data-sound="close">{{ __('Decline') }}</button>
                </form>
            @elseif ($friendship->status === \App\Models\Friendship::Pending)
                <form method="POST" action="{{ route('friends.destroy', $friendship) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="panel-btn-ghost" data-sound="close">{{ __('Request sent · Cancel') }}</button>
                </form>
            @else
                <a href="{{ route('messages.show', $user) }}" class="panel-btn" data-sound="open">
                    <x-icon name="chat" class="h-4 w-4" /> {{ __('Message') }}
                </a>
                <form method="POST" action="{{ route('friends.destroy', $friendship) }}"
                    onsubmit="return confirm(@js(__('Remove this friend?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="panel-btn-ghost" data-sound="close">
                        <x-icon name="check" class="h-4 w-4" /> {{ __('Friends') }}
                    </button>
                </form>
            @endif
        </div>
    @endif
@else
    <a href="{{ route('login') }}" class="panel-btn"><x-icon name="user-plus" class="h-4 w-4" /> {{ __('Add friend') }}</a>
@endauth
