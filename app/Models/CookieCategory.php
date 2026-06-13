<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CookieCategory extends Model
{
    protected $fillable = ['key', 'label', 'description', 'is_required', 'is_enabled', 'sort_order'];

    protected $casts = [
        'label'       => 'array',
        'description' => 'array',
        'is_required' => 'boolean',
        'is_enabled'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('cookie_categories'));
        static::deleted(fn () => Cache::forget('cookie_categories'));
    }

    public static function getCached(): array
    {
        return Cache::remember('cookie_categories', 3600, function () {
            return static::query()
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn (self $cat) => [
                    'key'         => $cat->key,
                    'label'       => $cat->label ?? [],
                    'description' => $cat->description ?? [],
                    'is_required' => (bool) $cat->is_required,
                    'is_enabled'  => (bool) $cat->is_enabled,
                    'sort_order'  => $cat->sort_order,
                ])
                ->all();
        });
    }
}