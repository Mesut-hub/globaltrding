@extends('layouts.app')

@section('meta_title', 'Products - Globaltrding')
@section('meta_description', 'Browse Globaltrding products and use Product Finder to discover the right industrial equipment for your needs.')
@section('og_type', 'website')
@section('og_title', 'Products - Globaltrding')
@section('og_description', 'Browse Globaltrding products and use Product Finder to discover the right industrial equipment for your needs.')

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
                'name' => 'Products',
                'item' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}/products",
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    @php
        $locale = app()->getLocale();
        $fallback = config('locales.default', 'en');

        $brandLabel = $brand
            ? (data_get($brand->name, $locale) ?: data_get($brand->name, $fallback) ?: $brand->slug)
            : null;
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-4xl font-semibold tracking-tight">Products</h1>
                @if ($brandLabel)
                    <p class="mt-2 text-slate-600">Filtered by brand: <span class="font-medium">{{ $brandLabel }}</span></p>
                @endif
            </div>
        </div>

        <form method="GET" action="/{{ $locale }}/products" class="mt-8 grid gap-3 md:grid-cols-12">
            <div class="md:col-span-5">
                <input
                    name="q"
                    value="{{ $q }}"
                    placeholder="Search products..."
                    class="w-full rounded-md border border-slate-300 px-3 py-2"
                />
            </div>

            <div class="md:col-span-5">
                <select name="brand" class="w-full rounded-md border border-slate-300 px-3 py-2">
                    <option value="">All brands</option>
                    @foreach ($brands as $b)
                        @php
                            $bLabel = data_get($b->name, $locale) ?: data_get($b->name, $fallback) ?: $b->slug;
                        @endphp
                        <option value="{{ $b->slug }}" @selected($brandSlug === $b->slug)>
                            {{ $bLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 flex gap-2">
                <button class="w-full rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                    Search
                </button>
            </div>
        </form>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($products as $product)
                @php
                    $name = data_get($product->name, $locale) ?: data_get($product->name, $fallback) ?: '';
                    $summary = data_get($product->summary, $locale) ?: data_get($product->summary, $fallback) ?: '';
                    $brandName = data_get($product->brand?->name, $locale) ?: data_get($product->brand?->name, $fallback) ?: '';
                @endphp

                <a href="/{{ $locale }}/products/{{ $product->slug }}"
                   class="rounded-xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-sm transition bg-white">
                    <div class="text-xs text-slate-500">{{ $brandName }}</div>
                    <div class="mt-2 text-lg font-semibold">{{ $name }}</div>

                    @if ($summary)
                        <div class="mt-2 text-sm text-slate-600">{{ $summary }}</div>
                    @endif
                </a>
            @empty
                <div class="text-slate-600 md:col-span-12">
                    No products found.
                </div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $products->links() }}
        </div>
    </section>
@endsection