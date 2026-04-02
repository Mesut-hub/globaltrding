<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\NewsController;
use App\Models\NewsPost;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\HomeController;

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
        
        Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    });