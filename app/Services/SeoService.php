<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Centralized SEO resolution service.
 *
 * Resolves SEO metadata from any model using the unified `seo` JSON column.
 * All models store: seo.title.{locale}, seo.description.{locale}, seo.og_image, etc.
 */
class SeoService
{
    public function resolve(
        ?Model $model,
        string $locale,
        string $fallback,
        ?string $defaultTitle = null,
        ?string $defaultDescription = null,
        ?string $defaultImage = null,
    ): array {
        $seo = is_array($model?->seo ?? null) ? ($model->seo ?? []) : [];

        $title = $this->pickLocale(data_get($seo, 'title'), $locale, $fallback)
            ?: $this->pickLocale($model?->title ?? null, $locale, $fallback)
            ?: $defaultTitle
            ?: config('app.name', 'Globaltrding');

        $description = $this->pickLocale(data_get($seo, 'description'), $locale, $fallback)
            ?: $this->pickLocale($model?->excerpt ?? null, $locale, $fallback)
            ?: $defaultDescription
            ?: '';

        $ogImage = (string) (data_get($seo, 'og_image') ?: $defaultImage ?: '');

        // Resolve og_image from model cover if available and not set in seo
        if ($ogImage === '' && $model !== null) {
            $coverPath = $model->cover_image_path
                ?? $model->cover_video_path
                ?? null; // videos don't make good OG images, but poster does
            $posterPath = $model->cover_poster_path ?? null;

            $resolvePath = $posterPath ?: ($coverPath && !str_ends_with((string)$coverPath, '.mp4') && !str_ends_with((string)$coverPath, '.webm') ? $coverPath : null);

            if ($resolvePath) {
                $ogImage = \Illuminate\Support\Facades\Storage::disk('public')->url($resolvePath);
            }
        }

        $canonical   = (string) (data_get($seo, 'canonical') ?: '');
        $robots      = (string) (data_get($seo, 'robots') ?: '');
        $schemaType  = (string) (data_get($seo, 'schema_type') ?: '');

        return compact('title', 'description', 'ogImage', 'canonical', 'robots', 'schemaType');
    }

    private function pickLocale(mixed $value, string $locale, string $fallback): string
    {
        if (is_string($value) || is_numeric($value)) {
            return trim((string) $value);
        }
        if (!is_array($value)) return '';

        $v = data_get($value, $locale) ?? data_get($value, $fallback);
        if (is_string($v) || is_numeric($v)) return trim((string) $v);

        // Try any non-empty value as last resort
        foreach ($value as $vv) {
            if (is_string($vv) && trim($vv) !== '') return trim($vv);
        }
        return '';
    }
}