<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NavGroup extends Model
{
    protected $fillable = ['key', 'label', 'sort_order', 'is_active'];

    protected $casts = [
        'label' => 'array',
        'is_active' => 'boolean',
    ];

    public function links()
    {
        return $this->hasMany(NavLink::class)->orderBy('sort_order');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('gt_nav_payload'));
        static::deleted(fn () => Cache::forget('gt_nav_payload'));
    }
}