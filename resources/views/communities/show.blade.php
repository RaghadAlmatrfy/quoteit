@extends('Layout.app')

@section('title', $field?->label() ?? __('All fields'))
@section('content')
    <div class="mx-auto max-w-3xl space-y-6 p-8">
        <section class="glass-panel overflow-hidden p-0">
            <div class="h-3 {{ $field ? 'bg-'.$field->color() : 'bg-tuwaiq-purple' }}"></div>
            <div class="space-y-4 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold tracking-wide text-ink-muted uppercase">{{ __('Community') }}</p>
                        <h1 class="text-2xl font-bold">{{ $field?->label() ?? __('All fields') }}</h1>
                    </div>
                    <a href="{{ route('communities.index') }}" class="chip">{{ __('All communities') }}</a>
                </div>

                @if ($members->isNotEmpty())
                    <div>
                        <p class="mb-2 text-sm font-semibold">{{ __('Members') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($members as $member)
                                <a href="{{ route('users.show', $member) }}" title="{{ $member->name }}"
                                    class="flex items-center gap-2 rounded-full bg-tile py-1 ps-1 pe-3 text-sm hover:ring-2 hover:ring-accent">
                                    <img src="{{ $member->avatarUrl() }}" alt="" class="h-7 w-7 rounded-full object-cover">
                                    {{ $member->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <section class="space-y-4">
            @forelse ($quotes as $quote)
                <x-quote-card :quote="$quote" />
            @empty
                <p class="text-gray-500">{{ __('No quotes yet. Be the first to share one!') }}</p>
            @endforelse

            {{ $quotes->links() }}
        </section>
    </div>

@endsection
