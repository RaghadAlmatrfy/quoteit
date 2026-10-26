@extends('Layout.app')

@section('title', __('Edit profile'))
@section('content')
    <div class="mx-auto max-w-3xl space-y-6 p-8">
        <h1 class="page-title">{{ __('Edit profile') }}</h1>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="glass-panel space-y-5">
            @csrf
            @method('PUT')

            <x-errors />

            <div class="flex items-center gap-4">
                <img src="{{ $user->avatarUrl() }}" alt="" id="avatar-preview-img" class="h-20 w-20 rounded-full object-cover">
                <label class="panel-btn-ghost cursor-pointer">
                    <x-icon name="photo" class="h-4 w-4" /> {{ __('Change photo') }}
                    <input type="file" name="avatar" accept="image/*" class="sr-only" data-avatar-input>
                </label>
                <p class="text-xs text-ink-muted">{{ __('An image up to 5 MB.') }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-semibold">{{ __('Name') }}</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required minlength="2" maxlength="50" class="panel-input">
                    <p class="mt-1 text-xs text-ink-muted">{{ __('2–50 characters.') }}</p>
                </div>
                <div>
                    <label for="username" class="mb-1 block text-sm font-semibold">{{ __('Username') }}</label>
                    <input id="username" name="username" value="{{ old('username', $user->username) }}" required minlength="3"
                        maxlength="30" pattern="[A-Za-z0-9_\-]+" class="panel-input" dir="ltr">
                    <p class="mt-1 text-xs text-ink-muted">{{ __('Letters, numbers, - and _ only.') }}</p>
                </div>
            </div>

            <div>
                <label for="bio" class="mb-1 block text-sm font-semibold">{{ __('Bio') }}</label>
                <textarea id="bio" name="bio" rows="2" maxlength="160" class="panel-input">{{ old('bio', $user->bio) }}</textarea>
                <p class="mt-1 text-xs text-ink-muted">{{ __('Optional, up to 160 characters.') }}</p>
            </div>

            <div>
                <label for="specialty" class="mb-1 block text-sm font-semibold">{{ __('Specialty') }}</label>
                <select id="specialty" name="specialty" class="panel-input">
                    <option value="">{{ __('Choose…') }}</option>
                    @foreach (\App\Enums\Field::cases() as $field)
                        <option value="{{ $field->value }}" @selected(old('specialty', $user->specialty?->value) === $field->value)
                            class="text-black">{{ $field->label() }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-ink-muted">{{ __('Your main field. You appear as a member of its community.') }}</p>
            </div>

            <fieldset>
                <legend class="mb-2 text-sm font-semibold">{{ __('Interests') }}</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Enums\Field::cases() as $field)
                        <label class="field-chip border-line text-ink has-checked:border-{{ $field->color() }} has-checked:bg-{{ $field->color() }} {{ $field->isDark() ? 'has-checked:text-white' : 'has-checked:text-midnight-orchid' }}">
                            <input type="checkbox" name="interests[]" value="{{ $field->value }}" class="sr-only"
                                @checked(in_array($field->value, old('interests', $user->interests ?? []), true))>
                            {{ $field->label() }}
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-ink-muted">{{ __('Choose any fields you like. They show on your profile.') }}</p>
            </fieldset>

            {{-- Private / public account --}}
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-line p-4">
                <input type="checkbox" name="is_private" value="1" class="mt-1 h-4 w-4" @checked(old('is_private', $user->is_private))>
                <span>
                    <span class="flex items-center gap-1 font-semibold"><x-icon name="lock" class="h-4 w-4" /> {{ __('Private account') }}</span>
                    <span class="block text-sm text-ink-muted">{{ __('Only your friends can see your quotes. Everyone can still see your name and photo.') }}</span>
                </span>
            </label>

            <div class="flex gap-2">
                <button type="submit" class="panel-btn" data-sound="save">{{ __('Save') }}</button>
                <a href="{{ route('users.show', $user) }}" class="panel-btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
