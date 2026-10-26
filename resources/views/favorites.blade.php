@extends('Layout.app')

@section('title', __('Favorites'))
@section('content')
    <div class="mx-auto max-w-3xl space-y-4 p-8">
        <h1 class="page-title">{{ __('My favorites') }}</h1>

        @forelse ($quotes as $quote)
            <x-quote-card :quote="$quote" />
        @empty
            <p class="glass-panel text-ink-muted">{{ __('You have not saved any quotes yet. Tap “Save” on a quote to keep it here.') }}</p>
        @endforelse

        {{ $quotes->links() }}
    </div>
@endsection
