@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');

    $title = data_get($page->title, $locale) ?: data_get($page->title, $fallback) ?: $page->slug;

    // SEO
    $metaTitle = data_get($page->seo, "title.$locale")
        ?: data_get($page->seo, "title.$fallback")
        ?: $title;

    $metaDescription = data_get($page->seo, "description.$locale")
        ?: data_get($page->seo, "description.$fallback")
        ?: '';

    $content = data_get($page->content, $locale) ?: data_get($page->content, $fallback) ?: null;
    $blocks = $page->blocks ?? [];

    // detect if first block is hero
    $firstBlockType = is_array($blocks) && count($blocks) ? ($blocks[0]['type'] ?? null) : null;
    $hasHeroHeader = $firstBlockType === 'hero';
@endphp

@push('body_class')
    has-hero-header
@endpush

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)

@section('og_type', 'website')
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)

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
                'item' => rtrim((string) config('app.url'), '/') . "/{$locale}",
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $title,
                'item' => rtrim((string) config('app.url'), '/') . "/{$locale}/pages/{$page->slug}",
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    {{-- If first block is hero, render it full-width as header --}}
    @if ($hasHeroHeader)
        @php $hero = $blocks[0]; @endphp
        @include('shared.blocks.render', ['block' => $hero])

        {{-- Render remaining blocks as normal page sections --}}
        <section class="mx-auto max-w-7xl px-4 py-12">
            <div class="space-y-8">
                @foreach (array_slice($blocks, 1) as $block)
                    @include('shared.blocks.render', ['block' => $block])
                @endforeach

                {{-- fallback to legacy content if blocks are empty after hero --}}
                @if (count($blocks) <= 1)
                    @if (is_array($content))
                        @foreach ($content as $block)
                            @include('shared.blocks.render', ['block' => $block])
                        @endforeach
                    @elseif (is_string($content) && trim($content) !== '')
                        <div class="prose prose-slate max-w-none">
                            {!! nl2br(e($content)) !!}
                        </div>
                    @endif
                @endif
            </div>
        </section>

    @else
        {{-- Normal page (no hero header) --}}
        <section class="mx-auto max-w-7xl px-4 py-12">
            <a href="/{{ $locale }}/" class="text-sm text-slate-600 hover:underline">
                ← Back to Home
            </a>

            <h1 class="mt-4 text-4xl font-semibold tracking-tight">{{ $title }}</h1>

            <div class="mt-8 space-y-8">
                @if (is_array($blocks) && count($blocks))
                    @foreach ($blocks as $block)
                        @include('shared.blocks.render', ['block' => $block])
                    @endforeach
                @elseif (is_array($content))
                    @foreach ($content as $block)
                        @include('shared.blocks.render', ['block' => $block])
                    @endforeach
                @elseif (is_string($content) && trim($content) !== '')
                    <div class="prose prose-slate max-w-none">
                        {!! nl2br(e($content)) !!}
                    </div>
                @else
                    <div class="text-slate-600">
                        This page has no content yet.
                    </div>
                @endif
            </div>
        </section>
    @endif
@endsection