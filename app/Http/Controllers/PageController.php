<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function show(Request $request, string $locale, string $slug)
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $fallback = config('locales.default', 'en');

        // Resolve full SEO metadata including OG image from the page's seo JSON
        $seoMeta = app(SeoService::class)->resolve($page, $locale, $fallback);

        // Convert storage-relative OG image path to a full public URL if needed
        if (
            ! empty($seoMeta['ogImage'])
            && ! str_starts_with($seoMeta['ogImage'], 'http')
        ) {
            $seoMeta['ogImage'] = Storage::disk('public')->url($seoMeta['ogImage']);
        }

        return view('pages.show', [
            'page'    => $page,
            'seoMeta' => $seoMeta,
        ]);
    }
}