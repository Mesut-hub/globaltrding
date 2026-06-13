@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');

    $title = data_get($page->title, $locale) ?: data_get($page->title, $fallback) ?: '';
    $content = data_get($page->content, $locale) ?: data_get($page->content, $fallback) ?: '';

    $metaTitle = data_get($page->seo, "title.$locale") ?: data_get($page->seo, "title.$fallback") ?: $title;
    $metaDescription = data_get($page->seo, "description.$locale") ?: data_get($page->seo, "description.$fallback") ?: '';
@endphp

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12">
        <h1 class="text-4xl font-semibold tracking-tight">{{ $title }}</h1>

        <div class="prose prose-slate mt-6 max-w-none">
            {!! nl2br(e($content)) !!}
        </div>
    </section>
@endsection