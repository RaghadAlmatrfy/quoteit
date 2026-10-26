@extends('Layout.app')

@section('title', __('Friends'))
@section('content')
    <div class="mx-auto max-w-3xl space-y-8 p-8">
        <div class="flex items-center justify-between gap-3">
            <h1 class="page-title">{{ __('Friends') }}</h1>
            <a href="{{ route('users.index') }}" class="chip"><x-icon name="user-plus" class="h-4 w-4" /> {{ __('Find people') }}</a>
        </div>

        @if ($incoming->isNotEmpty())
            <section class="space-y-3">
                <h2 class="text-lg font-bold text-white">{{ __('Friend requests') }}</h2>
                @foreach ($incoming as $request)
                    <div class="glass-panel flex flex-wrap items-center gap-3 p-4">
                        <a href="{{ route('users.show', $request->requester) }}" class="flex items-center gap-3">
                            <img src="{{ $request->requester->avatarUrl() }}" alt="" class="h-12 w-12 rounded-full object-cover">
                            <span class="font-semibold">{{ $request->requester->name }}</span>
                        </a>
                        <div class="ms-auto flex gap-2">
                            <form method="POST" action="{{ route('friends.accept', $request) }}">
                                @csrf
                                @method('PATCH')
                                <button class="panel-btn" data-burst data-sound="like"><x-icon name="check" class="h-4 w-4" /> {{ __('Accept') }}</button>
                            </form>
                            <form method="POST" action="{{ route('friends.destroy', $request) }}">
                                @csrf
                                @method('DELETE')
                                <button class="panel-btn-ghost" data-sound="close">{{ __('Decline') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="space-y-3">
            <h2 class="text-lg font-bold text-white">{{ __('My friends') }} ({{ $friends->count() }})</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @forelse ($friends as $friend)
                    <div class="glass-panel flex items-center gap-3 p-4">
                        <a href="{{ route('users.show', $friend) }}" class="flex min-w-0 items-center gap-3">
                            <img src="{{ $friend->avatarUrl() }}" alt="" class="h-12 w-12 rounded-full object-cover">
                            <span class="truncate font-semibold">{{ $friend->name }}</span>
                        </a>
                        <a href="{{ route('messages.show', $friend) }}" class="panel-btn ms-auto px-3" aria-label="{{ __('Message') }}"
                            title="{{ __('Message') }}"><x-icon name="chat" class="h-4 w-4" /></a>
                    </div>
                @empty
                    <p class="glass-panel text-ink-muted sm:col-span-2">{{ __('No friends yet. Visit someone’s page and tap “Add friend”.') }}</p>
                @endforelse
            </div>
        </section>

        @if ($outgoing->isNotEmpty())
            <section class="space-y-3">
                <h2 class="text-lg font-bold text-white">{{ __('Sent requests') }}</h2>
                @foreach ($outgoing as $request)
                    <div class="glass-panel flex items-center gap-3 p-4">
                        <a href="{{ route('users.show', $request->addressee) }}" class="flex items-center gap-3">
                            <img src="{{ $request->addressee->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                            <span class="font-semibold">{{ $request->addressee->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('friends.destroy', $request) }}" class="ms-auto">
                            @csrf
                            @method('DELETE')
                            <button class="panel-btn-ghost" data-sound="close">{{ __('Cancel') }}</button>
                        </form>
                    </div>
                @endforeach
            </section>
        @endif
    </div>
@endsection
