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

        try {
            $payload = $this->service->buildFrontendPayload($locale, $fallback);
        } catch (\Throwable $e) {
            // Never break page rendering due to cookie service failure
            $payload = [
                'categories' => [],
                'version'    => '1.0',
                'position'   => 'bottom',
                'showReject' => false,
                'showManage' => false,
                'title'      => '',
                'description'=> '',
                'policyUrl'  => '/' . $locale . '/pages/privacy-policy',
            ];
        }

        $view->with('cookiePayload', $payload);
    }
}