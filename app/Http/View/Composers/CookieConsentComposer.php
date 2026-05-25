<?php

namespace App\Http\View\Composers;

use App\Services\CookieConsentService;
use Illuminate\View\View;

class CookieConsentComposer
{
    public function __construct(private CookieConsentService $service) {}

    public function compose(View $view): void
    {
        $locale   = app()->getLocale();
        $fallback = config('locales.default', 'en');

        $view->with('cookiePayload', $this->service->buildFrontendPayload($locale, $fallback));
    }
}