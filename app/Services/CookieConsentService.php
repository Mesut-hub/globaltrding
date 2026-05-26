<?php

namespace App\Services;

use App\Models\CookieCategory;
use App\Models\CookieConsentLog;
use App\Models\CookieSetting;
use Illuminate\Http\Request;

class CookieConsentService
{
    public function getCategories(): array
    {
        return CookieCategory::getCached();
    }

    public function getSettings(): array
    {
        return CookieSetting::getCached();
    }

    /**
     * Resolve a multilingual setting value.
     */
    public function getSetting(string $key, string $locale, string $fallback = 'en'): mixed
    {
        $settings = $this->getSettings();
        $value    = $settings[$key] ?? null;

        if (is_array($value)) {
            return $value[$locale] ?? $value[$fallback] ?? reset($value);
        }

        return $value;
    }

    /**
     * Build the payload delivered to the frontend.
     */
    public function buildFrontendPayload(string $locale, string $fallback = 'en'): array
    {
        $categories = $this->getCategories();
        $settings   = $this->getSettings();

        $pick = function ($value) use ($locale, $fallback) {
            if (!is_array($value)) return (string) ($value ?? '');
            return (string) ($value[$locale] ?? $value[$fallback] ?? reset($value) ?? '');
        };

        return [
            'version'     => (string) ($settings['consent_version'] ?? '1.0'),
            'position'    => (string) ($settings['position'] ?? 'bottom'),
            'showReject'  => filter_var($settings['show_reject_all'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'showManage'  => filter_var($settings['show_manage'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'policyUrl'   => '/' . $locale . '/' . ($settings['policy_url_suffix'] ?? 'pages/privacy-policy'),
            'title'       => $pick($settings['banner_title'] ?? []),
            'description' => $pick($settings['banner_description'] ?? []),
            'categories'  => collect($categories)->map(fn ($cat) => [
                'key'         => $cat->key,
                'label'       => $pick($cat->label),
                'description' => $pick($cat->description),
                'required'    => (bool) $cat->is_required,
            ])->values()->all(),
        ];
    }

    /**
     * Log consent to database (GDPR audit trail).
     */
    public function logConsent(Request $request, array $consents, string $locale): void
    {
        $settings = $this->getSettings();
        $version  = (string) ($settings['consent_version'] ?? '1.0');

        CookieConsentLog::create([
            'session_id'       => $request->session()->getId(),
            'ip_hash'          => hash('sha256', (string) $request->ip()),
            'consents'         => $consents,
            'locale'           => $locale,
            'user_agent_hash'  => hash('sha256', (string) $request->userAgent()),
            'consented_at'     => now(),
            'consent_version'  => $version,
        ]);
    }
}