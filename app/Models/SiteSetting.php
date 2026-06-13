<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings'));
        static::deleted(fn () => Cache::forget('site_settings'));
    }

    public static function getCached(): array
    {
        return Cache::remember('site_settings', 3600, function () {
            return static::query()->pluck('value', 'key')->all();
        });
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $all = static::getCached();
        return array_key_exists($key, $all) ? (string) $all[$key] : $default;
    }
}