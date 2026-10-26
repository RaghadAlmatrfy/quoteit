<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark all my notifications as read (when I open the bell).
     */
    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return $request->expectsJson() ? response()->json(['ok' => true]) : redirect()->back();
    }
}
