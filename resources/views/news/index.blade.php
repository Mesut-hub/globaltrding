@extends('layouts.app')

@section('meta_title', 'News - Globaltrding')
@section('meta_description', 'Latest updates and announcements from Globaltrding.')
@section('og_type', 'website')
@section('og_title', 'News - Globaltrding')
@section('og_description', 'Latest updates and announcements from Globaltrding.')

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
                'name' => 'News',
                'item' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . "/{$locale}/news",
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12">
        <h1 class="text-4xl font-semibold tracking-tight">News</h1>

        <div class="mt-8 space-y-6">
            @foreach ($news as $post)
                @php
                    $locale = app()->getLocale();
                    $fallback = config('locales.default', 'en');

                    $title = data_get($post->title, $locale) ?: data_get($post->title, $fallback) ?: '';
                    $excerpt = data_get($post->excerpt, $locale) ?: data_get($post->excerpt, $fallback) ?: '';
                @endphp

                <article class="border-b border-slate-200 pb-6">
                    <a class="text-xl font-semibold hover:underline"
                       href="/{{ $locale }}/news/{{ $post->slug }}">
                        {{ $title }}
                    </a>

                    @if ($post->published_at)
                        <div class="mt-1 text-sm text-slate-500">
                            {{ $post->published_at->format('Y-m-d') }}
                        </div>
                    @endif

                    @if ($excerpt)
                        <p class="mt-3 text-slate-600">{{ $excerpt }}</p>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $news->links() }}
        </div>
    </section>
@endsection