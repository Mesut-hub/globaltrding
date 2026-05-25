<?php

namespace App\Http\Controllers;

use App\Services\CookieConsentService;
use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    public function __construct(private CookieConsentService $service) {}

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $locale = app()->getLocale();

        $validated = $request->validate([
            'consents'        => ['required', 'array'],
            'consents.*'      => ['boolean'],
        ]);

        $consents = $validated['consents'];

        // Ensure necessary is always true
        $consents['necessary'] = true;

        // Log to DB (GDPR)
        $this->service->logConsent($request, $consents, $locale);

        return response()->json(['ok' => true]);
    }

    public function payload(Request $request): \Illuminate\Http\JsonResponse
    {
        $locale   = app()->getLocale();
        $fallback = config('locales.default', 'en');
        $payload  = $this->service->buildFrontendPayload($locale, $fallback);

        return response()->json($payload);
    }
}