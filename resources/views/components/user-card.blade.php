@props(['user'])

<a href="{{ route('users.show', $user) }}"
    class="glass-panel flex items-center gap-3 p-4 transition hover:-translate-y-0.5 hover:border-accent">
    <img src="{{ $user->avatarUrl() }}" alt="" class="h-12 w-12 shrink-0 rounded-full object-cover">
    <div class="min-w-0">
        <p class="flex items-center gap-1 truncate font-semibold">
            {{ $user->name }}
            @if ($user->is_private)
                <x-icon name="lock" class="h-3.5 w-3.5 text-ink-muted" />
            @endif
        </p>
        <p class="truncate text-xs text-ink-muted">{{ '@'.$user->username }}
            @if ($user->specialty) · {{ $user->specialty->label() }} @endif
        </p>
    </div>
    {{ $slot }}
</a>
