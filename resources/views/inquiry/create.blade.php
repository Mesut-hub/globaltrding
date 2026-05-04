@extends('layouts.app')

@section('meta_title', 'Inquiry - Globaltrding')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="text-4xl font-semibold tracking-tight">Inquiry</h1>
        <p class="mt-3 text-slate-600">
            Please provide your company details and inquiry message. Our team will respond.
        </p>

        @if (session('success'))
            <div class="mt-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-green-900">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="/{{ app()->getLocale() }}/inquiry" class="mt-8 space-y-4">
            @csrf

            <div>
                <label class="text-sm font-medium">Full name *</label>
                <input name="full_name" value="{{ old('full_name') }}"
                       class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" required>
                @error('full_name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium">Email *</label>
                <input name="email" type="email" value="{{ old('email') }}"
                       class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" required>
                @error('email') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium">Company *</label>
                <input name="company" value="{{ old('company') }}"
                       class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" required>
                @error('company') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium">Phone *</label>
                <input name="phone" value="{{ old('phone') }}"
                       class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" required>
                @error('phone') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium">Subject *</label>
                <input name="subject" value="{{ old('subject') }}"
                       class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" required>
                @error('subject') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium">Message *</label>
                <textarea name="message" rows="7"
                          class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" required>{{ old('message') }}</textarea>
                @error('message') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <button class="rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                Submit
            </button>
        </form>
    </section>
@endsection