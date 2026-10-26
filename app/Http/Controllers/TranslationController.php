<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{
    /**
     * Translate a quote (Arabic <-> English) inside the site.
     * The server asks the translation service, so the browser never leaves the page.
     */
    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:1000'],
            'to' => ['required', 'in:ar,en'],
        ]);

        $cacheKey = 'translation:'.$data['to'].':'.md5($data['text']);

        $translated = Cache::remember($cacheKey, now()->addWeek(), fn () => $this->translate($data['text'], $data['to']));

        if ($translated === null) {
            Cache::forget($cacheKey);

            return response()->json(['message' => __('Translation is not available right now.')], 503);
        }

        return response()->json(['translation' => $translated, 'to' => $data['to']]);
    }

    /**
     * Try Google first, then MyMemory. Returns null when both fail.
     */
    private function translate(string $text, string $to): ?string
    {
        // PHP on Windows often has no list of trusted certificates, so https calls fail.
        // In that case we use the public Mozilla list that comes with the project.
        $verify = ini_get('curl.cainfo') || ini_get('openssl.cafile')
            ? true
            : resource_path('certs/cacert.pem');
        $http = fn () => Http::timeout(8)->withOptions(['verify' => $verify]);

        try {
            $response = $http()->get('https://translate.googleapis.com/translate_a/single', [
                'client' => 'gtx', 'sl' => 'auto', 'tl' => $to, 'dt' => 't', 'q' => $text,
            ]);

            if ($response->ok() && is_array($response->json(0))) {
                return collect($response->json(0))->map(fn ($part) => $part[0] ?? '')->implode('');
            }
        } catch (\Throwable $exception) {
            Log::warning('Google translation failed: '.$exception->getMessage());
        }

        try {
            $from = $to === 'ar' ? 'en' : 'ar';
            $response = $http()->get('https://api.mymemory.translated.net/get', [
                'q' => $text, 'langpair' => $from.'|'.$to,
            ]);

            return $response->ok() ? $response->json('responseData.translatedText') : null;
        } catch (\Throwable $exception) {
            Log::warning('MyMemory translation failed: '.$exception->getMessage());

            return null;
        }
    }
}
