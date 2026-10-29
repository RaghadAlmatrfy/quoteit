{{-- The page uses the main layout; the form and the picture come from Layout.AuthImg --}}
@extends('Layout.app')

@section('title', __('Sign Up'))
@section('content')

@section('form')

        <div class=" items-center justify-center p-8">
            <form method="post" action="{{ route('signup') }}" class="mx-auto  mt-5 max-w-xl rounded-lg  p-9 ">
                @csrf
                <h2 class="mb-4 text-2xl font-bold  text-center text-white">{{ __('Sign Up') }}</h2>

                <x-errors />

                <div class="mb-4">
                    <label for="name" class="label">{{ __('Name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name"
                        class="input peer user-invalid:border-red-500"
    required minlength="2" maxlength="50" pattern="[A-Za-z؀-ۿ ]+">

                    <p class="mt-1 text-xs text-white peer-user-invalid:text-red-600">
                        {{ __('2–50 characters, letters only.') }}
                    </p>
                </div>
                <div class="mb-4">
                    <label for="email" class="label">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email"
                        class="input peer user-invalid:border-red-500" required
                        pattern="[^@\s]+@[^@\s]+\.[A-Za-z]{2,}">
                    <p class="mt-1 text-xs text-white peer-user-invalid:text-red-600">
                        {{ __('A valid email, e.g. name@example.com') }}
                    </p>
                </div>

                <div class="mb-4">
                    <label for="password" class="label">{{ __('Password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password"
                        class="input peer user-invalid:border-red-500" required
                        minlength="8">
                    <p class="mt-1 text-xs text-white peer-user-invalid:text-red-600">
                        {{ __('At least 8 characters.') }}
                    </p>
                </div>
              
                <p class="mb-4 text-sm text-white">
                   {{ __('You already have an account?') }} <a href="{{ route('login') }}" class="text-blue-500 hover:underline">{{ __('Log In') }}</a>
                </p>

                <button type="submit" class="btn-primary w-full" data-sound="open">{{ __('Sign Up') }}</button>
            </form>
        </div>
        
@endsection
    @include('Layout.AuthImg')
@endsection
