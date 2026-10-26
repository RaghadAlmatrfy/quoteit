@extends('Layout.AuthImg')
@extends('Layout.app')

@section('title', __('Log In'))
@section('content')
@section('form')

    <div class="flex items-center justify-center p-8">
        <form method="post" action="{{ route('login') }}" class="mx-auto mt-5 w-full max-w-xl rounded-lg p-9">
            @csrf

            <h2 class="mb-2 text-center text-2xl font-bold text-white">{{ __('Log In') }}</h2>
            <p class="mb-4 text-center text-sm text-tuwaiq-text/80" data-known-user-hint hidden></p>

            <x-errors />

            <div class="mb-4">
                <label for="email" class="label">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email"
                    class="input peer user-invalid:border-red-500" required data-remember-email
                    pattern="[^@\s]+@[^@\s]+\.[A-Za-z]{2,}">
                <p class="mt-1 text-xs text-white peer-user-invalid:text-red-600">
                    {{ __('A valid email, e.g. name@example.com') }}
                </p>

            </div>

            <div class="mb-4">
                <label for="password" class="label">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" autocomplete="current-password"
                    class="input peer user-invalid:border-red-500" required minlength="8">
                <p class="mt-1 text-xs text-white peer-user-invalid:text-red-600">
                    {{ __('At least 8 characters.') }}
                </p>
            </div>

            <label class="mb-4 flex items-center gap-2 text-sm text-white">
                <input type="checkbox" name="remember" value="1" checked> {{ __('Remember me') }}
            </label>

            <button type="submit" class="btn btn-primary w-full" data-sound="open">{{ __('Log In') }}</button>

            <p class="mt-4 text-sm text-white">
                {{ __("Don't have an account?") }} <a href="{{ route('signup') }}" class="text-blue-500 hover:underline">{{ __('Sign Up') }}</a>
            </p>
        </form>
    </div>


@endsection
@endsection
