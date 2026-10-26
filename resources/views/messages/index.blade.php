@extends('Layout.app')

@section('title', __('Messages'))
@section('content')
    <div class="mx-auto max-w-3xl space-y-4 p-8">
        <h1 class="page-title">{{ __('Messages') }}</h1>
        <p class="text-sm text-tuwaiq-text/70">{{ __('You can send private messages to your friends.') }}</p>

        @forelse ($conversations as $row)
            <a href="{{ route('messages.show', $row['friend']) }}"
                class="glass-panel flex items-center gap-3 p-4 transition hover:border-accent">
                <img src="{{ $row['friend']->avatarUrl() }}" alt="" class="h-12 w-12 rounded-full object-cover">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $row['friend']->name }}</p>
                    <p class="truncate text-sm text-ink-muted">
                        @if ($row['last'])
                            {{ $row['last']->sender_id === auth()->id() ? __('You').': ' : '' }}{{ $row['last']->body ?: __('[media]') }}
                        @else
                            {{ __('Say hello!') }}
                        @endif
                    </p>
                </div>
                @if ($row['unread'] > 0)
                    <span class="badge">{{ $row['unread'] }}</span>
                @endif
            </a>
        @empty
            <p class="glass-panel text-ink-muted">{{ __('Add friends first, then you can message them here.') }}</p>
        @endforelse
    </div>
@endsection
