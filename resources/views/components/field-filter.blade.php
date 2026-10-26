{{-- Filter chips: All + one chip per field --}}
@props(['active' => null, 'baseUrl'])

<nav aria-label="{{ __('Filter by field') }}" class="flex flex-wrap gap-2">
    <a href="{{ $baseUrl }}" class="chip {{ $active ? '' : 'chip-active' }}">{{ __('All fields') }}</a>
    @foreach (\App\Enums\Field::cases() as $field)
        <a href="{{ $baseUrl }}?field={{ $field->value }}" class="chip {{ $active === $field ? 'chip-active' : '' }}">
            <span class="h-2.5 w-2.5 rounded-full bg-{{ $field->color() }} ring-1 ring-white/50"></span>
            {{ $field->label() }}
        </a>
    @endforeach
</nav>
