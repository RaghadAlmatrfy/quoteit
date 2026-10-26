<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SignupController extends Controller
{
    public function signup(SignupRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', __('Your account has been created. Welcome!'));
    }
}
