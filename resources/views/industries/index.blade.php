@extends('layouts.app')

@section('meta_title', 'Industries - Globaltrding')
@section('meta_description', 'Explore the industries Globaltrding serves with industrial equipment sourcing and tailored solutions.')
@section('og_type', 'website')
@section('og_title', 'Industries - Globaltrding')
@section('og_description', 'Explore the industries Globaltrding serves with industrial equipment sourcing and tailored solutions.')

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
        $locale = app()->getLocale();
        $fallback = config('locales.default', 'en');
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-4xl font-semibold tracking-tight">Industries</h1>
                <p class="mt-2 text-slate-600">
                    Solutions for your industry. Explore our focus areas and sourcing capabilities.
                </p>
            </div>

            <a href="/{{ $locale }}/products" class="text-sm text-slate-600 hover:underline">
                Product search →
            </a>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($industries as $ind)
                @php
                    $title = data_get($ind->title, $locale) ?: data_get($ind->title, $fallback) ?: $ind->slug;
                    $img = $ind->cover_image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($ind->cover_image_path) : null;
                @endphp
                <a href="/{{ $locale }}/industries/{{ $ind->slug }}"
                   class="group rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 hover:shadow-sm transition">
                    <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                        @if ($img)
                            <img src="{{ $img }}"
                                 alt="{{ $title }}"
                                 class="h-full w-full object-cover group-hover:scale-[1.015] transition" />
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="text-2xl font-light tracking-tight">{{ $title }}</div>
                        <div class="mt-2 text-sm text-slate-700 group-hover:underline">
                            Discover more →
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endsection