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
@endphp

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)

@section('og_type', 'website')
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12">
        <a href="/{{ $locale }}/" class="text-sm text-slate-600 hover:underline">
            ← Back to Home
        </a>

        <h1 class="mt-4 text-4xl font-semibold tracking-tight">{{ $title }}</h1>

        {{-- Content renderer (MVP) --}}
        <div class="mt-8 space-y-8">
            @if (is_array($content))
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
@endsection