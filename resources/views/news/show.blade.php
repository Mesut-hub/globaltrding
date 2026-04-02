@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');

    $title = data_get($post->title, $locale) ?: data_get($post->title, $fallback) ?: '';
    $content = data_get($post->content, $locale) ?: data_get($post->content, $fallback) ?: '';

    $metaTitle = data_get($post->seo, "title.$locale")
        ?: data_get($post->seo, "title.$fallback")
        ?: $title;

    $metaDescription = data_get($post->seo, "description.$locale")
        ?: data_get($post->seo, "description.$fallback")
        ?: '';
@endphp

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12">
        <a href="/{{ $locale }}/news" class="text-sm text-slate-600 hover:underline">
            ← Back to News
        </a>

        <h1 class="mt-4 text-4xl font-semibold tracking-tight">{{ $title }}</h1>

        @if ($post->published_at)
            <div class="mt-2 text-sm text-slate-500">
                {{ $post->published_at->format('Y-m-d') }}
            </div>
        @endif

        <div class="prose prose-slate mt-8 max-w-none">
            {!! nl2br(e($content)) !!}
        </div>
    </section>
@endsection