<?php

namespace App\Services;

use App\Models\NavGroup;
use Illuminate\Support\Facades\Cache;

class NavService
{
    public function payload(): array
    {
        return Cache::remember('gt_nav_payload', 3600, function () {
            $groups = NavGroup::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->with([
                    'page:id,slug',                          // overlay title link via CMS page
                    'links' => function ($q) {
                        $q->where('is_active', true)->orderBy('sort_order');
                    },
                    'links.page:id,slug',
                ])
                ->get();

            return $groups->map(function ($g) {
                return [
                    'key'   => $g->key,
                    'label' => $g->label ?? [],

                    // ── Overlay section-title link ───────────────────────────
                    // Consumed by the frontend overlay panel heading only.
                    // The main nav-bar item is never wrapped in an <a> tag.
                    'overlay_url'    => $g->url,                  // raw URL (nullable)
                    'overlay_slug'   => $g->page?->slug,          // CMS page slug (nullable)
                    'overlay_target' => $g->target ?? '_self',

                    // ── Child links ──────────────────────────────────────────
                    'links' => $g->links->map(function ($l) {
                        return [
                            'label'         => $l->label ?? [],
                            'url'           => $l->url,
                            'page_slug'     => $l->page?->slug,
                            'target'        => $l->target ?? '_self',
                            'action'        => $l->action,
                            'desc'          => $l->desc ?? null,
                            'preview_image' => $l->preview_image ?? null,
                            'is_finder'     => (bool) ($l->is_finder ?? false),
                        ];
                    })->values()->all(),
                ];
            })->values()->all();
        });
    }
}