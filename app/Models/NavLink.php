<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NavLink extends Model
{
    protected $fillable = [
        'title',
        'desc',
        'preview_image',
        'nav_group_id',
        'label',
        'url',
        'page_id',
        'sort_order',
        'is_finder',
        'is_active',
        'target',
        'action',
    ];

    protected $casts = [
        'label'     => 'array',
        'desc'      => 'array',
        'is_active' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(NavGroup::class, 'nav_group_id');
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('gt_nav_payload'));
        static::deleted(fn () => Cache::forget('gt_nav_payload'));
    }
}