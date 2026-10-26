{{--
    Text style controls for a quote: font, size, bold, italic, alignment, background color.
    A live preview updates while you choose (see resources/js/app.js → "Style preview").
    - quote: the quote being edited (or null for a new one)
    - dark:  true when shown on the purple edit card
--}}
@props(['quote' => null, 'dark' => false])

@php
    use App\Support\QuoteStyle;

    $font = old('font', $quote?->font ?? 'default');
    $size = old('font_size', $quote?->font_size ?? 'md');
    $align = old('text_align', $quote?->text_align ?? 'start');
    $bg = old('bg_color', $quote?->bg_color ?? '');
    $labelClass = $dark ? 'text-white' : 'text-tuwaiq-purple';
    $controlClass = $dark
        ? 'border-white/30 bg-white/10 text-white'
        : 'border-tuwaiq-purple/20 bg-white text-tuwaiq-purple';
@endphp

<fieldset class="space-y-3" data-style-controls>
    <legend class="mb-1 text-sm font-semibold {{ $labelClass }}">{{ __('Text style') }}</legend>
    <p class="-mt-2 text-xs {{ $dark ? 'text-white/70' : 'text-gray-500' }}">{{ __('Optional: font, size, bold, italic, alignment and background. The preview shows the result.') }}</p>

    <div class="flex flex-wrap items-center gap-2">
        <label class="sr-only" for="font-{{ $quote?->id ?? 'new' }}">{{ __('Font') }}</label>
        <select id="font-{{ $quote?->id ?? 'new' }}" name="font" data-style="font"
            class="rounded-lg border px-2 py-1.5 text-sm {{ $controlClass }}" title="{{ __('Font') }}">
            @foreach (QuoteStyle::Fonts as $key => $option)
                <option value="{{ $key }}" data-family="{{ $option['family'] }}" @selected($font === $key)
                    style="font-family: {{ $option['family'] }}" class="text-black">
                    {{ $key === 'default' ? __('Default font') : $option['label'] }}
                </option>
            @endforeach
        </select>

        <label class="sr-only" for="size-{{ $quote?->id ?? 'new' }}">{{ __('Size') }}</label>
        <select id="size-{{ $quote?->id ?? 'new' }}" name="font_size" data-style="size"
            class="rounded-lg border px-2 py-1.5 text-sm {{ $controlClass }}" title="{{ __('Size') }}">
            @foreach (QuoteStyle::Sizes as $key => $option)
                <option value="{{ $key }}" data-class="{{ $option['class'] }}" @selected($size === $key) class="text-black">
                    {{ __($option['label']) }}</option>
            @endforeach
        </select>

        {{-- Bold / Italic --}}
        <label class="style-toggle {{ $controlClass }}" title="{{ __('Bold') }}">
            <input type="checkbox" name="is_bold" value="1" class="sr-only" data-style="bold"
                @checked(old('is_bold', $quote?->is_bold))>
            <span class="font-bold">B</span>
        </label>
        <label class="style-toggle {{ $controlClass }}" title="{{ __('Italic') }}">
            <input type="checkbox" name="is_italic" value="1" class="sr-only" data-style="italic"
                @checked(old('is_italic', $quote?->is_italic))>
            <span class="font-serif italic">I</span>
        </label>

        {{-- Alignment: right / center / left --}}
        @foreach (['right' => 'Align right', 'center' => 'Align center', 'left' => 'Align left'] as $value => $label)
            <label class="style-toggle {{ $controlClass }}" title="{{ __($label) }}">
                <input type="radio" name="text_align" value="{{ $value }}" class="sr-only" data-style="align"
                    @checked($align === $value)>
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    @if ($value === 'right')
                        <path d="M4 6h16M10 12h10M6 18h14" />
                    @elseif ($value === 'center')
                        <path d="M4 6h16M7 12h10M5 18h14" />
                    @else
                        <path d="M4 6h16M4 12h10M4 18h14" />
                    @endif
                </svg>
                <span class="sr-only">{{ __($label) }}</span>
            </label>
        @endforeach
        <input type="radio" name="text_align" value="start" class="hidden" @checked($align === 'start') data-style="align">
    </div>

    {{-- Background color --}}
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs font-semibold {{ $labelClass }}">{{ __('Background') }}</span>
        <label class="swatch" title="{{ __('Default') }}">
            <input type="radio" name="bg_color" value="" class="sr-only" data-style="bg" @checked($bg === '')>
            <span class="swatch-dot bg-[conic-gradient(#432a80_0_50%,#ffffff_0)]"></span>
        </label>
        @foreach (QuoteStyle::Backgrounds as $color)
            <label class="swatch" title="{{ $color }}">
                <input type="radio" name="bg_color" value="{{ $color }}" class="sr-only" data-style="bg"
                    @checked(strtolower($bg) === $color)>
                <span class="swatch-dot" style="background: {{ $color }}"></span>
            </label>
        @endforeach
        <label class="swatch" title="{{ __('Pick any color') }}">
            <input type="radio" name="bg_color" value="{{ $bg && ! in_array(strtolower($bg), QuoteStyle::Backgrounds, true) ? $bg : '#53398f' }}"
                class="sr-only" data-style="bg" data-custom-color-radio
                @checked($bg && ! in_array(strtolower($bg), QuoteStyle::Backgrounds, true))>
            <span class="swatch-dot relative overflow-hidden bg-[conic-gradient(red,yellow,lime,cyan,blue,magenta,red)]">
                <input type="color" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" data-custom-color
                    value="{{ $bg && ! in_array(strtolower($bg), QuoteStyle::Backgrounds, true) ? $bg : '#53398f' }}"
                    aria-label="{{ __('Pick any color') }}">
            </span>
        </label>
    </div>
</fieldset>
