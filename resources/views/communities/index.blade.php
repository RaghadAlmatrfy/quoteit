@extends('Layout.app')

@section('title', __('Communities'))
@section('content')
    <div class="mx-auto max-w-3xl space-y-6 p-8">
        <div>
            <h1 class="page-title">{{ __('Communities') }}</h1>
            <p class="mt-1 text-sm text-tuwaiq-text/70">{{ __('One community for each field, and one for everyone.') }}</p>
        </div>

        {{-- The community for all fields --}}
        <a href="{{ route('communities.show', 'all') }}" class="glass-panel block transition hover:-translate-y-0.5 hover:border-accent">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-tuwaiq-purple text-white"><x-icon name="globe" class="h-7 w-7" /></span>
                <div>
                    <h2 class="text-xl font-bold">{{ __('All fields') }}</h2>
                    <p class="text-sm text-ink-muted">{{ __(':quotes quotes · :members members', ['quotes' => $allQuotes, 'members' => $allMembers]) }}</p>
                </div>
            </div>
        </a>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($communities as $community)
                <a href="{{ route('communities.show', $community['field']->value) }}"
                    class="glass-panel block overflow-hidden p-0 transition hover:-translate-y-0.5 hover:border-accent">
                    <div class="h-2 bg-{{ $community['field']->color() }}"></div>
                    <div class="p-5">
                        <h2 class="text-lg font-bold">{{ $community['field']->label() }}</h2>
                        <p class="text-sm text-ink-muted">{{ __(':quotes quotes · :members members', ['quotes' => $community['quotes'], 'members' => $community['members']]) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
