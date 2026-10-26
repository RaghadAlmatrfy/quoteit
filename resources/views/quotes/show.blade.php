@extends('Layout.app')

@section('title', __('Quote'))
@section('content')
    <div class="mx-auto max-w-3xl space-y-6 p-8">
        <a href="{{ url()->previous() === url()->current() ? route('home') : url()->previous() }}" class="chip">← {{ __('Back') }}</a>
        <x-quote-card :quote="$quote" />
    </div>
@endsection
