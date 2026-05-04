@extends('layouts.app')

@section('meta_title', 'Collaboration - Globaltrding')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-14">
        <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-7">
                <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight">
                    Collaboration with Globaltrding
                </h1>
                <p class="mt-4 text-lg text-slate-600">
                    We build long-term partnerships for industrial equipment and raw materials supply,
                    with transparent processes, compliance-first sourcing, and a global network.
                </p>

                @if (session('success'))
                    <div class="mt-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-green-900">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="/{{ app()->getLocale() }}/collaboration/apply"
                       class="rounded-md bg-slate-900 px-5 py-3 text-white font-medium hover:bg-slate-800">
                        Start collaboration
                    </a>
                    <a href="/{{ app()->getLocale() }}/inquiry"
                       class="rounded-md border border-slate-300 px-5 py-3 text-slate-900 font-medium hover:bg-slate-50">
                        Send an inquiry
                    </a>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="text-sm font-semibold text-slate-900">What you can expect</div>
                    <ul class="mt-4 space-y-3 text-slate-600">
                        <li>• Dedicated coordination and fast response time</li>
                        <li>• Compliance-minded sourcing and documentation</li>
                        <li>• Multilingual communication and global reach</li>
                        <li>• Reliable delivery planning & risk mitigation</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-14">
            <h2 class="text-2xl font-semibold tracking-tight">Why collaborate with us</h2>

            <div class="mt-8 grid gap-6 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="font-semibold">Global sourcing</div>
                    <p class="mt-2 text-slate-600">
                        Access a broad supplier network with structured validation and continuous improvement.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="font-semibold">Operational reliability</div>
                    <p class="mt-2 text-slate-600">
                        Clear timelines, practical documentation support, and consistent communication.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="font-semibold">Partnership mindset</div>
                    <p class="mt-2 text-slate-600">
                        Long-term collaboration approach aligned with quality, safety, and reputation.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14">
            <h2 class="text-2xl font-semibold tracking-tight">Process</h2>

            <div class="mt-8 grid gap-6 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 p-6">
                    <div class="text-sm font-semibold text-slate-900">Step 1</div>
                    <div class="mt-2 font-semibold">Submit request</div>
                    <p class="mt-2 text-slate-600">Share company details and collaboration intent.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-6">
                    <div class="text-sm font-semibold text-slate-900">Step 2</div>
                    <div class="mt-2 font-semibold">Review</div>
                    <p class="mt-2 text-slate-600">Our team reviews and schedules the next steps.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-6">
                    <div class="text-sm font-semibold text-slate-900">Step 3</div>
                    <div class="mt-2 font-semibold">Commercial alignment</div>
                    <p class="mt-2 text-slate-600">Scope, terms, documentation, and delivery plan.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 p-6">
                    <div class="text-sm font-semibold text-slate-900">Step 4</div>
                    <div class="mt-2 font-semibold">Execution</div>
                    <p class="mt-2 text-slate-600">Build a long-term partnership and scale up.</p>
                </div>
            </div>

            <div class="mt-10">
                <a href="/{{ app()->getLocale() }}/collaboration/apply"
                   class="inline-flex items-center rounded-md bg-slate-900 px-5 py-3 text-white font-medium hover:bg-slate-800">
                    Go to collaboration form →
                </a>
            </div>
        </div>
    </section>
@endsection