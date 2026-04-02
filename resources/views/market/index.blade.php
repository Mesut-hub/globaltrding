@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');

    $selectedName =
        $selected
            ? (is_array($selected->name)
                ? (data_get($selected->name, $locale) ?: data_get($selected->name, $fallback) ?: $selected->slug)
                : ((string) ($selected->name ?: $selected->slug)))
            : 'Market';

    $selectedSlug = $selected?->slug ?? '';
@endphp

@section('meta_title', $selectedName . ' - Market')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12"
             data-market-root
             data-locale="{{ $locale }}"
             data-belt-slugs="{{ $instruments->pluck('slug')->implode(',') }}">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-4xl font-semibold tracking-tight">Market</h1>
                <p class="mt-2 text-slate-600">
                    Select an instrument and time period to view changes.
                </p>
            </div>

            <a href="/{{ $locale }}/" class="text-sm text-slate-600 hover:underline">← Back to Home</a>
        </div>

        <div class="mt-8 grid gap-4 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <label class="text-sm font-medium text-slate-700">Instrument</label>

                    <select id="instrumentSelect" class="mt-2 w-full rounded-md border border-slate-300 px-3 py-2">
                        @foreach ($instruments as $inst)
                            @php
                                $label = is_array($inst->name)
                                    ? (data_get($inst->name, $locale) ?: data_get($inst->name, $fallback) ?: $inst->slug)
                                    : ((string) ($inst->name ?: $inst->slug));
                            @endphp
                            <option value="{{ $inst->slug }}" @selected($inst->slug === $selectedSlug)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <div class="mt-4">
                        <label class="text-sm font-medium text-slate-700">Period</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button data-period="1m" class="periodBtn rounded-md border border-slate-300 px-3 py-1.5 text-sm">1M</button>
                            <button data-period="3m" class="periodBtn rounded-md border border-slate-300 px-3 py-1.5 text-sm">3M</button>
                            <button data-period="1y" class="periodBtn rounded-md border border-slate-300 px-3 py-1.5 text-sm">1Y</button>
                            <button data-period="custom" class="periodBtn rounded-md border border-slate-300 px-3 py-1.5 text-sm">Custom</button>
                        </div>
                    </div>

                    <div id="customRange" class="mt-4 hidden">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs text-slate-600">From</label>
                                <input id="fromDate" type="date" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="text-xs text-slate-600">To</label>
                                <input id="toDate" type="date" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm" />
                            </div>
                        </div>

                        <button id="applyCustom" class="mt-3 w-full rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                            Apply
                        </button>
                    </div>

                    <div class="mt-4 text-xs text-slate-500">
                        Data source: TCMB EVDS (will populate after sync).
                    </div>

                    <div class="mt-4 rounded-lg border border-slate-100 overflow-hidden" id="latestTable">
                        <div class="bg-slate-50 px-3 py-2 text-xs font-medium text-slate-600">Latest values</div>
                        <div class="divide-y divide-slate-100">
                            @foreach ($instruments as $inst)
                                @php
                                    $label = is_array($inst->name)
                                        ? (data_get($inst->name, $locale) ?: data_get($inst->name, $fallback) ?: $inst->slug)
                                        : ((string) ($inst->name ?: $inst->slug));
                                @endphp
                                <div class="px-3 py-2 text-sm flex items-center justify-between" data-latest-row="{{ $inst->slug }}">
                                    <div>
                                        <div class="font-medium text-slate-800">{{ $label }}</div>
                                        <div class="text-xs text-slate-500" data-latest-date>—</div>
                                    </div>
                                    <div class="text-right tabular-nums">
                                        <span data-latest-value>—</span>
                                        <span class="text-xs text-slate-500" data-latest-unit></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm text-slate-500" id="rangeText">—</div>
                            <div class="text-xl font-semibold" id="titleText">{{ $selectedName }}</div>
                        </div>

                        <div class="text-right">
                            <div class="text-sm text-slate-500">Latest</div>
                            <div class="text-xl font-semibold" id="latestText">—</div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <canvas id="marketChart" height="120"></canvas>
                    </div>

                    <div id="emptyHint" class="mt-4 hidden rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 text-sm">
                        No data found for this range. Run the sync command after adding your EVDS key, or insert points.
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection