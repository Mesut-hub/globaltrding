<?php
// app/Http/View/Composers/PromotionComposer.php

namespace App\Http\View\Composers;

use App\Services\PromotionService;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PromotionComposer
{
    public function __construct(private readonly PromotionService $service) {}

    public function compose(View $view): void
    {
        $locale   = app()->getLocale();
        $fallback = config('locales.default', 'en');

        try {
            $payload = $this->service->getActivePayload($locale, $fallback);
        } catch (\Throwable $e) {
            // Never break page rendering due to promotion service failure
            \Illuminate\Support\Facades\Log::warning('[PromotionComposer] Failed to load payload: ' . $e->getMessage());
            $payload = [];
        }

        $view->with('promotionPayload', $payload);
    }
}