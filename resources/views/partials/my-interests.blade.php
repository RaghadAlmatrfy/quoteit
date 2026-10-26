{{--
    "My interests": what I liked, saved, copied and shared, by field.
    Anything with no activity is hidden; it appears after my first like / save / share.
    JavaScript reloads this panel from route('interests.show') after each interaction.
--}}
<section aria-labelledby="interests-title" class="glass-panel" data-interests data-refresh-url="{{ route('interests.show') }}">
    <h2 id="interests-title" class="text-2xl font-bold">{{ __('My interests') }}</h2>

    @if ($interests['total'] === 0)
        <p class="mt-2 text-sm text-ink-muted">{{ __('Like, save, copy or share quotes, and your interests will appear here.') }}</p>
    @else
        <dl class="mt-4 grid grid-cols-3 gap-2 sm:gap-3">
            @foreach ([['likes', 'Likes', 'like'], ['saves', 'Saved', 'bookmark'], ['shares', 'Copied & shared', 'share']] as [$key, $label, $icon])
                @if ($interests[$key] > 0)
                    <div class="stat-tile">
                        <dt class="stat-label flex items-center justify-center gap-1"><x-icon :name="$icon" class="h-3.5 w-3.5" /> {{ __($label) }}</dt>
                        <dd class="stat-value">{{ $interests[$key] }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>

        {{-- Tech in general: everything I interacted with --}}
        <p class="mt-5 flex items-center justify-between rounded-xl bg-tile px-3 py-2 text-sm">
            <span class="font-semibold">{{ __('Tech in general') }}</span>
            <span class="tabular-nums">{{ trans_choice(':count interaction|:count interactions', $interests['total']) }}</span>
        </p>

        <ul class="mt-3 space-y-2">
            @foreach ($interests['fields'] as $row)
                <li class="flex items-center gap-3 text-sm">
                    <span class="w-36 shrink-0 sm:w-44">{{ $row['field']->label() }}</span>
                    <span class="h-2.5 flex-1 overflow-hidden rounded-full bg-ink/10">
                        <span class="block h-full rounded-full bg-{{ $row['field']->color() }} ring-1 ring-ink/30 ring-inset transition-all duration-700"
                            style="width: {{ $row['percent'] }}%"></span>
                    </span>
                    <span class="w-10 text-end tabular-nums text-ink-muted">{{ $row['percent'] }}%</span>
                </li>
            @endforeach
        </ul>
    @endif
</section>
