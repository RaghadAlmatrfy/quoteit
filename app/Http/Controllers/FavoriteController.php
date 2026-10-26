<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Notifications\InteractionNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * My saved quotes.
     */
    public function index(): View
    {
        $viewer = Auth::user();

        $quotes = Quote::visibleTo($viewer)
            ->forCards($viewer)
            ->whereHas('favoritedBy', fn ($user) => $user->where('users.id', $viewer->id))
            ->latest()
            ->latest('id')
            ->paginate(15);

        return view('favorites', ['quotes' => $quotes, 'page' => 'Favorites']);
    }

    /**
     * Save / unsave a quote.
     */
    public function toggle(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        abort_unless($quote->isVisibleTo(Auth::user()), 403);

        $result = Auth::user()->favorites()->toggle($quote->id);
        $saved = count($result['attached']) > 0;

        if ($saved) {
            InteractionNotification::send($quote->user, InteractionNotification::Saved, Auth::user(), $quote);
        }

        if (! $request->expectsJson()) {
            return redirect()->back();
        }

        return response()->json(['saved' => $saved]);
    }
}
