<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use App\Models\Page;
use Illuminate\Http\Request;

class IndustryController extends Controller
{
    public function index(Request $request, string $locale)
    {
        $industries = Industry::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $page = Page::where('slug', 'industries')
            ->where('is_published', true)
            ->first();

        $appUrl = rtrim((string) config('app.url'), '/');

        $seoMeta = [
            'title'       => 'Industries | Global Trading Ltd. Co. — Istanbul',
            'description' => 'Industrial supply across oil & gas, petrochemical, boron minerals, glass, ceramics, agriculture, energy, solar, NDT, and security screening. 40+ countries.',
            'canonical'   => "{$appUrl}/{$locale}/industries",
            'ogType'      => 'website',
            'robots'      => 'index,follow',
        ];

        return view('industries.index', compact('industries', 'page', 'seoMeta'));
    }

    public function show(Request $request, string $locale, Industry $industry)
    {
        abort_unless($industry->is_published, 404);

        $appUrl = rtrim((string) config('app.url'), '/');

        // Resolve title for current locale
        $titleArr = $industry->title;
        $title = is_array($titleArr)
            ? ($titleArr[$locale] ?? $titleArr['en'] ?? '')
            : (string) ($titleArr ?? '');

        // Resolve SEO fields
        $seoCol  = is_array($industry->seo) ? $industry->seo : [];
        $seoTitle = $seoCol['title'][$locale]
            ?? $seoCol['title']['en']
            ?? $title . ' Industry | Global Trading Ltd. Co.';
        $seoDesc  = $seoCol['description'][$locale]
            ?? $seoCol['description']['en']
            ?? '';

        // OG image
        $ogImage = !empty($industry->cover_image_path)
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($industry->cover_image_path)
            : $appUrl . '/images/og-default.png';

        $canonicalUrl = "{$appUrl}/{$locale}/industries/{$industry->slug}";

        $schema = [
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'       => 'WebPage',
                    '@id'         => $canonicalUrl,
                    'name'        => $seoTitle,
                    'description' => $seoDesc,
                    'url'         => $canonicalUrl,
                    'inLanguage'  => $locale,
                    'image'       => $ogImage,
                    'publisher'   => [
                        '@type' => 'Organization',
                        '@id'   => $appUrl . '/#organization',
                    ],
                ],
                [
                    '@type'           => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type'=>'ListItem','position'=>1,'name'=>'Home',      'item'=>"{$appUrl}/{$locale}"],
                        ['@type'=>'ListItem','position'=>2,'name'=>'Industries','item'=>"{$appUrl}/{$locale}/industries"],
                        ['@type'=>'ListItem','position'=>3,'name'=>$title,      'item'=>$canonicalUrl],
                    ],
                ],
            ],
        ];

        $seoMeta = [
            'title'       => mb_substr(html_entity_decode($seoTitle, ENT_QUOTES, 'UTF-8'), 0, 70),
            'description' => mb_substr(html_entity_decode($seoDesc,  ENT_QUOTES, 'UTF-8'), 0, 160),
            'canonical'   => $canonicalUrl,
            'ogImage'     => $ogImage,
            'ogType'      => 'website',
            'robots'      => 'index,follow',
            'schema'      => $schema,
        ];

        // $industry still passed — view unchanged
        return view('industries.show', compact('industry', 'seoMeta'));
    }
}