@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');

    $name = data_get($product->name, $locale) ?: data_get($product->name, $fallback) ?: '';
    $summary = data_get($product->summary, $locale) ?: data_get($product->summary, $fallback) ?: '';
    $description = data_get($product->description, $locale) ?: data_get($product->description, $fallback) ?: '';

    $brandName = data_get($product->brand?->name, $locale) ?: data_get($product->brand?->name, $fallback) ?: '';

    $metaTitle = data_get($product->seo, "title.$locale")
        ?: data_get($product->seo, "title.$fallback")
        ?: $name;

    $metaDescription = data_get($product->seo, "description.$locale")
        ?: data_get($product->seo, "description.$fallback")
        ?: $summary;
@endphp

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12">
        <a href="/{{ $locale }}/products" class="text-sm text-slate-600 hover:underline">
            ← Back to Products
        </a>

        <div class="mt-4 text-xs text-slate-500">{{ $brandName }}</div>
        <h1 class="mt-2 text-4xl font-semibold tracking-tight">{{ $name }}</h1>

        @if ($summary)
            <p class="mt-4 text-slate-600 max-w-3xl">{{ $summary }}</p>
        @endif

        @if ($description)
            <div class="prose prose-slate mt-8 max-w-none">
                {!! nl2br(e($description)) !!}
            </div>
        @endif
    </section>
@endsection