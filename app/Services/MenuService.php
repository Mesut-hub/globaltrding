<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    public function tree(): array
    {
        return Cache::remember('menu_tree', 3600, function () {
            $items = MenuItem::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('parent_id');

            $build = function ($parentId) use (&$build, $items) {
                return ($items[$parentId] ?? collect())->map(function ($it) use (&$build) {
                    return [
                        'id' => $it->id,
                        'label' => $it->label ?? [],
                        'url' => $it->url,
                        'page_id' => $it->page_id,
                        'target' => $it->target ?? '_self',
                        'children' => $build($it->id),
                    ];
                })->values()->all();
            };

            return $build(null);
        });
    }
}