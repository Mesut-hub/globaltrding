<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Response;
use App\Models\Product;
use App\Models\Industry;
use App\Models\NewsPost;
use App\Models\Page;

Route::get('/sitemap.xml', function () {
    $base = rtrim(config('app.url', 'https://globaltrding.com'), '/');
    $locales = config('locales.supported', ['en']);

    // Static routes (no lastmod)
    $staticPaths = [
        '' => '', // home
        'products' => 'products',
        'industries' => 'industries',
        'news' => 'news',
        'market' => 'market',
        'collaboration' => 'collaboration',
    ];

    // Collect entries as: ['loc' => url, 'lastmod' => 'YYYY-MM-DD'|null]
    $entries = [];

    foreach ($locales as $loc) {
        foreach ($staticPaths as $path) {
            $url = $path === '' ? "{$base}/{$loc}" : "{$base}/{$loc}/{$path}";
            $entries[] = ['loc' => $url, 'lastmod' => null];
        }
    }

    // Dynamic: Products
    foreach (Product::query()->where('is_published', true)->get(['slug', 'updated_at']) as $p) {
        foreach ($locales as $loc) {
            $entries[] = [
                'loc' => "{$base}/{$loc}/products/{$p->slug}",
                'lastmod' => optional($p->updated_at)->toDateString(),
            ];
        }
    }

    // Dynamic: Industries
    foreach (Industry::query()->where('is_published', true)->get(['slug', 'updated_at']) as $i) {
        foreach ($locales as $loc) {
            $entries[] = [
                'loc' => "{$base}/{$loc}/industries/{$i->slug}",
                'lastmod' => optional($i->updated_at)->toDateString(),
            ];
        }
    }

    // Dynamic: News
    foreach (NewsPost::query()->where('is_published', true)->get(['slug', 'updated_at']) as $n) {
        foreach ($locales as $loc) {
            $entries[] = [
                'loc' => "{$base}/{$loc}/news/{$n->slug}",
                'lastmod' => optional($n->updated_at)->toDateString(),
            ];
        }
    }

    // Dynamic: Pages (once you start creating them)
    foreach (Page::query()->where('is_published', true)->get(['slug', 'updated_at']) as $pg) {
        foreach ($locales as $loc) {
            $entries[] = [
                'loc' => "{$base}/{$loc}/pages/{$pg->slug}",
                'lastmod' => optional($pg->updated_at)->toDateString(),
            ];
        }
    }

    // De-dup by loc
    $byLoc = [];
    foreach ($entries as $e) {
        $byLoc[$e['loc']] = $e;
    }
    $entries = array_values($byLoc);

    $xml = view('sitemap.xml', ['entries' => $entries])->render();

    return Response::make($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
});

Route::redirect('/', '/en', 301);

Route::prefix('{locale}')
    ->whereIn('locale', config('locales.supported'))
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');

        Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
        Route::get('/news', [NewsController::class, 'index'])->name('news.index');
        Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

        Route::get('/collaboration', [CollaborationController::class, 'create'])->name('collaboration.create');
        Route::post('/collaboration', [CollaborationController::class, 'store'])->name('collaboration.store');

        Route::get('/market', [MarketController::class, 'index'])->name('market.index');
        Route::get('/market/data', [MarketController::class, 'data'])->name('market.data');

        Route::get('/industries', [IndustryController::class, 'index'])->name('industries.index');
        Route::get('/industries/{industry:slug}', [IndustryController::class, 'show'])->name('industries.show');

        Route::get('/search', [SearchController::class, 'index'])
            ->middleware('search.throttle')
            ->name('search.index');
    });