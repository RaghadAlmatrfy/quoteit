<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Reaction;
use App\Notifications\InteractionNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    /**
     * Like or dislike a quote. Clicking the same button again removes the reaction.
     */
    public function store(Request $request, Quote $quote): JsonResponse|RedirectResponse
    {
        abort_unless($quote->isVisibleTo(Auth::user()), 403);

        $type = $request->validate(['type' => ['required', Rule::in([Reaction::Like, Reaction::Dislike])]])['type'];

        $existing = Reaction::where('user_id', Auth::id())->where('quote_id', $quote->id)->first();

        if ($existing?->type === $type) {
            $existing->delete();
            $current = null;
        } elseif ($existing !== null) {
            $existing->update(['type' => $type]);
            $current = $type;
        } else {
            Reaction::create(['user_id' => Auth::id(), 'quote_id' => $quote->id, 'type' => $type]);
            $current = $type;

            if ($type === Reaction::Like) {
                InteractionNotification::send($quote->user, InteractionNotification::Liked, Auth::user(), $quote);
            }
        }

        if (! $request->expectsJson()) {
            return redirect()->back();
        }

        // Both numbers in one database trip
        $counts = $quote->reactions()->toBase()
            ->selectRaw('sum(case when type = ? then 1 else 0 end) as likes', [Reaction::Like])
            ->selectRaw('sum(case when type = ? then 1 else 0 end) as dislikes', [Reaction::Dislike])
            ->first();

        return response()->json([
            'reaction' => $current,
            'likes' => (int) $counts->likes,
            'dislikes' => (int) $counts->dislikes,
        ]);
    }
}
