<?php
// app/Services/PromotionService.php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PromotionService
{
    /**
     * Return the frontend-ready promotion payload for the given locale.
     * Cached 1 hour; busted automatically by Promotion::booted().
     */
    public function getActivePayload(string $locale, string $fallback = 'en'): array
    {
        $cacheKey = "gt_promotion_payload.{$locale}";

        return Cache::remember($cacheKey, 3600, function () use ($locale, $fallback): array {
            $now = now();

            $promotions = Promotion::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
                ->orderByDesc('priority')
                ->orderBy('id')
                ->get();

            return $promotions
                ->map(fn (Promotion $p) => $this->toFrontendArray($p, $locale, $fallback))
                ->values()
                ->all();
        });
    }

    // ─────────────────────────────────────────────────────────────────────

    private function toFrontendArray(Promotion $p, string $locale, string $fallback): array
    {
        $pick = function (mixed $value) use ($locale, $fallback): string {
            if (is_string($value) || is_numeric($value)) {
                return (string) $value;
            }
            if (!is_array($value)) {
                return '';
            }
            return (string) ($value[$locale] ?? $value[$fallback] ?? reset($value) ?? '');
        };

        return [
            'id'                 => $p->id,
            'title'              => $pick($p->title),
            'content'            => $pick($p->content),
            'cta_label'          => $pick($p->cta_label),
            'cta_url'            => (string) ($p->cta_url ?? ''),
            'cta_target'         => (string) ($p->cta_target ?? '_self'),
            'media_type'         => $p->media_type,
            'media_url'          => $this->resolveUrl($p->media_path),
            'thumbnail_url'      => $this->resolveUrl($p->thumbnail_path),
            'animation_type'     => $p->animation_type,
            'display_mode'       => $p->display_mode,
            'display_frequency'  => $p->display_frequency,
            'auto_show_delay_ms' => (int) $p->auto_show_delay_ms,
            'overlay_size'       => $p->overlay_size,
            'overlay_position'   => $p->overlay_position,
            'bg_color'           => $p->bg_color,
            'text_color'         => $p->text_color,
            'cta_bg_color'       => $p->cta_bg_color,
            'cta_text_color'     => $p->cta_text_color,
            'show_close_button'  => (bool) $p->show_close_button,
            'close_on_backdrop'  => (bool) $p->close_on_backdrop,
            'target_pages'       => $p->target_pages ?? ['*'],
        ];
    }

    private function resolveUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return Storage::disk('public')->url($path);
    }
}