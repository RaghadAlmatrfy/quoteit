{{--
    Display tools: notifications, text size, light/dark, language, tour, sound.
    vertical = true  -> stacked in the right bar (computer)
    vertical = false -> in a row in the top bar (phone)
--}}
@props(['vertical' => false])
<div class="flex {{ $vertical ? 'flex-col items-center gap-2' : 'flex-wrap items-center justify-end gap-1.5' }}"
    role="group" aria-label="{{ __('Display settings') }}" data-tour="tools">
    @auth
        <x-notifications-bell />
    @endauth

    {{-- Text size: − then + (left / right, or top / bottom in the right bar) --}}
    <div class="tool-group {{ $vertical ? 'h-auto flex-col py-1' : '' }}" dir="ltr" role="group" aria-label="{{ __('Text size') }}">
        <button type="button" data-font-step="-1" data-tip="{{ __('Smaller text') }}" aria-label="{{ __('Smaller text') }}"
            class="{{ $vertical ? 'py-1.5' : '' }}">−</button>
        <span class="px-1 text-xs text-ink-muted" aria-hidden="true">A</span>
        <button type="button" data-font-step="1" data-tip="{{ __('Bigger text') }}" aria-label="{{ __('Bigger text') }}"
            class="{{ $vertical ? 'py-1.5' : '' }}">+</button>
    </div>

    {{-- Light / dark: one switch --}}
    <button type="button" class="theme-switch" dir="ltr" role="switch" aria-checked="true" data-theme-toggle
        data-tip="{{ __('Light / dark mode') }}" aria-label="{{ __('Dark mode') }}">
        <span class="knob">
            <x-icon name="sun" class="h-4 w-4" data-theme-icon-light />
            <x-icon name="moon" class="hidden h-4 w-4" data-theme-icon-dark />
        </span>
    </button>

    <a href="{{ route('locale', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="tool-btn" data-sound="open"
        lang="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}"
        data-tip="{{ __('Change language') }}">{{ app()->getLocale() === 'ar' ? 'EN' : 'ع' }}</a>

    @auth
        <button type="button" class="tool-btn" data-tour-start data-tip="{{ __('Show the tour') }}"
            aria-label="{{ __('Show the tour') }}"><x-icon name="bulb" class="h-4 w-4" /></button>
    @endauth

    {{-- Sound: choose the click sound (or turn it off) --}}
    <button type="button" class="tool-btn" popovertarget="sound-menu" data-sound-button data-tip="{{ __('Choose the sound') }}"
        aria-label="{{ __('Choose the sound') }}"><x-icon name="sound" class="h-4 w-4" data-sound-on /><x-icon name="mute"
            class="hidden h-4 w-4" data-sound-off /></button>
</div>
