<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request, string $locale)
    {
        $news = NewsPost::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10);

        $appUrl = rtrim((string) config('app.url'), '/');

        $seoMeta = [
            'title'       => 'News & Industry Updates | Global Trading Ltd. Co.',
            'description' => 'Latest news, technical articles, and supply chain insights from Global Trading Ltd. Co. — Istanbul industrial equipment and raw materials supplier.',
            'canonical'   => "{$appUrl}/{$locale}/news",
            'ogType'      => 'website',
            'robots'      => 'index,follow',
        ];

        return view('news.index', compact('news', 'seoMeta'));
    }

    public function show(Request $request, string $locale, string $slug)
    {
        $post = NewsPost::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $appUrl = rtrim((string) config('app.url'), '/');

        // Resolve title
        $titleArr = $post->title;
        $title = is_array($titleArr)
            ? ($titleArr[$locale] ?? $titleArr['en'] ?? $slug)
            : (string) ($titleArr ?? $slug);

        // Resolve SEO fields
        $seoCol  = is_array($post->seo) ? $post->seo : [];
        $seoTitle = $seoCol['title'][$locale]
            ?? $seoCol['title']['en']
            ?? $title . ' | Global Trading Ltd. Co.';
        $seoDesc  = $seoCol['description'][$locale]
            ?? $seoCol['description']['en']
            ?? '';

        // OG image
        $ogImage = !empty($post->cover_image_path)
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($post->cover_image_path)
            : $appUrl . '/images/og-default.png';

        $canonicalUrl = "{$appUrl}/{$locale}/news/{$slug}";

        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'NewsArticle',
            'headline'        => mb_substr($title, 0, 110),
            'description'     => $seoDesc,
            'url'             => $canonicalUrl,
            'image'           => $ogImage,
            'inLanguage'      => $locale,
            'datePublished'   => $post->published_at?->toIso8601String(),
            'dateModified'    => $post->updated_at?->toIso8601String(),
            'mainEntityOfPage'=> $canonicalUrl,
            'author'          => [
                '@type' => 'Organization',
                'name'  => 'Global Trading Ltd. Co.',
                'url'   => $appUrl,
            ],
            'publisher'       => [
                '@type' => 'Organization',
                'name'  => 'Global Trading Ltd. Co.',
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => $appUrl . '/images/logo.png',
                ],
            ],
        ];

        $seoMeta = [
            'title'         => mb_substr(html_entity_decode($seoTitle, ENT_QUOTES, 'UTF-8'), 0, 70),
            'description'   => mb_substr(html_entity_decode($seoDesc,  ENT_QUOTES, 'UTF-8'), 0, 160),
            'canonical'     => $canonicalUrl,
            'ogImage'       => $ogImage,
            'ogType'        => 'article',
            'robots'        => 'index,follow',
            'publishedTime' => $post->published_at?->toIso8601String(),
            'modifiedTime'  => $post->updated_at?->toIso8601String(),
            'schema'        => $schema,
        ];

        // $post still passed — view unchanged
        return view('news.show', compact('post', 'seoMeta'));
    }
}