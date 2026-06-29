@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');

    // Unified title resolution — seo.title wins, then page title
    $title = data_get($page->seo, "title.{$locale}")
        ?: data_get($page->seo, "title.{$fallback}")
        ?: data_get($page->title, $locale)
        ?: data_get($page->title, $fallback)
        ?: $page->slug;

    // SEO
    $metaTitle = $title;

    $metaDescription = data_get($page->seo, "description.{$locale}")
        ?: data_get($page->seo, "description.{$fallback}")
        ?: '';

    $content = data_get($page->content, $locale) ?: data_get($page->content, $fallback) ?: null;
    $blocks = is_array($page->blocks ?? null) ? $page->blocks : [];

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

        {{-- Render remaining blocks — full-width types break out of container --}}
    @php
        $fullWidthBlocks = ['fullWidthCards', 'insightsGrid', 'market_belt'];
    @endphp

    @foreach (array_slice($blocks, 1) as $block)
        @php $bType = $block['type'] ?? ''; @endphp
        @if (in_array($bType, $fullWidthBlocks))
            @include('shared.blocks.render', ['block' => $block])
        @else
            <div class="mx-auto max-w-7xl px-4">
                @include('shared.blocks.render', ['block' => $block])
            </div>
        @endif
    @endforeach

    @if (count($blocks) <= 1)
        <div class="mx-auto max-w-7xl px-4 py-12">
            @if (is_array($content))
                @foreach ($content as $block)
                    @include('shared.blocks.render', ['block' => $block])
                @endforeach
            @elseif (is_string($content) && trim($content) !== '')
                <div class="prose prose-slate max-w-none">
                    {!! nl2br(e($content)) !!}
                </div>
            @endif
        </div>
    @endif

    @else
        {{-- Normal page (no hero header) --}}
        <div class="mx-auto max-w-7xl px-4 py-12">
            <a href="/{{ $locale }}/" class="text-sm text-slate-600 hover:underline">← Back to Home</a>
            <h1 class="mt-4 text-4xl font-semibold tracking-tight">{{ $title }}</h1>
        </div>

        @php
            $fullWidthBlocks = ['fullWidthCards', 'insightsGrid', 'market_belt'];
        @endphp

        @if (is_array($blocks) && count($blocks))
            @foreach ($blocks as $block)
                @php $bType = $block['type'] ?? ''; @endphp
                @if (in_array($bType, $fullWidthBlocks))
                    @include('shared.blocks.render', ['block' => $block])
                @else
                    <div class="mx-auto max-w-7xl px-4 mt-8">
                        @include('shared.blocks.render', ['block' => $block])
                    </div>
                @endif
            @endforeach
        @elseif (is_array($content))
            <div class="mx-auto max-w-7xl px-4">
                @foreach ($content as $block)
                    @include('shared.blocks.render', ['block' => $block])
                @endforeach
            </div>
        @elseif (is_string($content) && trim($content) !== '')
            <div class="mx-auto max-w-7xl px-4">
                <div class="prose prose-slate max-w-none">
                    {!! nl2br(e($content)) !!}
                </div>
            </div>
        @else
            <div class="mx-auto max-w-7xl px-4">
                <div class="text-slate-600">This page has no content yet.</div>
            </div>
        @endif
    @endif
@endsection