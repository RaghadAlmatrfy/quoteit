@extends('Layout.app')

@section('title', $user->name)
@section('content')
    <div class="mx-auto max-w-3xl space-y-6 p-8">

        {{-- Profile header --}}
        <section class="glass-panel">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <img src="{{ $user->avatarUrl() }}" alt="" class="h-24 w-24 rounded-full object-cover ring-4 ring-accent/60">
                <div class="min-w-0 flex-1">
                    <h1 class="flex flex-wrap items-center gap-2 text-2xl font-bold">
                        {{ $user->name }}
                        <span class="inline-flex items-center gap-1 rounded-full bg-tile px-2 py-0.5 text-xs font-semibold text-ink-muted">
                            <x-icon :name="$user->is_private ? 'lock' : 'globe'" class="h-3.5 w-3.5" />
                            {{ $user->is_private ? __('Private account') : __('Public account') }}
                        </span>
                    </h1>
                    <p class="text-sm text-ink-muted">{{ '@'.$user->username }}</p>
                    @if ($user->bio)
                        <p class="mt-2">{{ $user->bio }}</p>
                    @endif
                </div>

                <div class="shrink-0">
                    @if (auth()->user()?->is($user))
                        <a href="{{ route('profile.edit') }}" class="panel-btn"><x-icon name="edit" class="h-4 w-4" /> {{ __('Edit profile') }}</a>
                    @else
                        <x-friend-button :user="$user" :friendship="$friendship" />
                    @endif
                </div>
            </div>

            <dl class="mt-5 grid grid-cols-3 gap-3">
                <div class="stat-tile"><dt class="stat-label">{{ __('Quotes') }}</dt><dd class="stat-value">{{ $stats['quotes'] }}</dd></div>
                <div class="stat-tile"><dt class="stat-label">{{ __('Friends') }}</dt><dd class="stat-value">{{ $stats['friends'] }}</dd></div>
                <div class="stat-tile"><dt class="stat-label">{{ __('Likes received') }}</dt><dd class="stat-value">{{ $stats['likes'] }}</dd></div>
            </dl>

            {{-- Specialty and interests --}}
            <div class="mt-5 space-y-3 text-sm">
                @if ($user->specialty)
                    <p class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold">{{ __('Specialty') }}:</span>
                        <a href="{{ route('communities.show', $user->specialty->value) }}"
                            class="rounded-full px-3 py-1 text-xs font-semibold bg-{{ $user->specialty->color() }} {{ $user->specialty->isDark() ? 'text-white' : 'text-midnight-orchid' }}">
                            {{ $user->specialty->label() }}</a>
                    </p>
                @endif
                @if ($user->interestFields())
                    <p class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold">{{ __('Interests') }}:</span>
                        @foreach ($user->interestFields() as $interest)
                            <a href="{{ route('communities.show', $interest->value) }}"
                                class="rounded-full px-3 py-1 text-xs font-semibold bg-{{ $interest->color() }} {{ $interest->isDark() ? 'text-white' : 'text-midnight-orchid' }}">
                                {{ $interest->label() }}</a>
                        @endforeach
                    </p>
                @endif
            </div>
        </section>

        {{-- Their quotes --}}
        <section class="space-y-4">
            <h2 class="page-title">{{ auth()->user()?->is($user) ? __('My quotes') : __('Quotes by :name', ['name' => $user->name]) }}</h2>

            @if (! $canSeeQuotes)
                <div class="glass-panel flex items-center gap-3">
                    <x-icon name="lock" class="h-8 w-8 shrink-0 text-ink-muted" />
                    <div>
                        <p class="font-semibold">{{ __('This account is private') }}</p>
                        <p class="text-sm text-ink-muted">{{ __('Add :name as a friend to see their quotes.', ['name' => $user->name]) }}</p>
                    </div>
                </div>
            @else
                @forelse ($quotes as $quote)
                    <x-quote-card :quote="$quote" />
                @empty
                    <p class="text-gray-500">{{ __('No quotes yet.') }}</p>
                @endforelse

                {{ $quotes->links() }}
            @endif
        </section>
    </div>

@endsection
