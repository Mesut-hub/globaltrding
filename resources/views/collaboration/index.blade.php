@extends('layouts.app')

@section('meta_title', __('collaboration.meta_title'))

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-14">
        <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-7">
                <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight">
                    {{ __('collaboration.title') }}
                </h1>
                <p class="mt-4 text-lg text-slate-600">
                    {{ __('collaboration.subtitle') }}
                </p>

                @if (session('success'))
                    <div class="mt-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-green-900">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="/{{ app()->getLocale() }}/collaboration/apply"
                       class="rounded-md bg-slate-900 px-5 py-3 text-white font-medium hover:bg-slate-800">
                        {{ __('collaboration.start') }}
                    </a>
                    <a href="/{{ app()->getLocale() }}/inquiry"
                       class="rounded-md border border-slate-300 px-5 py-3 text-slate-900 font-medium hover:bg-slate-50">
                        {{ __('collaboration.send') }}
                    </a>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="text-sm font-semibold text-slate-900">{{ __('collaboration.expect') }}</div>
                    <ul class="mt-4 space-y-3 text-slate-600">
                        <li>• {{ __('collaboration.dedicated') }}</li>
                        <li>• {{ __('collaboration.compliance') }}</li>
                        <li>• {{ __('collaboration.multilingual') }}</li>
                        <li>• {{ __('collaboration.reliable') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-14">
            <h2 class="text-2xl font-semibold tracking-tight">{{ __('collaboration.why') }}</h2>

            <div class="mt-8 grid gap-6 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="font-semibold">{{ __('collaboration.sourcing') }}</div>
                    <p class="mt-2 text-slate-600">
                        {{ __('collaboration.access') }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="font-semibold">{{ __('collaboration.operational') }}</div>
                    <p class="mt-2 text-slate-600">
                        {{ __('collaboration.timelines') }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="font-semibold">{{ __('collaboration.partnership') }}</div>
                    <p class="mt-2 text-slate-600">
                        {{ __('collaboration.approach') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14">
            <h2 class="text-2xl font-semibold tracking-tight">{{ __('collaboration.process') }}</h2>

            <div class="mt-8 grid gap-6 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 p-6">
                    <div class="text-sm font-semibold text-slate-900">{{ __('collaboration.Step 1') }}</div>
                    <div class="mt-2 font-semibold">{{ __('collaboration.submit') }}</div>
                    <p class="mt-2 text-slate-600">{{ __('collaboration.share') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-6">
                    <div class="text-sm font-semibold text-slate-900">{{ __('collaboration.Step 2') }}</div>
                    <div class="mt-2 font-semibold">{{ __('collaboration.review') }}</div>
                    <p class="mt-2 text-slate-600">{{ __('collaboration.team') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-6">
                    <div class="text-sm font-semibold text-slate-900">{{ __('collaboration.Step 3') }}</div>
                    <div class="mt-2 font-semibold">{{ __('collaboration.commercial') }}</div>
                    <p class="mt-2 text-slate-600">{{ __('collaboration.scope') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-6">
                    <div class="text-sm font-semibold text-slate-900">{{ __('collaboration.Step 4') }}</div>
                    <div class="mt-2 font-semibold">{{ __('collaboration.execution') }}</div>
                    <p class="mt-2 text-slate-600">{{ __('collaboration.build') }}</p>
                </div>
            </div>

            <div class="mt-10">
                <a href="/{{ app()->getLocale() }}/collaboration/apply"
                   class="inline-flex items-center rounded-md bg-slate-900 px-5 py-3 text-white font-medium hover:bg-slate-800">
                    {{ __('collaboration.form') }}
                </a>
            </div>
        </div>
    </section>
@endsection