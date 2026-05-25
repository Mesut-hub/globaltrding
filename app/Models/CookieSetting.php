<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CookieSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'json'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('cookie_settings_v2'));
        static::deleted(fn () => Cache::forget('cookie_settings_v2'));
    }

    public static function getCached(): array
    {
        return Cache::remember('cookie_settings_v2', 3600, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }
}