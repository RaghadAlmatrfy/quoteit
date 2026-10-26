<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    /**
     * The welcome tour was finished or skipped: do not show it automatically again.
     */
    public function complete(): JsonResponse
    {
        Auth::user()->forceFill(['tour_completed_at' => now()])->save();

        return response()->json(['ok' => true]);
    }
}
