{{--
    Field picker: radio "chips" (AI, Data, Cloud ...), each in its own color.
    - name:     input name
    - selected: the checked field value
    - dark:     true when shown on the purple card (white text)
--}}
@props(['name' => 'field', 'selected' => 'ai', 'dark' => false])

<fieldset>
    <legend class="mb-2 text-sm font-semibold {{ $dark ? 'text-white' : 'text-tuwaiq-purple' }}">
        {{ __('Field') }}
    </legend>

    <div class="flex flex-wrap gap-2">
        @foreach (\App\Enums\Field::cases() as $field)
            <label
                class="field-chip {{ $dark ? 'border-white/40 text-white' : 'border-tuwaiq-purple/20 text-tuwaiq-purple' }} has-checked:border-{{ $field->color() }} has-checked:bg-{{ $field->color() }} {{ $field->isDark() ? 'has-checked:text-white' : 'has-checked:text-midnight-orchid' }}">
                <input type="radio" name="{{ $name }}" value="{{ $field->value }}" class="sr-only"
                    @checked($field->value === $selected) required>
                {{ $field->label() }}
            </label>
        @endforeach
    </div>
    <p class="mt-1 text-xs {{ $dark ? 'text-white/70' : 'text-gray-500' }}">{{ __('Choose the field closest to your quote. It decides its community and color.') }}</p>
</fieldset>
