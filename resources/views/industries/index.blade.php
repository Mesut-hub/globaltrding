@extends('layouts.app')

@section('meta_title', __('industries.meta_title'))
@section('meta_description', __('industries.meta_description'))
@section('og_type', 'website')
@section('og_title', 'Industries - Global Trading')
@section('og_description', 'Explore the industries Global Trading serves with industrial products sourcing and tailored solutions.')

@php $locale = app()->getLocale(); @endphp
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
                'name' => 'Industries',
                'item' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}/industries",
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    @php
        $locale   = app()->getLocale();
        $fallback = config('locales.default', 'en');
    @endphp

    @if ($page && ! empty($page->blocks))
        @php $hasGrid = collect($page->blocks)->contains(fn ($b) => ($b['type'] ?? '') === 'industries_grid'); @endphp

        @foreach ($page->blocks as $block)
            @if (($block['type'] ?? '') === 'industries_grid')
                @include('industries._grid', ['industries' => $industries, 'locale' => $locale, 'fallback' => $fallback])
            @else
                @include('shared.blocks.render', ['block' => $block])
            @endif
        @endforeach

        {{-- Safety net: if editor forgot to place the grid block, append it at the end --}}
        @if (! $hasGrid)
            @include('industries._grid', ['industries' => $industries, 'locale' => $locale, 'fallback' => $fallback])
        @endif
    @else
        {{-- No page record yet: render the grid directly (backwards compat) --}}
        @include('industries._grid', ['industries' => $industries, 'locale' => $locale, 'fallback' => $fallback])
    @endif
@endsection