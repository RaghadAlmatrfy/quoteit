@extends('Layout.app')

@section('title', $friend->name)
@section('content')
    <div class="mx-auto max-w-3xl space-y-4 p-8">
        <div class="glass-panel flex items-center gap-3 p-4">
            <a href="{{ route('messages.index') }}" class="chip">←</a>
            <a href="{{ route('users.show', $friend) }}" class="flex items-center gap-3">
                <img src="{{ $friend->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                <span class="font-semibold">{{ $friend->name }}</span>
            </a>
        </div>

        {{-- The chat --}}
        <div id="chat" class="glass-panel max-h-[55vh] space-y-3 overflow-y-auto" data-chat
            data-refresh-url="{{ route('messages.show', $friend) }}">
            @forelse ($messages as $message)
                @php($mine = $message->sender_id === auth()->id())
                <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2 shadow {{ $mine ? 'bg-tuwaiq-purple text-white' : 'bg-white text-tuwaiq-purple' }}">
                        @if ($message->media_path)
                            @if ($message->media_type === 'video')
                                <video src="{{ $message->mediaUrl() }}" controls class="mb-2 max-h-64 rounded-lg"></video>
                            @else
                                <img src="{{ $message->mediaUrl() }}" alt="" class="mb-2 max-h-64 rounded-lg">
                            @endif
                        @endif
                        @if ($message->body)
                            <p class="whitespace-pre-line">{{ $message->body }}</p>
                        @endif
                        <p class="mt-1 text-end text-[0.7rem] opacity-60">{{ $message->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-sm text-ink-muted">{{ __('No messages yet. Say hello!') }}</p>
            @endforelse
        </div>

        <x-errors />

        <form method="POST" action="{{ route('messages.store', $friend) }}" enctype="multipart/form-data"
            class="glass-panel flex items-end gap-2 p-3">
            @csrf
            <label class="panel-btn-ghost cursor-pointer px-3" title="{{ __('Attach an image or video') }}">
                <x-icon name="photo" class="h-5 w-5" />
                <input type="file" name="media" accept="image/*,video/*" class="sr-only" data-media-input="message-preview">
            </label>
            <div class="flex-1">
                <div id="message-preview" class="mb-2 hidden max-w-40 overflow-hidden rounded-lg"></div>
                <textarea name="body" rows="1" maxlength="1000" class="panel-input resize-none"
                    placeholder="{{ __('Write a message…') }}">{{ old('body') }}</textarea>
                <p class="mt-1 text-xs text-ink-muted">{{ __('Up to 1000 characters, or attach an image / video.') }}</p>
            </div>
            <button type="submit" class="panel-btn px-3" data-sound="send" aria-label="{{ __('Send') }}"><x-icon name="send" class="h-5 w-5 rtl:-scale-x-100" /></button>
        </form>
    </div>
@endsection
