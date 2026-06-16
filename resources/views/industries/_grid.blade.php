@php
    $pageTitle    = data_get($page?->title, $locale)
                 ?: data_get($page?->title, $fallback)
                 ?: __('industries.meta_title');

    $pageSubtitle = data_get($page?->content, $locale)
                 ?: data_get($page?->content, $fallback)
                 ?: __('industries.subtitle');
@endphp

<section class="mx-auto max-w-7xl px-4 py-12">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-4xl font-semibold tracking-tight">{{ $pageTitle }}</h1>
            @if ($pageSubtitle)
                <p class="mt-2 text-slate-600">{{ $pageSubtitle }}</p>
            @endif
        </div>
        <a href="/{{ $locale }}/products" class="text-sm text-slate-600 hover:underline">
            {{ __('products.finder_title') }} →
        </a>
    </div>

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($industries as $ind)
            @php
                $indTitle = data_get($ind->title, $locale) ?: data_get($ind->title, $fallback) ?: $ind->slug;
                $indImg   = $ind->cover_image_path
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($ind->cover_image_path)
                    : null;
            @endphp
            <a href="/{{ $locale }}/industries/{{ $ind->slug }}"
               class="group rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 hover:shadow-sm transition">
                <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                    @if ($indImg)
                        <img src="{{ $indImg }}" alt="{{ $indTitle }}"
                             class="h-full w-full object-cover group-hover:scale-[1.015] transition" />
                    @endif
                </div>
                <div class="p-4">
                    <div class="text-2xl font-light tracking-tight">{{ $indTitle }}</div>
                    <div class="mt-2 text-sm text-slate-700 group-hover:underline">
                        {{ __('ui.discover_more') }} →
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>