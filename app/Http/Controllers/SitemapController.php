<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Page;
use App\Models\NewsPost;
use App\Models\Industry;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $locales = config('locales.supported', ['en']);
        $baseUrl = rtrim((string) config('app.url'), '/');

        $pages = [];

        // Homepage for each locale
        foreach ($locales as $locale) {
            $pages[] = [
                'loc' => "{$baseUrl}/{$locale}",
                'changefreq' => 'weekly',
                'priority' => '1.0',
                'lastmod' => now()->toAtomString(),
            ];
        }

        // Products
        Product::where('is_published', true)
            ->select(['slug', 'updated_at'])
            ->chunk(500, function ($products) use ($baseUrl, $locales, &$pages) {
                foreach ($products as $product) {
                    foreach ($locales as $locale) {
                        $pages[] = [
                            'loc' => "{$baseUrl}/{$locale}/products/{$product->slug}",
                            'changefreq' => 'monthly',
                            'priority' => '0.8',
                            'lastmod' => $product->updated_at->toAtomString(),
                        ];
                    }
                }
            });

        // Pages
        Page::where('is_published', true)
            ->select(['slug', 'updated_at'])
            ->chunk(500, function ($pages_data) use ($baseUrl, $locales, &$pages) {
                foreach ($pages_data as $page) {
                    foreach ($locales as $locale) {
                        $pages[] = [
                            'loc' => "{$baseUrl}/{$locale}/pages/{$page->slug}",
                            'changefreq' => 'monthly',
                            'priority' => '0.7',
                            'lastmod' => $page->updated_at->toAtomString(),
                        ];
                    }
                }
            });

        // News
        NewsPost::where('is_published', true)
            ->select(['slug', 'updated_at'])
            ->orderByDesc('published_at')
            ->chunk(500, function ($news) use ($baseUrl, $locales, &$pages) {
                foreach ($news as $post) {
                    foreach ($locales as $locale) {
                        $pages[] = [
                            'loc' => "{$baseUrl}/{$locale}/news/{$post->slug}",
                            'changefreq' => 'monthly',
                            'priority' => '0.6',
                            'lastmod' => $post->updated_at->toAtomString(),
                        ];
                    }
                }
            });

        // Industries
        Industry::where('is_published', true)
            ->select(['slug', 'updated_at'])
            ->chunk(500, function ($industries) use ($baseUrl, $locales, &$pages) {
                foreach ($industries as $industry) {
                    foreach ($locales as $locale) {
                        $pages[] = [
                            'loc' => "{$baseUrl}/{$locale}/industries/{$industry->slug}",
                            'changefreq' => 'monthly',
                            'priority' => '0.7',
                            'lastmod' => $industry->updated_at->toAtomString(),
                        ];
                    }
                }
            });

        $xml = view('sitemap.xml', ['pages' => $pages]);
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}