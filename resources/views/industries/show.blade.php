@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');
    $title = data_get($industry->title, $locale) ?: data_get($industry->title, $fallback) ?: $industry->slug;
    $excerpt = data_get($industry->excerpt, $locale) ?: data_get($industry->excerpt, $fallback) ?: '';
    $img = $industry->cover_image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($industry->cover_image_path) : null;
    $metaDescription = $excerpt ?: 'Industries we serve with industrial equipment and sourcing expertise.';
@endphp

@section('meta_title', $title . ' - Industries')
@section('meta_description', $metaDescription)

@section('og_type', 'website')
@section('og_title', $title . ' - Industries')
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
    <section class="mx-auto max-w-7xl px-4 py-10">
        <a href="/{{ $locale }}/industries" class="text-sm text-slate-600 hover:underline">← All industries</a>

        <div class="mt-4 grid gap-8 lg:grid-cols-12 lg:items-start">
            <div class="lg:col-span-7">
                <h1 class="text-4xl font-semibold tracking-tight">{{ $title }}</h1>
                @if ($excerpt)
                    <p class="mt-3 text-slate-600 text-lg">{{ $excerpt }}</p>
                @endif
            </div>

            <div class="lg:col-span-5">
                @if ($img)
                    <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100">
                        <img src="{{ $img }}" alt="{{ $title }}" class="w-full h-auto object-cover" />
                    </div>
                @endif
            </div>
        </div>

        {{-- Blocks renderer (MVP) --}}
        <div class="mt-10 space-y-8">
            @foreach (($industry->blocks ?? []) as $block)
                @include('shared.blocks.render', ['block' => $block])
            @endforeach
        </div>
    </section>
@endsection