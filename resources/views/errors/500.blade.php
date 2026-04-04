@extends('layouts.app')

@section('meta_title', 'Server error - Globaltrding')
@section('meta_description', 'An unexpected error occurred.')

@section('content')
    @php $locale = app()->getLocale(); @endphp

    <section class="mx-auto max-w-7xl px-4 py-20">
        <h1 class="text-4xl font-semibold tracking-tight">500 — Something went wrong</h1>
        <p class="mt-4 text-slate-600 max-w-2xl">
            An unexpected error occurred while loading this page. Please try again in a moment.
        </p>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="/{{ $locale }}/" class="rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                Go to Home
            </a>
            <a href="javascript:location.reload()" class="rounded-md border border-slate-300 px-4 py-2 hover:bg-slate-50">
                Reload
            </a>
        </div>
    </section>
@endsection