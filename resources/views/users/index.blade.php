@extends('Layout.app')

@section('title', __('People'))
@section('content')
    <div class="mx-auto max-w-3xl space-y-6 p-8">
        <h1 class="page-title">{{ __('People') }}</h1>

        <form method="GET" action="{{ route('users.index') }}" class="glass-panel p-3" role="search">
            <div class="flex gap-2">
                <label for="people-search" class="sr-only">{{ __('Search people') }}</label>
                <input id="people-search" type="search" name="q" value="{{ $search }}" class="panel-input"
                    placeholder="{{ __('Search by name or @username') }}">
                <button type="submit" class="panel-btn"><x-icon name="search" class="h-4 w-4" /> {{ __('Search') }}</button>
            </div>
            <p class="mt-1 text-xs text-ink-muted">{{ __('Type part of a name, e.g. Wajd or wajd.') }}</p>
        </form>

        <div class="grid gap-3 sm:grid-cols-2">
            @forelse ($people as $person)
                <x-user-card :user="$person">
                    <span class="ms-auto shrink-0 text-xs text-ink-muted">{{ trans_choice(':count quote|:count quotes', $person->quotes_count) }}</span>
                </x-user-card>
            @empty
                <p class="glass-panel text-ink-muted sm:col-span-2">{{ __('No one found.') }}</p>
            @endforelse
        </div>

        {{ $people->links() }}
    </div>
@endsection
