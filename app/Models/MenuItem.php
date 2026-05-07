<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MenuItem extends Model
{
    protected $fillable = [
        'label',
        'url',
        'page_id',
        'parent_id',
        'sort_order',
        'is_active',
        'target',
    ];

    protected $casts = [
        'label' => 'array',
        'is_active' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('menu_tree'));
        static::deleted(fn () => Cache::forget('menu_tree'));
    }
}