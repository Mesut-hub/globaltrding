@extends('layouts.app')

@php
    $locale   = app()->getLocale();
    $fallback = config('locales.default', 'en');
    $title    = data_get($industry->title, $locale)
        ?: data_get($industry->title, $fallback)
        ?: $industry->slug;

    $metaTitle = data_get($industry->seo, "title.{$locale}")
        ?: data_get($industry->seo, "title.{$fallback}")
        ?: $title;
    
    $excerpt = data_get($industry->excerpt, $locale)
        ?: data_get($industry->excerpt, $fallback)
        ?: '';
    
        $metaDescription = data_get($industry->seo, "description.{$locale}")
        ?: data_get($industry->seo, "description.{$fallback}")
        ?: $excerpt;

    $ogImage = null;
    if (data_get($industry->seo, 'og_image')) {
        $ogImage = data_get($industry->seo, 'og_image');
    } elseif ($industry->cover_image_path) {
        $ogImage = \Illuminate\Support\Facades\Storage::disk('public')->url($industry->cover_image_path);
    }

    $img = $industry->cover_image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($industry->cover_image_path) : null;
    $metaDescription = $excerpt ?: 'Industries we serve with industrial equipment and sourcing expertise.';
@endphp

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('og_type', 'website')
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)
@if ($ogImage)
    @section('og_image', $ogImage)
@endif

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
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $title,
                'item' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}/industries/{$industry->slug}",
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@if ($img)
    @section('og_image', $img)
@endif

@section('content')
    {{-- Blocks renderer (MVP) --}}
    @foreach (($industry->blocks ?? []) as $block)
        @include('shared.blocks.render', ['block' => $block])
    @endforeach
    @push('structured_data')
        <script type="application/ld+json">
        {!! json_encode([
            '@context'    => 'https://schema.org',
            '@type'       => 'WebPage',
            '@id'         => rtrim(config('app.url', ''), '/') . "/{$locale}/industries/{$industry->slug}#webpage",
            'name'        => $metaTitle,
            'description' => $metaDescription,
            'url'         => rtrim(config('app.url', ''), '/') . "/{$locale}/industries/{$industry->slug}",
            'inLanguage'  => $locale,
            'isPartOf'    => ['@id' => rtrim(config('app.url', ''), '/') . '/#website'],
            'about'       => [
                '@type' => 'Thing',
                'name'  => $title,
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
@endsection