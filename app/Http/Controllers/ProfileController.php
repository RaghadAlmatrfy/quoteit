<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Support\MediaUpload;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * /profile -> my own page.
     */
    public function show(): RedirectResponse
    {
        return redirect()->route('users.show', Auth::user());
    }

    public function edit(): View
    {
        return view('profile.edit', ['user' => Auth::user(), 'page' => 'Edit profile']);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $data = $request->safe()->except(['avatar', 'is_private', 'interests']);
        $data['interests'] = $request->input('interests', []);
        $data['is_private'] = $request->boolean('is_private');

        if ($request->hasFile('avatar')) {
            MediaUpload::delete($user->avatar_path);
            $data['avatar_path'] = MediaUpload::storeImage($request->file('avatar'), 'avatars');
        }

        $user->update($data);

        return redirect()->route('users.show', $user)->with('status', __('Your profile has been updated.'));
    }
}
