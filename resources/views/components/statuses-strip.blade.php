{{--
    New: temporary statuses (like stories).
    Each status shows for 10 seconds and disappears after 24 hours.
--}}
@props(['groups' => []])

<section class="glass-panel p-4" aria-labelledby="statuses-title" data-tour="statuses">
    <h2 id="statuses-title" class="mb-3 text-sm font-semibold text-ink-muted">{{ __('Statuses · 24 hours') }}</h2>

    <div class="flex gap-4 overflow-x-auto pb-1">
        @auth
            <button type="button" popovertarget="status-popup" data-sound="open"
                class="flex w-16 shrink-0 flex-col items-center gap-1 text-xs">
                <span class="relative">
                    <img src="{{ auth()->user()->avatarUrl() }}" alt="" class="h-14 w-14 rounded-full object-cover opacity-80">
                    <span class="absolute -end-1 -bottom-1 flex h-6 w-6 items-center justify-center rounded-full bg-tuwaiq-orange text-tuwaiq-purple ring-2 ring-[#2c2133]">
                        <x-icon name="plus" class="h-4 w-4" />
                    </span>
                </span>
                <span class="w-full truncate text-center">{{ __('Add status') }}</span>
            </button>
        @endauth

        @foreach ($groups as $index => $group)
            <button type="button" class="flex w-16 shrink-0 flex-col items-center gap-1 text-xs" data-status-open="{{ $index }}"
                data-status-ids="{{ collect($group['items'])->pluck('id')->implode(',') }}" data-sound="open">
                <span class="status-ring rounded-full p-[3px]">
                    <img src="{{ $group['user']['avatar'] }}" alt="" class="h-14 w-14 rounded-full object-cover ring-2 ring-[#2c2133]">
                </span>
                <span class="w-full truncate text-center">{{ $group['user']['mine'] ? __('You') : $group['user']['name'] }}</span>
            </button>
        @endforeach

        @if (empty($groups) && auth()->guest())
            <p class="text-sm text-ink-muted">{{ __('No statuses right now.') }}</p>
        @endif
    </div>
</section>

{{-- The viewer: full screen, 10 seconds per status --}}
<div id="status-viewer" class="fixed inset-0 z-[96] hidden items-center justify-center bg-black/90" role="dialog"
    aria-modal="true" aria-label="{{ __('Status') }}" data-seconds="{{ \App\Models\Status::SecondsOnScreen }}">
    <script type="application/json" data-status-groups>{!! json_encode($groups, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    <div class="relative flex h-full max-h-[92vh] w-full max-w-md flex-col overflow-hidden rounded-none sm:rounded-3xl" data-status-stage>
        <div class="absolute inset-x-0 top-0 z-10 space-y-3 bg-gradient-to-b from-black/60 to-transparent p-4">
            <div class="flex gap-1" data-status-bars></div>
            <div class="flex items-center gap-3 text-white">
                {{-- Tap the name or photo to open their profile --}}
                <a href="#" class="flex min-w-0 flex-1 items-center gap-3 hover:underline" data-status-profile>
                    <img src="" alt="" class="h-9 w-9 rounded-full object-cover" data-status-avatar>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold" data-status-name></span>
                        <span class="flex items-center gap-1 text-xs opacity-75">
                            <span data-status-ago></span>
                            <span data-status-private hidden class="inline-flex items-center gap-0.5">· <x-icon name="lock" class="h-3 w-3" /> {{ __('Friends only') }}</span>
                        </span>
                    </span>
                </a>
                <form method="POST" action="" data-status-delete hidden onsubmit="return confirm(@js(__('Delete this status?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-full p-1.5 hover:bg-white/20" aria-label="{{ __('Delete') }}"><x-icon name="trash" /></button>
                </form>
                <button type="button" class="rounded-full p-1.5 hover:bg-white/20" data-status-close aria-label="{{ __('Close') }}"><x-icon name="close" /></button>
            </div>
        </div>

        <div class="flex flex-1 items-center justify-center p-8 text-center" data-status-content></div>

        {{-- Bottom: like (others' statuses) or who viewed (my statuses) --}}
        <div class="absolute inset-x-0 bottom-0 z-20 flex items-center justify-center gap-3 bg-gradient-to-t from-black/60 to-transparent p-4">
            <button type="button" class="flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-semibold text-white backdrop-blur hover:bg-white/25"
                data-status-like hidden data-sound="like" data-burst>
                <x-icon name="heart" class="h-5 w-5" /> <span>{{ __('Like') }}</span>
            </button>
            <button type="button" class="flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-semibold text-white backdrop-blur hover:bg-white/25"
                data-status-viewers-toggle hidden>
                <x-icon name="eye" class="h-5 w-5" /> <span data-status-viewers-count>0</span> <span>{{ __('views') }}</span>
            </button>
        </div>

        {{-- Who viewed / liked my status --}}
        <div class="absolute inset-x-3 bottom-20 z-30 max-h-[45%] overflow-y-auto rounded-2xl bg-black/80 p-3 text-white" data-status-viewers hidden
            data-empty="{{ __('No one has seen it yet.') }}"></div>

        {{-- Tap left / right to go back / forward --}}
        <button type="button" class="absolute inset-y-24 start-0 w-1/3" data-status-prev aria-label="{{ __('Previous') }}"></button>
        <button type="button" class="absolute inset-y-24 end-0 w-1/3" data-status-next aria-label="{{ __('Next') }}"></button>
    </div>
</div>

@auth
    {{-- Add a status --}}
    <div id="status-popup" popover
        class="m-auto w-[90%] max-w-md rounded-3xl border border-line bg-panel p-0 text-ink shadow-2xl backdrop:bg-black/50 backdrop:backdrop-blur-sm">
        <form method="POST" action="{{ route('statuses.store') }}" enctype="multipart/form-data" class="space-y-4 p-6">
            @csrf
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold">{{ __('Add status') }}</h2>
                <button type="button" popovertarget="status-popup" popovertargetaction="hide" aria-label="{{ __('Close') }}"
                    class="rounded-full p-1 text-ink-muted hover:bg-tile"><x-icon name="close" /></button>
            </div>
            <p class="text-xs text-ink-muted">{{ __('Everyone sees each status for 10 seconds. It disappears after 24 hours.') }}</p>

            <div class="flex min-h-36 items-center justify-center rounded-2xl p-5 text-center text-xl font-semibold text-white"
                style="background: #432a80" data-status-preview>
                <textarea name="body" rows="3" maxlength="200" dir="auto"
                    class="w-full resize-none bg-transparent text-center placeholder-white/60 focus:outline-none"
                    placeholder="{{ __('Type a status…') }}"></textarea>
            </div>

            <p class="-mt-2 text-xs text-ink-muted">{{ __('Up to 200 characters. Choose a background color:') }}</p>
            <div class="flex flex-wrap gap-2">
                @foreach (['#432a80', '#2c2133', '#796989', '#7f626d', '#6f3dbf', '#f2a54a', '#4fc9b0', '#5324a6'] as $color)
                    <label class="swatch">
                        <input type="radio" name="bg_color" value="{{ $color }}" class="sr-only" @checked($loop->first) data-status-color>
                        <span class="swatch-dot" style="background: {{ $color }}"></span>
                    </label>
                @endforeach
            </div>

            {{-- Who can see it --}}
            <fieldset class="flex flex-wrap gap-2">
                <legend class="mb-1 text-sm font-semibold">{{ __('Who can see it?') }}</legend>
                <label class="field-chip border-line text-ink has-checked:border-accent has-checked:bg-accent has-checked:text-on-accent">
                    <input type="radio" name="visibility" value="public" class="sr-only" checked>
                    <x-icon name="globe" class="me-1 h-4 w-4" /> {{ __('Everyone') }}
                </label>
                <label class="field-chip border-line text-ink has-checked:border-accent has-checked:bg-accent has-checked:text-on-accent">
                    <input type="radio" name="visibility" value="friends" class="sr-only">
                    <x-icon name="lock" class="me-1 h-4 w-4" /> {{ __('Friends only') }}
                </label>
            </fieldset>

            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-dashed border-line p-3 text-sm">
                <x-icon name="photo" />
                <span>{{ __('Or add an image / video') }}</span>
                <input type="file" name="media" accept="image/*,video/*" class="sr-only" data-media-input="status-media-preview">
            </label>
            <div id="status-media-preview" class="hidden overflow-hidden rounded-xl"></div>
            <p class="-mt-2 text-xs text-ink-muted">{{ __('Write a text, add an image / video, or both (up to 20 MB).') }}</p>

            <button type="submit" class="panel-btn w-full py-3" data-sound="send">{{ __('Share status') }}</button>
        </form>
    </div>
@endauth
