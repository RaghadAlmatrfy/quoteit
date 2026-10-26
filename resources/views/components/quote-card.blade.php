{{--
    One quote card.
    - My quotes: purple with white text + Edit / Delete (camp design)
    - Other people's quotes: white with purple text, read-only (camp design)
    New: field color strip, author link, media, and the interactive buttons
    (like, dislike, save, copy link, share) colored by the quote's field.
--}}
@props(['quote', 'openComments' => false])

@php
    $isMine = auth()->check() && $quote->user_id === auth()->id();
    $isEditing = $isMine && (int) request('edit') === $quote->id;
    $field = $quote->field;
    $myReaction = auth()->check() ? $quote->reactions->first()?->type : null;
    $isSaved = auth()->check() && $quote->favoritedBy->isNotEmpty();
    $link = route('quotes.show', $quote);
    $fieldStyle = '--field-bg: var(--color-'.$field->color().'); --field-ink: '.($field->isDark() ? '#ffffff' : 'var(--color-midnight-orchid)').';';

    // The author's chosen look: background color, font, size, bold, italic, alignment
    // No chosen background? Each quote gets one of the palette colors, so the feed looks varied
    $cardBg = $quote->bg_color ?: \App\Support\QuoteStyle::cardColorFor($quote->id);
    $cardStyle = $fieldStyle.' background-color: '.$cardBg.'; color: '.\App\Support\QuoteStyle::textColorFor($cardBg).';';
    $cardColors = '';
    $fontFamily = \App\Support\QuoteStyle::family($quote->font);
    $textClasses = implode(' ', array_filter([
        \App\Support\QuoteStyle::sizeClass($quote->font_size),
        $quote->is_bold ? 'font-bold' : null,
        $quote->is_italic ? 'italic' : null,
        match ($quote->text_align) { 'right' => 'text-right', 'center' => 'text-center', 'left' => 'text-left', default => 'text-start' },
    ]));
@endphp

@if ($isEditing)
    {{-- Edit mode (my quote only) --}}
    <form method="POST" action="{{ route('quotes.update', $quote) }}" enctype="multipart/form-data"
        class="relative space-y-3 rounded-2xl bg-tuwaiq-purple p-5 ps-8 shadow" id="quote-{{ $quote->id }}">
        @csrf
        @method('PUT')
        <span class="field-strip bg-{{ $field->color() }}" aria-hidden="true"></span>

        <textarea name="content" dir="auto" rows="3" required minlength="3" maxlength="155" class="input"
            data-counter="edit-counter-{{ $quote->id }}" data-style-source autofocus>{{ old('content', $quote->content) }}</textarea>
        <div class="flex justify-between text-xs opacity-75">
            <p>{{ __('3–155 characters.') }}</p>
            <p id="edit-counter-{{ $quote->id }}"></p>
        </div>

        <x-quote-style-controls :quote="$quote" dark />

        {{-- Live preview --}}
        <div class="rounded-xl bg-white p-4 text-tuwaiq-purple" data-style-preview-box>
            <p dir="auto" data-style-preview>“{{ $quote->content }}”</p>
        </div>

        <x-field-picker name="field" :selected="old('field', $field->value)" dark />

        <div class="space-y-2 text-sm text-white">
            @if ($quote->media_path)
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remove_media" value="1"> {{ __('Remove the attached media') }}
                </label>
            @endif
            <label class="flex items-center gap-2">
                <x-icon name="photo" class="h-5 w-5" />
                <span>{{ __('Replace image / video') }}</span>
                <input type="file" name="media" accept="image/*,video/*" class="text-xs">
            </label>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-secondary" data-sound="save">{{ __('Save') }}</button>
            <a href="{{ url()->current() }}" class="btn text-white hover:underline">{{ __('Cancel') }}</a>
        </div>
    </form>
@else
    <article id="quote-{{ $quote->id }}" style="{{ $cardStyle }}"
        class="relative rounded-2xl p-5 ps-8 shadow {{ $cardColors }}">
        {{-- Color strip: the field of this quote --}}
        <span class="field-strip bg-{{ $field->color() }}" aria-hidden="true"></span>

        <header class="mb-2 flex items-center gap-3">
            <a href="{{ route('users.show', $quote->user) }}" class="flex min-w-0 items-center gap-2 hover:underline">
                <img src="{{ $quote->user->avatarUrl() }}" alt="" class="h-8 w-8 shrink-0 rounded-full object-cover">
                <span class="truncate text-sm font-semibold">{{ $isMine ? __('You') : $quote->user->name }}</span>
            </a>
            <a href="{{ route('communities.show', $field->value) }}"
                class="rounded-full px-2 py-0.5 text-xs font-semibold opacity-80 hover:opacity-100"
                style="background: var(--field-bg); color: var(--field-ink)">{{ $field->label() }}</a>

            @if ($quote->isDraft())
                <span class="rounded-full bg-tuwaiq-orange px-2 py-0.5 text-xs font-bold text-tuwaiq-purple">{{ __('Draft') }}</span>
            @elseif ($quote->isScheduled())
                <span class="rounded-full bg-tuwaiq-teal px-2 py-0.5 text-xs font-bold text-tuwaiq-purple">
                    {{ __('Scheduled') }} · <time datetime="{{ $quote->published_at->toIso8601String() }}" data-local-time>{{ $quote->published_at->format('Y-m-d H:i') }} UTC</time>
                </span>
            @endif

            @if ($isMine)
                <div class="ms-auto flex items-center gap-3">
                    @unless ($quote->isLive())
                        <form method="POST" action="{{ route('quotes.publish', $quote) }}">
                            @csrf
                            <button type="submit" class="rounded-full bg-tuwaiq-orange px-3 py-1 text-xs font-bold text-tuwaiq-purple hover:opacity-90"
                                data-burst data-sound="send">{{ __('Publish now') }}</button>
                        </form>
                    @endunless
                    <a href="{{ request()->fullUrlWithQuery(['edit' => $quote->id]) }}#quote-{{ $quote->id }}"
                        class="hover:text-tuwaiq-orange" aria-label="{{ __('Edit') }}" title="{{ __('Edit') }}">✎</a>

                    <form method="POST" action="{{ route('quotes.destroy', $quote) }}"
                        onsubmit="return confirm(@js(__('Delete this quote?')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="hover:text-tuwaiq-orange" aria-label="{{ __('Delete') }}"
                            title="{{ __('Delete') }}">⌫</button>
                    </form>
                </div>
            @endif
        </header>

        <p class="{{ $textClasses }}" dir="auto" @if ($fontFamily) style="font-family: {{ $fontFamily }}" @endif>“{{ $quote->content }}”</p>

        @if ($quote->media_path)
            <div class="mt-3 overflow-hidden rounded-xl">
                @if ($quote->media_type === 'video')
                    <video src="{{ $quote->mediaUrl() }}" controls preload="metadata" class="max-h-96 w-full bg-black"></video>
                @else
                    <img src="{{ $quote->mediaUrl() }}" alt="" loading="lazy" class="max-h-96 w-full object-cover">
                @endif
            </div>
        @endif

        <p class="mt-2 text-sm opacity-80">
            <span class="text-xs opacity-70">{{ ($quote->published_at ?? $quote->created_at)->translatedFormat('Y-m-d') }}</span>
        </p>

        {{-- Translation appears here when you tap "Translate" --}}
        <div class="mt-3 rounded-xl bg-black/10 p-3 text-sm" data-translation hidden>
            <p data-translation-text></p>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                <span class="opacity-60">{{ __('Automatic translation') }} ·</span>
                <button type="button" class="rounded-full border border-current/30 px-2 py-0.5 hover:bg-black/10" data-translate-to="ar" lang="ar">عربي</button>
                <button type="button" class="rounded-full border border-current/30 px-2 py-0.5 hover:bg-black/10" data-translate-to="en" lang="en">English</button>
            </div>
        </div>

        {{-- Interactive buttons (only on published quotes) --}}
        @if ($quote->isLive())
        <div class="mt-4 flex flex-wrap items-center gap-2" data-quote-actions>
            <form method="POST" action="{{ route('quotes.react', $quote) }}" data-ajax="reaction">
                @csrf
                <input type="hidden" name="type" value="like">
                <button type="submit" class="action-btn" aria-pressed="{{ $myReaction === 'like' ? 'true' : 'false' }}"
                    data-burst data-sound="like" aria-label="{{ __('Like') }}" title="{{ __('Like') }}">
                    <x-icon name="like" />
                    <span data-count="likes">{{ $quote->likes_count ?? 0 }}</span>
                </button>
            </form>

            <form method="POST" action="{{ route('quotes.react', $quote) }}" data-ajax="reaction">
                @csrf
                <input type="hidden" name="type" value="dislike">
                <button type="submit" class="action-btn" aria-pressed="{{ $myReaction === 'dislike' ? 'true' : 'false' }}"
                    data-burst data-sound="dislike" aria-label="{{ __('Dislike') }}" title="{{ __('Dislike') }}">
                    <x-icon name="dislike" />
                    <span data-count="dislikes">{{ $quote->dislikes_count ?? 0 }}</span>
                </button>
            </form>

            <form method="POST" action="{{ route('quotes.favorite', $quote) }}" data-ajax="favorite">
                @csrf
                <button type="submit" class="action-btn" aria-pressed="{{ $isSaved ? 'true' : 'false' }}" data-burst
                    data-sound="save" aria-label="{{ __('Save to favorites') }}" title="{{ __('Save to favorites') }}">
                    <x-icon name="bookmark" />
                    <span class="hidden sm:inline">{{ __('Save') }}</span>
                </button>
            </form>

            <button type="button" class="action-btn" data-copy-link="{{ $link }}" data-track-url="{{ route('quotes.track', $quote) }}" data-burst data-sound="copy"
                aria-label="{{ __('Copy link') }}" title="{{ __('Copy link') }}">
                <x-icon name="link" />
                <span class="hidden sm:inline">{{ __('Copy link') }}</span>
            </button>

            <button type="button" class="action-btn" data-comments-toggle aria-expanded="false" data-sound="open"
                aria-label="{{ __('Comments') }}" title="{{ __('Comments') }}">
                <x-icon name="chat" />
                <span data-comments-count>{{ $quote->comments_count ?? 0 }}</span>
            </button>

            <button type="button" class="action-btn" data-translate="{{ $quote->content }}" data-sound="tap"
                aria-label="{{ __('Translate') }}" title="{{ __('Translate') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
                </svg>
                <span class="hidden sm:inline">{{ __('Translate') }}</span>
            </button>

            <button type="button" class="action-btn" data-share data-share-url="{{ $link }}" data-track-url="{{ route('quotes.track', $quote) }}"
                data-share-text="“{{ $quote->content }}” — {{ $quote->user->name }}" data-burst data-sound="share"
                aria-label="{{ __('Share') }}" title="{{ __('Share') }}">
                <x-icon name="share" />
                <span class="hidden sm:inline">{{ __('Share') }}</span>
            </button>
        </div>

        {{-- Comments open here --}}
        <div class="mt-4 border-t border-current/15 pt-4" data-comments data-url="{{ route('comments.index', $quote) }}"
            @if (! ($openComments ?? false)) hidden @endif @if ($openComments ?? false) data-comments-autoload @endif>
            <p class="text-sm opacity-70">…</p>
        </div>
        @endif
    </article>
@endif
