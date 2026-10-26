<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteShare;
use App\Support\Interests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;

class InterestController extends Controller
{
    /**
     * The "My interests" panel (reloaded by JavaScript after every like / save / share).
     */
    public function show(): string
    {
        return Blade::render('<x-my-interests :interests="$interests" />', ['interests' => Interests::for(Auth::user())]);
    }

    /**
     * Remember that I copied the link of a quote or shared it.
     */
    public function track(Request $request, Quote $quote): JsonResponse
    {
        abort_unless($quote->isVisibleTo(Auth::user()), 403);

        $type = $request->validate(['type' => ['required', Rule::in([QuoteShare::Copy, QuoteShare::Share])]])['type'];

        QuoteShare::create(['user_id' => Auth::id(), 'quote_id' => $quote->id, 'type' => $type]);

        return response()->json(['ok' => true]);
    }
}
