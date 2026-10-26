@extends('Layout.app')

@section('title', __('Home'))
@section('content')

    <div class="mx-auto max-w-3xl space-y-8 p-8">

        <x-errors />

        {{-- Greeting --}}
        <header>
            <h1 class="text-2xl font-bold text-white">
                @auth
                    {{ __('Welcome, :name', ['name' => auth()->user()->name]) }}
                @else
                    {{ __('Welcome to Tuwaiq Quotes') }}
                @endauth
            </h1>
            <p class="mt-1 text-sm text-tuwaiq-text/70">{{ __('Share a piece of knowledge from the world of computing.') }}</p>
        </header>

        {{-- Temporary statuses (24 hours) --}}
        <x-statuses-strip :groups="$statusGroups" />

        {{-- My interests (likes, saves, copies, shares by field) --}}
        @if ($interests)
            <x-my-interests :interests="$interests" />
        @endif

        {{-- Quotes panel --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-bold text-white">{{ __('Quotes') }}</h2>

            <x-field-filter :active="$activeField" :base-url="route('home')" />

            @forelse ($quotes as $quote)
                <x-quote-card :quote="$quote" />
            @empty
                <p class="text-gray-500">{{ __('No quotes yet. Be the first to share one!') }}</p>
            @endforelse

            {{ $quotes->links() }}
        </section>

    </div>

@endsection
