@extends('layouts.app')

@section('meta_title', 'Page not found - Globaltrding')
@section('meta_description', 'The page you requested could not be found.')

@section('content')
    @php $locale = app()->getLocale(); @endphp

    <section class="mx-auto max-w-7xl px-4 py-20">
        <h1 class="text-4xl font-semibold tracking-tight">404 — Page not found</h1>
        <p class="mt-4 text-slate-600 max-w-2xl">
            The page you’re looking for doesn’t exist or may have been moved.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="/{{ $locale }}/" class="rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                Go to Home
            </a>
            <a href="/{{ $locale }}/products" class="rounded-md border border-slate-300 px-4 py-2 hover:bg-slate-50">
                Browse Products
            </a>
            <a href="/{{ $locale }}/news" class="rounded-md border border-slate-300 px-4 py-2 hover:bg-slate-50">
                Latest News
            </a>
        </div>
    </section>
@endsection