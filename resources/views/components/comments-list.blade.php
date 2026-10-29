{{-- The comments of one quote + the form to add a comment --}}
@props(['quote', 'comments'])

<div class="space-y-3" data-comments-list data-count="{{ $comments->count() }}">
    @forelse ($comments as $comment)
        <div class="flex items-start gap-2">
            <a href="{{ route('users.show', $comment->user) }}" class="shrink-0">
                <img src="{{ $comment->user->avatarUrl() }}" alt="" class="h-8 w-8 rounded-full object-cover">
            </a>
            <div class="min-w-0 flex-1 rounded-2xl bg-black/10 px-3 py-2">
                <p class="flex flex-wrap items-center gap-x-2 text-xs">
                    <a href="{{ route('users.show', $comment->user) }}" class="font-semibold hover:underline">{{ $comment->user->name }}</a>
                    <span class="opacity-60">{{ $comment->created_at->diffForHumans() }}</span>
                </p>
                <p class="mt-0.5 text-base whitespace-pre-line" dir="auto" @if ($comment->fontFamily()) style="font-family: {{ $comment->fontFamily() }}" @endif>{{ $comment->body }}</p>
            </div>
            @if ($comment->canBeDeletedBy(auth()->user()))
                <form method="POST" action="{{ route('comments.destroy', $comment) }}" data-comment-form
                    data-confirm="{{ __('Delete this comment?') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-full p-1 opacity-60 hover:opacity-100" aria-label="{{ __('Delete') }}"
                        data-sound="close"><x-icon name="trash" class="h-4 w-4" /></button>
                </form>
            @endif
        </div>
    @empty
        <p class="text-sm opacity-70">{{ __('No comments yet. Be the first to comment!') }}</p>
    @endforelse

    @auth
        <form method="POST" action="{{ route('comments.store', $quote) }}" class="flex items-start gap-2" data-comment-form>
            @csrf
            <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="h-8 w-8 shrink-0 rounded-full object-cover">
            <div class="flex-1">
                <textarea name="body" rows="1" required maxlength="300" dir="auto"
                    class="w-full resize-none rounded-2xl border border-current/20 bg-white/80 px-3 py-2 text-sm text-tuwaiq-purple placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-tuwaiq-orange"
                    placeholder="{{ __('Write a comment…') }}"></textarea>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                    <label class="flex items-center gap-1">
                        <span class="opacity-80">{{ __('Font') }}</span>
                        <select name="font" data-comment-font aria-label="{{ __('Comment font') }}"
                            class="rounded-full border border-current/20 bg-white/80 px-2 py-0.5 text-xs text-tuwaiq-purple focus:outline-none focus:ring-2 focus:ring-tuwaiq-orange">
                            @foreach (\App\Support\QuoteStyle::Fonts as $key => $option)
                                <option value="{{ $key }}" style="font-family: {{ $option['family'] }}">{{ __($option['label']) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <span class="opacity-70">{{ __('Up to 300 characters.') }}</span>
                </div>
            </div>
            <button type="submit" class="rounded-full bg-tuwaiq-orange p-2 text-tuwaiq-purple shadow hover:opacity-90"
                aria-label="{{ __('Send') }}" data-sound="send" data-burst><x-icon name="send" class="h-4 w-4 rtl:-scale-x-100" /></button>
        </form>
    @else
        <p class="text-sm"><a href="{{ route('login') }}" class="font-semibold underline">{{ __('Log in') }}</a> {{ __('to add a comment.') }}</p>
    @endauth
</div>
