<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Search people by name or @username.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $people = User::query()
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('username', "%{$search}%")))
            ->when(Auth::check(), fn ($query) => $query->whereKeyNot(Auth::id()))
            ->withCount('quotes')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', ['people' => $people, 'search' => $search, 'page' => 'People']);
    }

    /**
     * A user's page: info, interests, and their quotes (if allowed).
     */
    public function show(User $user): View
    {
        $viewer = Auth::user();
        $canSeeQuotes = $user->isVisibleTo($viewer);

        $quotes = $canSeeQuotes
            ? Quote::forCards($viewer)->published()->where('user_id', $user->id)->latest()->latest('id')->paginate(10)
            : null;

        return view('users.show', [
            'user' => $user,
            'quotes' => $quotes,
            'canSeeQuotes' => $canSeeQuotes,
            'composerField' => $viewer?->is($user) ? $user->specialty : null,
            'friendship' => $viewer && ! $viewer->is($user) ? $viewer->friendshipWith($user) : null,
            'stats' => [
                'quotes' => $user->quotes()->published()->count(),
                'friends' => count($user->friendIds()),
                'likes' => Reaction::where('type', Reaction::Like)
                    ->whereIn('quote_id', $user->quotes()->select('id'))
                    ->count(),
            ],
            'page' => 'Profile',
        ]);
    }
}
