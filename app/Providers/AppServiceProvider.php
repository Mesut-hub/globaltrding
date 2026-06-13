<?php

namespace App\Providers;

use App\Http\View\Composers\CookieConsentComposer;
use App\Http\View\Composers\PromotionComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\SeoService::class);
        $this->app->singleton(\App\Services\CookieConsentService::class);
        $this->app->singleton(\App\Services\PromotionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', CookieConsentComposer::class);
        View::composer('layouts.app', PromotionComposer::class);
        // Localize pagination
        \Illuminate\Pagination\Paginator::defaultView('pagination::tailwind');
        \Illuminate\Pagination\Paginator::defaultSimpleView('pagination::simple-tailwind');
    }
}
