<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Use the language the visitor chose (?lang=, session or cookie), English by default.
     * Switching with ?lang= stays on the same page, with no redirect.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $chosen = $request->query('lang');

        if (in_array($chosen, ['en', 'ar'], true)) {
            $request->session()->put('locale', $chosen);
            Cookie::queue(Cookie::forever('locale', $chosen));
        }

        $locale = $request->session()->get('locale', $request->cookie('locale'));

        if (in_array($locale, ['en', 'ar'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
