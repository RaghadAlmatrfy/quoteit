@extends('Layout.app')

@section('title', __('Drafts'))
@section('content')
    <div class="mx-auto max-w-3xl space-y-4 p-8">
        <div>
            <h1 class="page-title">{{ __('Drafts & scheduled') }}</h1>
            <p class="mt-1 text-sm text-tuwaiq-text/70">{{ __('Only you can see these. Publish them when you are ready.') }}</p>
        </div>

        @forelse ($quotes as $quote)
            <x-quote-card :quote="$quote" />
        @empty
            <p class="glass-panel text-ink-muted">{{ __('No drafts. In the “+” window, tap “Save draft” to keep a quote here.') }}</p>
        @endforelse

        {{ $quotes->links() }}
    </div>

@endsection
