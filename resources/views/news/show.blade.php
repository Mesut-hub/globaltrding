@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');

    $title = data_get($post->title, $locale) ?: data_get($post->title, $fallback) ?: '';
    $content = data_get($post->content, $locale) ?: data_get($post->content, $fallback) ?: '';

    $metaTitle = data_get($post->seo, "title.$locale")
        ?: data_get($post->seo, "title.$fallback")
        ?: $title;

    $metaDescription = data_get($post->seo, "description.$locale")
        ?: data_get($post->seo, "description.$fallback")
        ?: '';
@endphp

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)

@section('og_type', 'article')
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)
@section('og_image', rtrim(config('app.url', 'https://globaltrding.com'), '/') . '/images/logo.png')

@section('article_published_time', optional($post->published_at ?: $post->created_at)->toAtomString())
@section('article_modified_time', optional($post->updated_at)->toAtomString())

@push('structured_data')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}",
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'News',
                'item' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}/news",
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $title,
                'item' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}/news/{$post->slug}",
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $title,
        'description' => $metaDescription,
        'datePublished' => optional($post->published_at)->toAtomString(),
        'dateModified' => optional($post->updated_at)->toAtomString(),
        'mainEntityOfPage' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}/news/{$post->slug}",
        'author' => ['@type' => 'Organization', 'name' => 'Globaltrding'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Globaltrding',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . '/favicon.ico',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12">
        <a href="/{{ $locale }}/news" class="text-sm text-slate-600 hover:underline">
            ← Back to News
        </a>

        <h1 class="mt-4 text-4xl font-semibold tracking-tight">{{ $title }}</h1>

        @if ($post->published_at)
            <div class="mt-2 text-sm text-slate-500">
                {{ $post->published_at->format('Y-m-d') }}
            </div>
        @endif

        <div class="prose prose-slate mt-8 max-w-none">
            {!! nl2br(e($content)) !!}
        </div>
    </section>
@endsection