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

        $seoMeta = app(SeoService::class)->resolve($page, $locale, $fallback);

        if (
            ! empty($seoMeta['ogImage'])
            && ! str_starts_with($seoMeta['ogImage'], 'http')
        ) {
            $seoMeta['ogImage'] = Storage::disk('public')->url($seoMeta['ogImage']);
        }

        // ── ADD ONLY THIS BLOCK ──────────────────────────────────────
        $appUrl       = rtrim((string) config('app.url'), '/');
        $canonicalUrl = $seoMeta['canonical']
            ?? "{$appUrl}/{$locale}/pages/{$slug}";

        $titleArr = $page->title;
        $pageTitle = is_array($titleArr)
            ? ($titleArr[$locale] ?? $titleArr[$fallback] ?? $slug)
            : (string) ($titleArr ?? $slug);

        $seoMeta['schema'] = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => 'WebPage',
                    '@id'         => $canonicalUrl,
                    'name'        => $seoMeta['title'] ?? $pageTitle,
                    'description' => $seoMeta['description'] ?? '',
                    'url'         => $canonicalUrl,
                    'inLanguage'  => $locale,
                    'image'       => $seoMeta['ogImage'] ?? '',
                    'publisher'   => [
                        '@type' => 'Organization',
                        '@id'   => $appUrl . '/#organization',
                    ],
                ],
                [
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => [
                        [
                            '@type'    => 'ListItem',
                            'position' => 1,
                            'name'     => 'Home',
                            'item'     => "{$appUrl}/{$locale}",
                        ],
                        [
                            '@type'    => 'ListItem',
                            'position' => 2,
                            'name'     => $pageTitle,
                            'item'     => $canonicalUrl,
                        ],
                    ],
                ],
            ],
        ];
        // ── END OF ADDED BLOCK ───────────────────────────────────────

        return view('pages.show', [
            'page'    => $page,
            'seoMeta' => $seoMeta,
        ]);
    }
}