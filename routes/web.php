<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductAuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Response;
use App\Models\Product;
use App\Models\Industry;
use App\Models\NewsPost;
use App\Models\Page;

Route::get('/sitemap.xml', function () {
    $base = rtrim(config('app.url'), '/');
    if ($base === '' || str_contains($base, '127.0.0.1') || str_contains($base, 'localhost')) {
        $base = rtrim(request()->getSchemeAndHttpHost(), '/');
    }
    $locales  = config('locales.supported', ['en']);
    $entries  = [];

    $add = function (string $url, ?string $lastmod, float $priority, string $changefreq, ?string $imageUrl = null, ?string $imageTitle = null) use (&$entries) {
        $entries[$url] = compact('url', 'lastmod', 'priority', 'changefreq', 'imageUrl', 'imageTitle');
    };

    // Home
    foreach ($locales as $loc) {
        $add("{$base}/{$loc}", now()->toDateString(), 1.0, 'daily');
    }

    // Static high-priority
    $statics = [
        'products'     => [0.9, 'daily'],
        'industries'   => [0.8, 'weekly'],
        'news'         => [0.8, 'daily'],
        'market'       => [0.7, 'daily'],
        'collaboration'=> [0.6, 'monthly'],
        'inquiry'      => [0.5, 'monthly'],
    ];
    foreach ($statics as $path => [$priority, $freq]) {
        foreach ($locales as $loc) {
            $add("{$base}/{$loc}/{$path}", null, $priority, $freq);
        }
    }

    // Products
    \App\Models\Product::query()
        ->where('is_published', true)
        ->get(['slug', 'updated_at', 'display_name', 'seo'])
        ->each(function ($p) use (&$add, $base, $locales) {
            foreach ($locales as $loc) {
                $add(
                    "{$base}/{$loc}/products/{$p->slug}",
                    optional($p->updated_at)->toDateString(),
                    0.8,
                    'weekly'
                );
            }
        });

    // Industries
    \App\Models\Industry::query()
        ->where('is_published', true)
        ->get(['slug', 'updated_at', 'cover_image_path', 'title'])
        ->each(function ($i) use (&$add, $base, $locales) {
            $imgUrl = $i->cover_image_path
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($i->cover_image_path)
                : null;
            $imgTitle = is_array($i->title) ? (reset($i->title) ?: '') : (string)($i->title ?? '');
            foreach ($locales as $loc) {
                $add(
                    "{$base}/{$loc}/industries/{$i->slug}",
                    optional($i->updated_at)->toDateString(),
                    0.75,
                    'weekly',
                    $imgUrl,
                    $imgTitle
                );
            }
        });

    // News
    \App\Models\NewsPost::query()
        ->where('is_published', true)
        ->get(['slug', 'updated_at', 'cover_image_path', 'title'])
        ->each(function ($n) use (&$add, $base, $locales) {
            $imgUrl = $n->cover_image_path
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($n->cover_image_path)
                : null;
            $imgTitle = is_array($n->title) ? (reset($n->title) ?: '') : (string)($n->title ?? '');
            foreach ($locales as $loc) {
                $add(
                    "{$base}/{$loc}/news/{$n->slug}",
                    optional($n->updated_at)->toDateString(),
                    0.7,
                    'weekly',
                    $imgUrl,
                    $imgTitle
                );
            }
        });

    // Pages
    \App\Models\Page::query()
        ->where('is_published', true)
        ->get(['slug', 'updated_at'])
        ->each(function ($pg) use (&$add, $base, $locales) {
            foreach ($locales as $loc) {
                $add(
                    "{$base}/{$loc}/pages/{$pg->slug}",
                    optional($pg->updated_at)->toDateString(),
                    0.6,
                    'monthly'
                );
            }
        });

    $xml = view('sitemap.xml', ['entries' => array_values($entries)])->render();

    return \Illuminate\Support\Facades\Response::make($xml, 200, [
        'Content-Type'  => 'application/xml; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600',
    ]);
});

Route::redirect('/', '/en', 301);

Route::get('/robots.txt', function () {
    $appUrl  = rtrim(config('app.url'), '/');
    $isProd  = app()->environment('production');

    $content = "User-agent: *\n";
    $content .= $isProd ? "Allow: /\n" : "Disallow: /\n";
    $content .= "\n";
    $content .= "# Filament Admin - block crawlers\n";
    $content .= "Disallow: /adminhmt/\n";
    $content .= "Disallow: /editor/\n";
    $content .= "\n";
    $content .= "Sitemap: {$appUrl}/sitemap.xml\n";

    return response($content, 200, ['Content-Type' => 'text/plain']);
});

Route::prefix('{locale}')
    ->whereIn('locale', config('locales.supported'))
    ->middleware(['customer.status'])
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');

        Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
        Route::get('/news', [NewsController::class, 'index'])->name('news.index');
        Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

        Route::get('/collaboration', [CollaborationController::class, 'index'])->name('collaboration.index');
        Route::get('/collaboration/apply', [CollaborationController::class, 'create'])->name('collaboration.create');
        Route::post('/collaboration/apply', [CollaborationController::class, 'store'])->name('collaboration.store');

        Route::get('/inquiry', [InquiryController::class, 'create'])->name('inquiry.create');
        Route::post('/inquiry', [InquiryController::class, 'store'])->name('inquiry.store');

        Route::get('/market', [MarketController::class, 'index'])->name('market.index');
        Route::get('/market/data', [MarketController::class, 'data'])->name('market.data');

        Route::get('/industries', [IndustryController::class, 'index'])->name('industries.index');
        Route::get('/industries/{industry:slug}', [IndustryController::class, 'show'])->name('industries.show');

        Route::get('/search', [SearchController::class, 'index'])
            ->middleware('search.throttle')
            ->name('search.index');

        Route::get('/register', [RegisterController::class, 'step1'])->name('register.step1');
        Route::post('/register/step1', [RegisterController::class, 'postStep1'])->name('register.step1.post');
        Route::get('/register/step2', [RegisterController::class, 'step2'])->name('register.step2');
        Route::post('/register/step2', [RegisterController::class, 'postStep2'])->name('register.step2.post');
        Route::get('/register/success', [RegisterController::class, 'success'])->name('register.success');

        Route::get('/login', [ProductAuthController::class, 'show'])->name('product.login');
        Route::post('/login', [ProductAuthController::class, 'login'])->name('product.login.post');
        Route::post('/logout', [ProductAuthController::class, 'logout'])->name('product.logout');

        Route::get('/reset-password/{token}', [PasswordResetController::class, 'show'])
            ->name('password.reset');

        Route::post('/reset-password', [PasswordResetController::class, 'update'])
            ->name('password.update');

        Route::get('/reset-password', function (string $locale) {
            // If someone opens /en/reset-password directly, send them to login
            return redirect("/{$locale}/login");
        })->name('password.reset.landing');

        // Cookie Consent (API-style endpoints, locale-prefixed)
        Route::post('/cookie-consent', [CookieConsentController::class, 'store'])
            ->name('cookie.consent.store');
        Route::get('/cookie-consent/payload', [CookieConsentController::class, 'payload'])
            ->name('cookie.consent.payload');
    });