@php
    $type = $block['type'] ?? null;
    $data = $block['data'] ?? [];

    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');

    $t = function ($arr) use ($locale, $fallback) {
        if (!is_array($arr)) return (string) ($arr ?? '');
        return (string) ($arr[$locale] ?? $arr[$fallback] ?? (count($arr) ? reset($arr) : ''));
    };

    $urlWithLocale = function (?string $url) use ($locale) {
        $url = $url ?: '#';
        return str_replace('{locale}', $locale, $url);
    };

    $posterPath = $data['poster_path'] ?? null;
    $posterUrl = $posterPath ? Storage::disk('public')->url($posterPath) : null;
@endphp

{{-- =========================================================
    HERO (video OR image)
========================================================= --}}
@if ($type === 'hero')
    @php
        $minH = $data['min_h'] ?? '90vh';
        $title = $t($data['title'] ?? []);
        $subtitle = $t($data['subtitle'] ?? []);

        $cta1Label = $t($data['cta1_label'] ?? []);
        $cta1Url = $urlWithLocale($data['cta1_url'] ?? null);

        $cta2Label = $t($data['cta2_label'] ?? []);
        $cta2Url = $urlWithLocale($data['cta2_url'] ?? null);

        $mediaType = $data['media_type'] ?? 'video'; // video|image
        $mediaPath = $data['media_path'] ?? null;
        $mediaUrl = $mediaPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($mediaPath) : null;

        $overlayTop = (float) ($data['overlay_top'] ?? 0.45);
        $overlayMid = (float) ($data['overlay_mid'] ?? 0.15);
        $overlayBottom = (float) ($data['overlay_bottom'] ?? 0.55);

        $textOffset = (int) ($data['text_offset_px'] ?? 290); // pushes only text down
    @endphp

    <section class="relative text-white hero-shell" style="--hero-min-h: {{ $minH }};" data-hero>
        <div class="absolute inset-0 overflow-hidden bg-slate-950">
            @if ($mediaType === 'image')
                @if ($mediaUrl)
                    <img src="{{ $mediaUrl }}" class="h-full w-full object-cover" alt="">
                @elseif ($posterUrl)
                    <img src="{{ $posterUrl }}" class="h-full w-full object-cover" alt="">
                @endif
            @else
                @if ($mediaUrl)
                    <video class="h-full w-full object-cover opacity-80"
                        autoplay muted loop playsinline preload="metadata"
                        @if($posterUrl) poster="{{ $posterUrl }}" @endif>
                        <source src="{{ $mediaUrl }}" type="video/mp4">
                    </video>
                @endif

                @if ($posterUrl)
                    <img src="{{ $posterUrl }}"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover"
                        style="{{ $mediaUrl ? 'display:none;' : '' }}"
                        data-hero-poster>
                @endif
            @endif

            <div class="absolute inset-0"
                 style="background: linear-gradient(to bottom,
                    rgba(0,0,0,{{ $overlayTop }}),
                    rgba(0,0,0,{{ $overlayMid }}),
                    rgba(0,0,0,{{ $overlayBottom }}));"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:py-20"
             style="padding-top: {{ $textOffset }}px;">
            <div class="max-w-3xl">
                <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight leading-tight">
                    {{ $title }}
                </h1>

                @if ($subtitle)
                    <p class="mt-5 text-slate-200 text-lg">
                        {{ $subtitle }}
                    </p>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $cta1Url }}"
                       class="rounded-md bg-white px-5 py-2.5 text-slate-900 font-medium hover:bg-slate-100">
                        {{ $cta1Label ?: 'Discover more' }}
                    </a>

                    <a href="{{ $cta2Url }}"
                       class="rounded-md border border-white/30 px-5 py-2.5 font-medium hover:bg-white/10">
                        {{ $cta2Label ?: 'Contact' }}
                    </a>
                </div>
            </div>
        </div>

        <a href="/{{ $locale }}/collaboration"
           class="floating-mail"
           aria-label="Collaboration"
           title="Collaboration">✉</a>

        <style>
            @media (prefers-reduced-motion: reduce) {
                video { display: none; }
            }
        </style>
    </section>

{{-- =========================================================
    MARKET BELT
========================================================= --}}
@elseif ($type === 'market_belt')
    @php
        $beltSlugs = 'usd-try,eur-try,gbp-try';
        $dataUrl = "/{$locale}/market/data?instruments=" . urlencode($beltSlugs);
    @endphp


    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-3">
            <div class="flex flex-wrap items-center gap-2"
                data-market-belt
                data-market-url="{{ $dataUrl }}">

                @foreach (explode(',', $beltSlugs) as $slug)
                    <a href="/{{ $locale }}/market?instrument={{ $slug }}"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                    data-instrument="{{ $slug }}">
                        @php
                            $labels = [
                                'usd-try' => 'USD/TRY',
                                'eur-try' => 'EUR/TRY',
                                'gbp-try' => 'GBP/TRY',
                                'gold-gram-try' => 'Gold (g)',
                                'brent-usd' => 'Brent',
                            ];
                        @endphp

                        <span class="font-medium">{{ $labels[$slug] ?? $slug }}</span>
                        <span class="text-slate-900 tabular-nums" data-price>—</span>
                        <span class="text-xs" data-change></span>
                    </a>
                @endforeach

                <a href="/{{ $locale }}/market" class="ml-auto text-sm text-slate-600 hover:underline">
                    View market →
                </a>
            </div>
        </div>
    </section>


{{-- =========================================================
    INDUSTRIES SLIDER (from Industries CMS)
========================================================= --}}
@elseif ($type === 'industries_slider')
    @php
        $sectionTitle = $t($data['title'] ?? ['en' => 'Industries']);
        $viewAllUrl = $urlWithLocale($data['view_all_url'] ?? '/{locale}/industries');

        $industries = \App\Models\Industry::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->limit(12)
            ->get();
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-12" data-industry-slider>
        <div class="flex items-end justify-between gap-4">
            <h2 class="text-2xl font-semibold tracking-tight">{{ $sectionTitle }}</h2>

            <div class="flex items-center gap-3">
                <a href="{{ $viewAllUrl }}"
                   class="text-sm text-slate-600 hover:text-slate-900 hover:underline">
                    View all →
                </a>

                <button type="button" class="ind-btn" data-ind="prev" aria-label="Previous">‹</button>
                <button type="button" class="ind-btn" data-ind="next" aria-label="Next">›</button>
            </div>
        </div>

        <div class="mt-6 overflow-hidden">
            <div class="ind-track flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-2"
                 data-ind="track">
                @foreach ($industries as $ind)
                    @php
                        $title = data_get($ind->title, $locale) ?: data_get($ind->title, $fallback) ?: $ind->slug;
                        $img = $ind->cover_image_path
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($ind->cover_image_path)
                            : null;
                    @endphp

                    <a href="/{{ $locale }}/industries/{{ $ind->slug }}"
                       class="snap-start shrink-0 w-[85%] sm:w-[45%] lg:w-[28%] rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 hover:shadow-sm transition">
                        <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                            @if ($img)
                                <img src="{{ $img }}" alt="{{ $title }}"
                                     class="h-full w-full object-cover hover:scale-[1.015] transition" />
                            @endif
                        </div>
                        <div class="p-4">
                            <div class="text-xl font-light tracking-tight">{{ $title }}</div>
                            <div class="mt-2 text-sm text-slate-700 hover:underline">
                                Discover more →
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

{{-- =========================================================
    CTA SECTION
========================================================= --}}
@elseif ($type === 'cta')
    @php
        $title = $t($data['title'] ?? []);
        $text = $t($data['text'] ?? []);
        $btnLabel = $t($data['button_label'] ?? []);
        $btnUrl = $urlWithLocale($data['button_url'] ?? '#');
    @endphp

    <section class="mx-auto max-w-7xl px-4 pb-12">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 sm:p-10">
            <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-8">
                    <h2 class="text-2xl font-semibold tracking-tight">
                        {{ $title }}
                    </h2>
                    @if ($text)
                        <p class="mt-3 text-slate-600">
                            {{ $text }}
                        </p>
                    @endif
                </div>
                <div class="lg:col-span-4 flex lg:justify-end">
                    <a href="{{ $btnUrl }}"
                       class="inline-flex items-center justify-center rounded-md bg-slate-900 px-5 py-2.5 text-white font-medium hover:bg-slate-800">
                        {{ $btnLabel ?: 'Open' }}
                    </a>
                </div>
            </div>
        </div>
    </section>

{{-- =========================================================
    CARDS GRID (Insights/People/Sustainability etc.)
========================================================= --}}
@elseif ($type === 'cards')
    @php
        $title = $t($data['title'] ?? []);
        $items = $data['items'] ?? [];
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-12">
        @if ($title)
            <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>
        @endif

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($items as $item)
                @php
                    $itemTitle = $t($item['title'] ?? []);
                    $itemText = $t($item['text'] ?? []);
                    $itemUrl = $urlWithLocale($item['url'] ?? '#');
                    $imgPath = $item['image_path'] ?? null;
                    $imgUrl = $imgPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($imgPath) : null;
                @endphp

                <a href="{{ $itemUrl }}"
                   class="group rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 hover:shadow-sm transition">
                    <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                        @if ($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $itemTitle }}"
                                 class="h-full w-full object-cover group-hover:scale-[1.015] transition" />
                        @endif
                    </div>

                    <div class="p-4">
                        <div class="text-lg font-semibold leading-snug">{{ $itemTitle }}</div>
                        @if ($itemText)
                            <div class="mt-2 text-sm text-slate-600">{{ $itemText }}</div>
                        @endif
                        <div class="mt-3 text-sm text-slate-700 group-hover:underline">
                            Find out more →
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

{{-- =========================================================
    TRENDING TOPICS
========================================================= --}}
@elseif ($type === 'trending_topics')
    @php
        $title = $t($data['title'] ?? ['en' => 'Trending topics']);
        $topics = $data['topics'] ?? [];
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-12">
        <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>

        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($topics as $topic)
                @php
                    $label = $t($topic['label'] ?? []);
                    $url = $urlWithLocale($topic['url'] ?? '#');
                @endphp

                <a href="{{ $url }}"
                   class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 hover:border-slate-300 hover:bg-slate-50 transition">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </section>
@endif