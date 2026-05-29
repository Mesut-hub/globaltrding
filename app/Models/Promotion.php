<?php
// app/Models/Promotion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Promotion extends Model
{
    protected $fillable = [
        'title', 'content', 'cta_label', 'cta_url', 'cta_target',
        'media_type', 'media_path', 'thumbnail_path',
        'animation_type', 'display_mode', 'display_frequency', 'auto_show_delay_ms',
        'overlay_size', 'overlay_position',
        'bg_color', 'text_color', 'cta_bg_color', 'cta_text_color',
        'show_close_button', 'close_on_backdrop',
        'target_pages',
        'is_active', 'starts_at', 'ends_at', 'priority',
    ];

    protected $casts = [
        'title'            => 'array',
        'content'          => 'array',
        'cta_label'        => 'array',
        'target_pages'     => 'array',
        'show_close_button'=> 'boolean',
        'close_on_backdrop'=> 'boolean',
        'is_active'        => 'boolean',
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'auto_show_delay_ms' => 'integer',
        'priority'         => 'integer',
    ];

    // ── Cache invalidation ────────────────────────────────────────────────
    protected static function booted(): void
    {
        $clearCache = function (): void {
            // Bump a locale-agnostic version counter.
            // PromotionService reads this and incorporates it into the cache key,
            // so ALL locales are invalidated simultaneously without needing to
            // know which locales exist.
            $newVersion = (int) Cache::get('gt_promo_v', 0) + 1;
            Cache::put('gt_promo_v', $newVersion, now()->addDays(30));

            // Belt-and-suspenders: also explicitly forget per-locale keys
            // in case old code paths still reference them.
            foreach (config('locales.supported', ['en']) as $locale) {
                Cache::forget("gt_promotion_payload.{$locale}");
            }
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    public function isCurrentlyScheduled(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at !== null && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at !== null && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}