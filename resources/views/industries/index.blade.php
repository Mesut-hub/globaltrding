@extends('layouts.app')

@php
    $locale      = app()->getLocale();
    $fallback    = config('locales.default', 'en');
    $metaTitle   = data_get($page?->title,   $locale) ?: data_get($page?->title,   $fallback) ?: __('industries.meta_title');
    $metaDesc    = data_get($page?->content, $locale) ?: data_get($page?->content, $fallback) ?: __('industries.meta_description');
    $seoTitle    = data_get($page?->seo, "title.{$locale}")       ?: data_get($page?->seo, "title.{$fallback}")       ?: $metaTitle;
    $seoDesc     = data_get($page?->seo, "description.{$locale}") ?: data_get($page?->seo, "description.{$fallback}") ?: $metaDesc;
@endphp

@section('meta_title',       $seoTitle)
@section('meta_description', $seoDesc)
@section('og_type',          'website')
@section('og_title',         $seoTitle)
@section('og_description',   $seoDesc)

@push('structured_data')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',
             'item' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}"],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $metaTitle,
             'item' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}/industries"],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    @if ($page && ! empty($page->blocks))
        @php $hasGrid = collect($page->blocks)->contains(fn ($b) => ($b['type'] ?? '') === 'industries_grid'); @endphp

        @foreach ($page->blocks as $block)
            @if (($block['type'] ?? '') === 'industries_grid')
                @include('industries._grid', compact('industries', 'locale', 'fallback', 'page'))
            @else
                @include('shared.blocks.render', ['block' => $block])
            @endif
        @endforeach

        @if (! $hasGrid)
            @include('industries._grid', compact('industries', 'locale', 'fallback', 'page'))
        @endif
    @else
        @include('industries._grid', compact('industries', 'locale', 'fallback', 'page'))
    @endif
@endsection