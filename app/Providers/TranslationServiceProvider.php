<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;

class TranslationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Pre-warm translation cache on first request in production
        if (app()->environment('production')) {
            $this->warmTranslationCache();
        }
    }

    private function warmTranslationCache(): void
    {
        $locales = config('locales.supported', ['en']);
        
        foreach ($locales as $locale) {
            Cache::remember("translations.{$locale}", 3600, function () use ($locale) {
                $path = app()->langPath($locale);
                if (!is_dir($path)) return [];
                
                $translations = [];
                foreach (glob("{$path}/*.php") as $file) {
                    $key = basename($file, '.php');
                    $translations[$key] = require $file;
                }
                return $translations;
            });
        }
    }
}