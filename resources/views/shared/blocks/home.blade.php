@php
    $type = $block['type'] ?? null;
    $data = $block['data'] ?? [];
    $locale   = app()->getLocale();
    $fallback = config('locales.default', 'en');
    $t = function ($arr) use ($locale, $fallback) {
        if (!is_array($arr)) return (string)($arr ?? '');
        return (string)($arr[$locale] ?? $arr[$fallback] ?? (count($arr) ? reset($arr) : ''));
    };
    $urlWithLocale = function (?string $url) use ($locale) {
        return str_replace('{locale}', $locale, $url ?: '#');
    };
    $posterPath = $data['poster_path'] ?? null;
    $posterUrl  = $posterPath ? Storage::disk('public')->url($posterPath) : null;
    $th = function ($value, string $locale, string $fallback) use ($t): string {
            return $t($value, $locale, $fallback);
    };
@endphp

{{-- HERO --}}
@if ($type === 'hero')
    @php
        $height        = $data['height']           ?? 'screen';
        $pos           = $data['content_position'] ?? 'left';
        $align         = $data['content_align']    ?? 'left';
        $overlayColor  = $data['overlay_color']    ?? '#000000';
        $overlayOpacity = is_numeric($data['overlay_opacity'] ?? null)
                            ? max(0, min(1, (float) $data['overlay_opacity']))
                            : 0.45;

        $slides       = is_array($data['slides'] ?? null) ? $data['slides'] : [];
        $autoplay     = (bool) ($data['autoplay']       ?? true);
        $interval     = (int)  ($data['interval_ms']    ?? 4500);
        $pauseOnHover = (bool) ($data['pause_on_hover'] ?? true);

        $heightClass     = match($height) { 'xl' => 'gt-hero--xl', 'lg' => 'gt-hero--lg', default => 'gt-hero--screen' };
        $contentPosClass = match($pos)    { 'center' => 'gt-hero__content--center', 'right' => 'gt-hero__content--right', default => 'gt-hero__content--left' };
        $textAlignClass  = match($align)  { 'center' => 'text-center', 'right' => 'text-right', default => 'text-left' };

        $titleSize = $data['title_size'] ?? 'xl';
        $leadSize  = $data['lead_size']  ?? 'md';
        $maxW      = is_numeric($data['content_max_width']  ?? null) ? (int) $data['content_max_width']  : 760;
        $offX = is_numeric($data['content_offset_x'] ?? null) ? (int) $data['content_offset_x'] : 0;
        $offY = is_numeric($data['content_offset_y'] ?? null) ? (int) $data['content_offset_y'] : 0;

        $titleClass = match($titleSize) { 'md' => 'gt-hero__title--md', 'lg' => 'gt-hero__title--lg', default => 'gt-hero__title--xl' };
        $leadClass  = match($leadSize)  { 'sm' => 'gt-hero__lead--sm',  'lg' => 'gt-hero__lead--lg',  default => 'gt-hero__lead--md' };

        $s0         = $slides[0] ?? [];
        $heroKicker = $t($s0['kicker']    ?? '', $locale, $fallback);
        $title      = $t($s0['title']    ?? []);
        $subtitle   = $t($s0['lead'] ?? []);
        $cta1Label  = $t($s0['cta1_label'] ?? []);
        $cta1Url    = $urlWithLocale($s0['cta1_url'] ?? null);
        $cta2Label  = $t($s0['cta2_label'] ?? []);
        $cta2Url    = $urlWithLocale($s0['cta2_url'] ?? null);
        $mediaType  = $data['media_type'] ?? 'video';
        $videoPath  = $data['video'] ?? null;
        $videoUrl   = $videoPath ? Storage::disk('public')->url($videoPath) : null;
        $imageUrls  = collect(is_array($data['images'] ?? null) ? $data['images'] : [])
                    ->map(fn ($p) => $p ? Storage::disk('public')->url($p) : null)
                    ->filter()->values()->all();

        $slidesForJs = collect($slides)->map(fn ($s) => [
            'kicker'     => $t($s['kicker']     ?? []),
            'title'      => $t($s['title']      ?? []),
            'lead'       => $t($s['lead']       ?? []),
            'cta1_label' => $t($s['cta1_label'] ?? []),
            'cta1_url'   => $s['cta1_url'] ?? null,
            'cta2_label' => $t($s['cta2_label'] ?? []),
            'cta2_url'   => $s['cta2_url'] ?? null,
        ])->all();
    @endphp
    <section class="relative text-white hero-shell {{ $heightClass }}" 
            data-hero
            data-hero-autoplay="{{ $autoplay ? '1' : '0' }}"
            data-hero-interval="{{ $interval }}"
            data-hero-pause-hover="{{ $pauseOnHover ? '1' : '0' }}">
        <div class="absolute inset-0 overflow-hidden bg-slate-950">
            @if ($mediaType === 'video')
                <video class="h-full w-full object-cover" autoplay muted loop playsinline preload="metadata">
                    <source src="{{ $videoUrl }}">
                </video>
            @elseif ($mediaType === 'image' && count($imageUrls))
                <div class="gt-hero__slider" data-hero-slider>
                    @foreach ($imageUrls as $i => $u)
                        <div class="gt-hero__slide {{ $i === 0 ? 'is-active' : '' }}" data-hero-slide="{{ $i }}">
                            <img src="{{ $u }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                        </div>
                    @endforeach
                    @if (count($imageUrls) > 1)
                        <button type="button" class="gt-hero__nav gt-hero__nav--prev" data-hero-prev aria-label="Previous">‹</button>
                        <button type="button" class="gt-hero__nav gt-hero__nav--next" data-hero-next aria-label="Next">›</button>
                    @endif
                </div>
            @elseif ($mediaType === 'multimedia' && count($imageUrls))
                <div class="gt-hero__slider" data-hero-slider>
                    @php
                        // Construct a combined array of items to create sequential slides
                        $multimediaSlides = [];
                        
                        if (!empty($videoUrl)) {
                            $multimediaSlides[] = ['type' => 'video', 'url' => $videoUrl];
                        }
                        
                        foreach ($imageUrls as $imageUrl) {
                            $multimediaSlides[] = ['type' => 'image', 'url' => $imageUrl];
                        }
                    @endphp

                    @foreach ($multimediaSlides as $index => $slide)
                        <div class="gt-hero__slide {{ $index === 0 ? 'is-active' : '' }}" data-hero-slide="{{ $index }}">
                            @if ($slide['type'] === 'video')
                                <video class="h-full w-full object-cover" autoplay muted loop playsinline preload="metadata">
                                    <source src="{{ $slide['url'] }}" type="video/mp4">
                                </video>
                            @else
                                <img src="{{ $slide['url'] }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                            @endif
                        </div>
                    @endforeach

                    @if (count($multimediaSlides) > 1)
                        <button type="button" class="gt-hero__nav gt-hero__nav--prev" data-hero-prev aria-label="Previous">‹</button>
                        <button type="button" class="gt-hero__nav gt-hero__nav--next" data-hero-next aria-label="Next">›</button>
                    @endif
                </div>
            @else
                <div class="gt-hero__placeholder"></div>
            @endif
            <div class="gt-hero__overlay" style="background: {{ $overlayColor }}; opacity: {{ $overlayOpacity }};"></div>
            <div class="gt-hero__content {{ $contentPosClass }} {{ $textAlignClass }}"
                style="max-width: {{ $maxW }}px; transform: translate({{ $offX }}px, {{ $offY }}px);"
                data-hero-content
                data-hero-slides='@json($slidesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)'>

                @if ($heroKicker)
                    <div class="gt-hero__kicker" data-hero-kicker>{{ $heroKicker }}</div>
                @else
                    <div class="gt-hero__kicker hidden" data-hero-kicker></div>
                @endif
                <h1 class="{{ $titleClass }}" data-hero-title>{{ $title }}</h1>
                @if ($subtitle)<p class="{{ $leadClass }}" data-hero-lead>{{ $subtitle }}</p>@endif
                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($cta1Url && $cta1Label && $cta2Url && $cta2Label)
                        <a href="{{ $cta1Url }}" class="rounded-md bg-white px-5 py-2.5 text-slate-900 font-medium hover:bg-slate-100">{{ $cta1Label ?: 'Discover more' }}</a>
                        <a href="{{ $cta2Url }}" class="rounded-md border border-white/30 px-5 py-2.5 font-medium hover:bg-white/10">{{ $cta2Label ?: 'Contact' }}</a>
                    @elseif ($cta1Url && $cta1Label)
                        <a href="{{ $cta1Url }}" class="rounded-md bg-white px-5 py-2.5 text-slate-900 font-medium hover:bg-slate-100">{{ $cta1Label ?: 'Discover more' }}</a>
                    @else
                        <a href="#" class="gt-btn gt-btn-primary hidden" data-hero-cta></a>
                    @endif
                </div>
            </div>
        </div>
        <a href="/{{ $locale }}/collaboration" class="floating-mail" aria-label="Collaboration" title="Collaboration">✉</a>
        <style>@media (prefers-reduced-motion:reduce){video{display:none}}</style>
    </section>

{{-- MARKET BELT --}}
@elseif ($type === 'market_belt')
    @php $beltSlugs = 'usd-try,eur-try,gbp-try,gold-gram-try,brent-usd'; $dataUrl = "/{$locale}/market/data?instruments=".urlencode($beltSlugs); @endphp
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-3">
            <div class="flex flex-wrap items-center gap-2" data-market-belt data-market-url="{{ $dataUrl }}">
                @foreach (explode(',', $beltSlugs) as $slug)
                    @php $labels=['usd-try'=>'USD/TRY','eur-try'=>'EUR/TRY','gbp-try'=>'GBP/TRY','gold-gram-try'=>'Gold (g)','brent-usd'=>'Brent']; @endphp
                    <a href="/{{ $locale }}/market?instrument={{ $slug }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" data-instrument="{{ $slug }}">
                        <span class="font-medium">{{ $labels[$slug] ?? $slug }}</span>
                        <span class="text-slate-900 tabular-nums" data-price>—</span>
                        <span class="text-xs" data-change></span>
                    </a>
                @endforeach
                <a href="/{{ $locale }}/market" class="ml-auto text-sm text-slate-600 hover:underline">{{ __('market.view_market') }}</a>
            </div>
        </div>
    </section>

{{-- INDUSTRIES SLIDER --}}
@elseif ($type === 'industries_slider')
    @php
        $indKicker    = $t($data['kicker']         ?? '');
        $indHeading   = $t($data['heading_tabs']   ?? ($data['title'] ?? ['en' => 'Industries']));
        $indSubtitle  = $t($data['subtitle_tabs']  ?? '');
        $indInnerHead = $t($data['inner_heading']  ?? '');
        $viewAllUrl   = $urlWithLocale($data['view_all_url'] ?? '/{locale}/industries');

        $indBgPath    = $data['bg_image_path']     ?? null;
        $indBgUrl     = $indBgPath ? Storage::disk('public')->url($indBgPath) : null;
        $indBgOverlay = $data['bg_overlay_color']  ?? '#8B0000CC';
        $indCardBorder= $data['card_border_color'] ?? '#ffffff';
        $indCardOvDef = $data['card_overlay_color']?? '#00000099';
        $indNavColor  = $data['nav_btn_color']     ?? '#DAA520';
        $indAutoplay  = (bool) ($data['autoplay']    ?? true);
        $indDelay     = max(1000, (int) ($data['autoplay_ms'] ?? 4000));

        $industries = \App\Models\Industry::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->limit(12)
            ->get();

        $indId = 'ind_' . substr(md5(uniqid()), 0, 8);
    @endphp

    {{-- ── Section meta (kicker / heading / subtitle) ── --}}
    @if ($indKicker || $indHeading || $indSubtitle)
        <div class="mx-auto max-w-7xl px-4 py-6 mt-12">
            <div class="flex items-end justify-between gap-4">
                <div>
                    @if ($indKicker)
                        <div class="font-semibold uppercase tracking-widest text-slate-500 mb-2">
                            {{ $indKicker }}
                        </div>
                    @endif
                    @if ($indHeading)
                        <h2 class="font-light tracking-tight text-slate-800">
                            {{ $indHeading }}
                        </h2>
                    @endif
                    @if ($indSubtitle)
                        <p class="mt-3 text-slate-600">{!! $indSubtitle !!}</p>
                    @endif
                </div>
                <a href="{{ $viewAllUrl }}" class="text-sm text-slate-600 hover:underline whitespace-nowrap">
                    {{ __('ui.view_all') }} →
                </a>
            </div>
        </div>
    @endif

    @if ($indKicker || $indHeading || $indSubtitle)
    <section
        id="{{ $indId }}"
        class="w-full relative overflow-hidden select-none"
        style="{{ $indBgUrl
            ? "background-image:url('" . e($indBgUrl) . "');background-size:cover;background-position:center;"
            : 'background:#1a0000;' }}"
    >
    @else
    <section
        id="{{ $indId }}"
        class="w-full relative overflow-hidden select-none mt-12"
        style="{{ $indBgUrl
            ? "background-image:url('" . e($indBgUrl) . "');background-size:cover;background-position:center;"
            : 'background:#1a0000;' }}"
    >
    @endif
        {{-- Background overlay --}}
        <div class="absolute inset-0 pointer-events-none"
             style="background-color:{{ $indBgOverlay }};"></div>

        <div class="mx-auto max-w-7xl px-4 relative z-10 py-14">

            {{-- Inner heading --}}
            @if ($indInnerHead)
                <p class="text-center text-white font-extrabold text-sm tracking-[0.25em] uppercase mb-10 px-4">
                    {{ $indInnerHead }}
                </p>
            @endif

            @if (! $indKicker && ! $indHeading && ! $indSubtitle)
                <div class="flex items-center justify-end mb-6">
                    <a href="{{ $viewAllUrl }}" class="text-sm text-white/80 hover:text-white hover:underline">
                        {{ __('ui.view_all') }} →
                    </a>
                </div>
            @endif

            {{-- ── Carousel ── --}}
            <div class="relative" id="{{ $indId }}_wrap">

                {{-- Prev button --}}
                You're right — my last fix only nested a div but kept the buttons positioned at left-4/right-4, which sits inside the card row (overlapping the edge cards), not outside it. To fix both issues — buttons outside the cards, and vertically centered against the cards only (not the dots) — restructure it like this:
Replace this:
blade            {{-- ── Carousel ── --}}
            <div class="relative" id="{{ $indId }}_wrap">

                {{-- Prev button --}}
                <button
                    type="button"
                    id="{{ $indId }}_prev"
                    aria-label="Previous"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-30
                           w-14 h-14 rounded-full flex items-center justify-center
                           shadow-lg transition-all duration-300 cursor-pointer"
                    style="background:none;
                           border: 3px solid {{ $indNavColor }};
                           opacity:0; pointer-events:none;"
                >
                    <svg class="w-10 h-10 text-[rgb(218,165,32)]" fill="none" stroke="currentColor"
                         stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Next button --}}
                <button
                    type="button"
                    id="{{ $indId }}_next"
                    aria-label="Next"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-30
                           w-14 h-14 rounded-full flex items-center justify-center
                           shadow-lg transition-all duration-300 cursor-pointer"
                    style="background:none;
                           border: 3px solid {{ $indNavColor }};
                           opacity:0; pointer-events:none;"
                >
                    <svg class="w-10 h-10 text-[rgb(218,165,32)]" fill="none" stroke="currentColor"
                         stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                {{-- Track --}}
                <div class="overflow-hidden">
                    <div id="{{ $indId }}_track"
                         class="flex"
                         style="transition: transform 0.5s cubic-bezier(0.25,0.46,0.45,0.94); will-change: transform;">

                        @foreach ($industries as $ind)
                            @php
                                $iTitle   = $t($ind->title   ?? []) ?: $ind->slug;
                                $iSub     = $t($ind->excerpt ?? []);
                                $iImgUrl  = $ind->cover_image_path
                                    ? Storage::disk('public')->url($ind->cover_image_path)
                                    : null;
                                $iUrl     = $viewAllUrl && $ind->slug
                                    ? rtrim($viewAllUrl, '/') . '/' . $ind->slug
                                    : null;
                            @endphp

                            <div class="ind-slide flex-none w-1/4 px-3">
                                <a href="{{ $iUrl ?: '#' }}"
                                    class="ind-card relative block overflow-hidden"
                                    style="aspect-ratio:1/1;
                                           border: 2px solid {{ $indCardBorder }};"
                                >
                                    {{-- Card background image --}}
                                    @if ($iImgUrl)
                                        <img
                                            src="{{ $iImgUrl }}"
                                            alt="{{ $iTitle }}"
                                            class="absolute inset-0 w-full h-full object-cover
                                                   transition-transform duration-700 ind-card-img"
                                            loading="lazy"
                                        >
                                    @else
                                        <div class="absolute inset-0 bg-slate-900"></div>
                                    @endif

                                    {{-- Card overlay (fades on hover) --}}
                                    <div
                                        class="ind-card-overlay absolute inset-0
                                               transition-opacity duration-500"
                                        style="background-color:{{ $indCardOvDef }};"></div>

                                    {{-- Card text content --}}
                                    <div class="absolute inset-0 flex flex-col justify-end
                                                p-5 z-10 pointer-events-none">
                                        {{-- Gold accent line --}}
                                        <div class="w-8 h-1 bg-[rgb(218,165,32)] mb-3"></div>
                                        @if ($iTitle)
                                            <h3 class="text-white font-extrabold text-sm
                                                       uppercase tracking-wide leading-snug">
                                                {{ $iTitle }}
                                            </h3>
                                        @endif
                                        @if ($iSub)
                                            <p class="text-white/75 text-xs mt-1 leading-relaxed">
                                                {{ $iSub }}
                                            </p>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach

                    </div>{{-- /track --}}
                </div>{{-- /overflow-hidden --}}

                {{-- Dot indicators --}}
                <div id="{{ $indId }}_dots"
                    class="flex justify-center gap-2 mt-8 flex-wrap min-h-[20px]">
                </div>

            </div>{{-- /relative wrap --}}
        </div>{{-- /relative z-10 --}}
    </section>

    <script>
        (function () {
            'use strict';

            const id       = '{{ $indId }}';
            const wrap     = document.getElementById(id + '_wrap');
            const track    = document.getElementById(id + '_track');
            const prevBtn  = document.getElementById(id + '_prev');
            const nextBtn  = document.getElementById(id + '_next');
            const dotsWrap = document.getElementById(id + '_dots');
            const navColor = '{{ $indNavColor }}';
            const autoplay = {{ $indAutoplay ? 'true' : 'false' }};
            const delay    = {{ $indDelay }};

            if (!track) return;

            const slides = Array.from(track.querySelectorAll('.ind-slide'));
            const total  = slides.length;
            if (total === 0) return;

            let current = 0;
            let timer   = null;
            let dots    = [];

            // ── Responsive visible count ──────────────────────────────────
            function visibleCount() {
                const w = window.innerWidth;
                if (w < 480)  return 1;
                if (w < 768)  return 2;
                if (w < 1024) return 3;
                return 4;
            }

            function maxIdx() {
                return Math.max(0, total - visibleCount());
            }

            // ── Set slide widths ──────────────────────────────────────────
            function setSizes() {
                const pct = 100 / visibleCount();
                slides.forEach(function (s) { s.style.width = pct + '%'; });
            }

            // ── Build dots dynamically based on position count ────────────
            function buildDots() {
                if (!dotsWrap) return;
                dotsWrap.innerHTML = '';
                dots = [];

                const count = maxIdx() + 1;
                if (count <= 1) return;

                for (let i = 0; i < count; i++) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'ind-dot w-3 h-3 rounded-full transition-all duration-300';
                    btn.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                    btn.style.background = 'rgba(255,255,255,0.4)';
                    btn.addEventListener('click', function () {
                        stopTimer();
                        goTo(i);
                        startTimer();
                    });
                    dotsWrap.appendChild(btn);
                    dots.push(btn);
                }
            }

            // ── Navigate ──────────────────────────────────────────────────
            function goTo(idx) {
                current = Math.max(0, Math.min(idx, maxIdx()));
                const pct = current * (100 / visibleCount());
                track.style.transform = 'translateX(-' + pct + '%)';
                updateDots();
            }

            function next() { goTo(current >= maxIdx() ? 0 : current + 1); }
            function prev() { goTo(current <= 0 ? maxIdx() : current - 1); }

            // ── Dots ─────────────────────────────────────────────────────
            function updateDots() {
                dots.forEach(function (dot, i) {
                    if (i === current) {
                        dot.style.background = navColor;
                        dot.style.transform  = 'scale(1.25)';
                    } else {
                        dot.style.background = 'rgba(255,255,255,0.4)';
                        dot.style.transform  = 'scale(1)';
                    }
                });
            }

            // ── Auto-play ────────────────────────────────────────────────
            function startTimer() {
                if (!autoplay) return;
                clearInterval(timer);
                timer = setInterval(next, delay);
            }
            function stopTimer() { clearInterval(timer); }

            // ── Button visibility on section hover ───────────────────────
            function showNav() {
                [prevBtn, nextBtn].forEach(function (btn) {
                    if (!btn) return;
                    btn.style.opacity       = '1';
                    btn.style.pointerEvents = 'auto';
                });
            }
            function hideNav() {
                [prevBtn, nextBtn].forEach(function (btn) {
                    if (!btn) return;
                    btn.style.opacity       = '0';
                    btn.style.pointerEvents = 'none';
                });
            }

            // ── Card overlay: hide on card hover ─────────────────────────
            track.querySelectorAll('.ind-card').forEach(function (card) {
                const overlay = card.querySelector('.ind-card-overlay');
                const img     = card.querySelector('.ind-card-img');

                card.addEventListener('mouseenter', function () {
                    if (overlay) overlay.style.opacity = '0';
                    if (img)     img.style.transform   = 'scale(1.05)';
                });
                card.addEventListener('mouseleave', function () {
                    if (overlay) overlay.style.opacity = '1';
                    if (img)     img.style.transform   = 'scale(1)';
                });
            });

            // ── Event listeners ──────────────────────────────────────────
            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    stopTimer(); prev(); startTimer();
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    stopTimer(); next(); startTimer();
                });
            }

            if (wrap) {
                wrap.addEventListener('mouseenter', function () { showNav(); stopTimer(); });
                wrap.addEventListener('mouseleave', function () { hideNav(); startTimer(); });
            }

            // ── Touch / swipe ────────────────────────────────────────────
            let touchStartX = 0;
            track.addEventListener('touchstart', function (e) {
                touchStartX = e.touches[0].clientX;
            }, { passive: true });
            track.addEventListener('touchend', function (e) {
                const dx = touchStartX - e.changedTouches[0].clientX;
                if (Math.abs(dx) > 40) {
                    stopTimer();
                    dx > 0 ? next() : prev();
                    startTimer();
                }
            }, { passive: true });

            // ── Resize: rebuild dots + recalculate ───────────────────────
            let resizeTimer = null;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    setSizes();
                    buildDots();
                    goTo(Math.min(current, maxIdx()));
                }, 100);
            }, { passive: true });

            // ── Init ─────────────────────────────────────────────────────
            setSizes();
            buildDots();
            goTo(0);
            startTimer();
        })();
    </script>

{{-- CTA --}}
@elseif ($type === 'cta')
    @php $title=$t($data['title']??[]); $text=$t($data['text']??[]); $btnLabel=$t($data['button_label']??[]); $btnUrl=$urlWithLocale($data['button_url']??'#'); @endphp
    <section class="mx-auto max-w-7xl px-4 pb-12 py-12">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 sm:p-10">
            <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-8">
                    <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>
                    @if ($text)<p class="mt-3 text-slate-600">{{ $text }}</p>@endif
                </div>
                <div class="lg:col-span-4 flex lg:justify-end">
                    <a href="{{ $btnUrl }}" class="inline-flex items-center justify-center rounded-md bg-slate-900 px-5 py-2.5 text-white font-medium hover:bg-slate-800">{{ $btnLabel ?: 'Open' }}</a>
                </div>
            </div>
        </div>
    </section>

@elseif ($type === 'insightsGrid')
    @include('shared.blocks.render', ['block' => $block])
{{-- CARDS GRID --}}
@elseif ($type === 'cards')
    @php 
        $title=$t($data['title'] ?? '', $locale, $fallback); 
        $items=$data['items']??[]; 
        $viewAllUrl   = $urlWithLocale($data['view_all_url'] ?? '/{locale}');
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12" data-industry-slider>
        <div class="flex items-end justify-between gap-4">
            @if ($title)<h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>@endif
            <div class="flex items-center gap-3">
                <a href="{{ $viewAllUrl }}" class="text-sm text-slate-600 hover:underline">{{ __('ui.view_all') }} →</a>
            </div>
        </div>
        <div class="mt-6 overflow-hidden">
            <div class="flex gap-4 overflow-x-auto overflow-x-hidden snap-x snap-mandatory scroll-smooth pb-2" data-ind="track">
                @foreach ($items as $item)
                    @php
                        $imgpath = $item['image'] ?? null;
                        $imgUrl = $imgpath ? \Illuminate\Support\Facades\Storage::disk('public')->url($imgpath) : null;
                        $iTitle=$t($item['title'] ?? '', $locale, $fallback);
                        $iText=$t($item['text'] ?? '', $locale, $fallback);
                        $mtHtml     = $t($item['body_html'] ?? '', $locale, $fallback);
                        $ctaLbl    = $t($item['cta_label'] ?? '', $locale, $fallback);
                        $ctaLabel  = $ctaLbl !== '' ? $ctaLbl : null;
                        $iUrl=$item['url'] ?? null;
                    @endphp
                    <div class="snap-start shrink-0 w-[85%] sm:w-[45%] lg:w-[32%] rounded-xl border border-slate-200 bg-white overflow-hidden hover:shadow-sm transition">
                        <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                            @if ($imgUrl)<img src="{{ $imgUrl }}" alt="{{ $iTitle }}" class="h-full w-full object-cover hover:scale-[1.015] transition"/>@endif
                        </div>
                        <div class="p-4">
                            <div class="mt-2 text-lg text-slate-600">{{ $iTitle }}</div>
                            @if ($iText)<div class="text-xl font-semibold leading-snug">{{ $iText }}</div>@endif
                            @if ($mtHtml)<div class="mt-3 prose prose-slate max-w-none">{!! $mtHtml !!}</div> @endif
                            <a href="{{ $iUrl }}" class="inline-flex items-center rounded-md px-4 py-2 text-slate-600 hover:text-black">{{ $ctaLabel }} →</a>
                        </div>
                    </div>
                @endforeach
                @if (count($items) > 3)
                    <button type="button" class="ind-btn ind-btn--prev" data-ind="prev" aria-label="Previous">‹</button>
                    <button type="button" class="ind-btn ind-btn--next" data-ind="next" aria-label="Next">›</button>
                @endif
            </div>
        </div>
    </section>

{{-- ═══════════════════════════════════════════════════════════════════════════
     TRENDING TOPICS
     ═══════════════════════════════════════════════════════════════════════════
--}}
@elseif ($type === 'trending_topics')
    @php

        $sectionTitle = $t($data['title'] ?? ['en' => 'Trending Topics']);
        $bgPath  = $data['background_image_path'] ?? null;
        $bgUrl   = $bgPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($bgPath) : null;
        $topics  = is_array($data['topics'] ?? null) ? array_values($data['topics']) : [];
        for ($i = count($topics); $i < 5; $i++) $topics[$i] = [];

        $img = fn($it) => ($p = $it['image_path'] ?? null)
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($p) : null;

        // Validate source: must be 'instagram' or 'linkedin', never empty string
        $src = fn($it, string $default): string =>
            in_array($s = strtolower(trim($it['source'] ?? '')), ['instagram','linkedin'], true)
            ? $s : $default;

    @endphp

    <section class="tt-stage" data-tt>

        {{-- Confirm overlay --}}
        <div class="tt-confirm hidden" data-tt-confirm aria-hidden="true">
            <div class="tt-confirm__dialog" role="dialog" aria-modal="true">
                <div class="tt-confirm__text" data-confirm-text>
                    {{ __('nav.social_redirect') }}
                </div>
                <div class="tt-confirm__actions">
                    <button type="button" class="tt-confirm__btn" 
                        data-tt-confirm-cancel>{{ __('ui.cancel') }}</button>
                    <button type="button" class="tt-confirm__btn tt-confirm__btn--primary" 
                        data-tt-confirm-leave>{{ __('nav.leave_page') }}</button>
                </div>
            </div>
        </div>

        {{-- Background --}}
        <div class="tt-stage__bg">
            @if ($bgUrl)<img src="{{ $bgUrl }}" alt="" class="tt-stage__bgImg">
            @else<div class="tt-stage__bgFallback"></div>@endif
            <div class="tt-stage__bgOverlay"></div>
        </div>

        {{-- Scene: perspective lives here ONLY --}}
        <div class="tt-stage__scene">
            <div class="tt-rig">
                {{-- Stage labels --}}
        <div class="tt-stage__headline">#We Provide the Future</div>

                {{-- ══════════════════════════════════════════════════════════
                    CARD MACRO
                    Each card = .tt-slot (wrapper, hit-box) + .tt-card (visual)
                    ══════════════════════════════════════════════════════════ --}}

                {{-- CARD 0 — LEFT TOP (instagram) --}}
                @php
                $c0 = $topics[0]; $s0=$src($c0,'instagram'); $i0=$img($c0);
                $tx0=$t($c0['text']??[]); $ti0=$urlWithLocale($c0['original_url']??'#');
                $pr0=$urlWithLocale($c0['privacy_url']??'/{locale}/pages/privacy-policy');
                $tm0=(string)($c0['time_ago']??'—'); $pf0=(string)($c0['profile_name']??'Globaltrding');
                @endphp
                <div class="tt-slot tt-slot--leftTop" data-slot="leftTop">
                    <article class="tt-card" data-social-card data-tt-card data-source="{{ $s0 }}">
                        <div class="tt-card__consent"><div class="tt-consent__box">
                            @php $platform0 = $s0 === 'instagram' ? 'Instagram' : 'LinkedIn'; @endphp
                            <div class="tt-consent__text">
                                {!! __('ui.social_consent_text', [
                                    'platform' => $platform0,
                                    'link' => '<a href="' . $pr0 . '" target="_blank" rel="noopener">' . __('ui.privacy_policy') . '</a>',
                                ]) !!}
                            </div>
                            <a href="#" class="tt-consent__btn" data-social-accept>{{ __('cookie.accept_all') }}</a>
                        </div></div>
                        <div>
                            <div class="tt-card__badge {{ $s0==='instagram' ? 'tt-card__badge--ig' : 'tt-card__badge--li' }}">{{ $s0==='instagram' ? 'IG' : 'in' }}</div>
                            @if($s0 === 'instagram')
                                <div class="tt-card__media">
                                    @if($i0)<img src="{{ $i0 }}" alt="" class="tt-card__img">@endif
                                </div>
                            @else
                                <div class="tt-card__content">
                                    <div class="tt-card__media">
                                        @if($i0)<img src="{{ $i0 }}" alt="" class="tt-card__img">@endif
                                    </div>
                                </div>
                            @endif
                            <div class="tt-card__body">
                                <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf0 }}</span><span class="tt-card__time">{{ $tm0 }}</span></div>
                                <div class="tt-card__scroll" data-tt-scroll>
                                    <div class="tt-card__text">{{ $tx0 }}</div>
                                    <a class="tt-card__link" href="{{ $ti0 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti0 }}">{{ __('nav.show_original') }}</a>
                                </div>
                                <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down><span class="tt-card__downIcon">&#8964;</span></button>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- CARD 1 — LEFT BOTTOM (instagram) --}}
                @php
                $c1=$topics[1]; $s1=$src($c1,'instagram'); $i1=$img($c1);
                $tx1=$t($c1['text']??[]); $ti1=$urlWithLocale($c1['original_url']??'#');
                $pr1=$urlWithLocale($c1['privacy_url']??'/{locale}/pages/privacy-policy');
                $tm1=(string)($c1['time_ago']??'—'); $pf1=(string)($c1['profile_name']??'Globaltrding');
                @endphp
                <div class="tt-slot tt-slot--leftBottom" data-slot="leftBottom">
                    <article class="tt-card" data-social-card data-tt-card data-source="{{ $s1 }}">
                        <div class="tt-card__consent"><div class="tt-consent__box">
                            @php $platform0 = $s0 === 'instagram' ? 'Instagram' : 'LinkedIn'; @endphp
                            <div class="tt-consent__text">
                                {!! __('ui.social_consent_text', [
                                    'platform' => $platform0,
                                    'link' => '<a href="' . $pr0 . '" target="_blank" rel="noopener">' . __('ui.privacy_policy') . '</a>',
                                ]) !!}
                            </div>
                            <a href="#" class="tt-consent__btn" data-social-accept>{{ __('cookie.accept_all') }}</a>
                        </div></div>
                        <div>
                            <div class="tt-card__badge {{ $s1==='instagram' ? 'tt-card__badge--ig' : 'tt-card__badge--li' }}">{{ $s1==='instagram' ? 'IG' : 'in' }}</div>
                            @if($s1 === 'instagram')
                                <div class="tt-card__media">
                                    @if($i1)<img src="{{ $i1 }}" alt="" class="tt-card__img">@endif
                                </div>
                            @else
                                <div class="tt-card__content">
                                    <div class="tt-card__media">
                                        @if($i1)<img src="{{ $i1 }}" alt="" class="tt-card__img">@endif
                                    </div>
                                </div>
                            @endif
                            <div class="tt-card__body">
                                <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf1 }}</span><span class="tt-card__time">{{ $tm1 }}</span></div>
                                <div class="tt-card__scroll" data-tt-scroll>
                                    <div class="tt-card__text">{{ $tx1 }}</div>
                                    <a class="tt-card__link" href="{{ $ti1 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti1 }}">{{ __('nav.show_original') }}</a>
                                </div>
                                <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down><span class="tt-card__downIcon">&#8964;</span></button>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- CARD 2 — CENTER (linkedin, tall) --}}
                @php
                $c2=$topics[2]; $i2=$img($c2); $s2=$src($c2,'linkedin');
                $tx2=$t($c2['text']??[]); $tt2=$t($c2['title']??[]); $ti2=$urlWithLocale($c2['original_url']??'#');
                $pr2=$urlWithLocale($c2['privacy_url']??'/{locale}/pages/privacy-policy');
                $tm2=(string)($c2['time_ago']??'—'); $pf2=(string)($c2['profile_name']??'Globaltrding');
                @endphp
                <div class="tt-slot tt-slot--center" data-slot="center">
                    <article class="tt-card" data-social-card data-tt-card data-source="{{$s2}}">
                        <div class="tt-card__consent">
                            <div class="tt-consent__box">
                                @php $platform0 = $s0 === 'instagram' ? 'Instagram' : 'LinkedIn'; @endphp
                                <div class="tt-consent__text">
                                    {!! __('ui.social_consent_text', [
                                        'platform' => $platform0,
                                        'link' => '<a href="' . $pr0 . '" target="_blank" rel="noopener">' . __('ui.privacy_policy') . '</a>',
                                    ]) !!}
                                </div>
                                <a href="#" class="tt-consent__btn" data-social-accept>{{ __('cookie.accept_all') }}</a>
                            </div>
                        </div>
                        <div>
                            <div class="tt-card__badge {{ $s2==='instagram' ? 'tt-card__badge--ig' : 'tt-card__badge--li' }}">{{ $s2==='instagram' ? 'IG' : 'in' }}</div>
                            @if($s2 === 'instagram')
                                <div class="tt-card__media">
                                    <img src="{{ $i2 }}" alt="" class="tt-card__img">
                                </div>
                            @else
                                <div class="tt-card__content">
                                    <div class="tt-card__media">
                                        <img src="{{ $i2 }}" alt="" class="tt-card__img">
                                    </div>
                                </div>
                            @endif
                            {{-- body--lg: flex:1 fills remaining card height --}}
                            <div class="tt-card__body tt-card__body--lg">
                                <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf2 }}</span><span class="tt-card__time">{{ $tm2 }}</span></div>
                                @if($tt2)<div class="tt-card__title">{{ $tt2 }}</div>@endif
                                <div class="tt-card__scroll" data-tt-scroll>
                                    <div class="tt-card__text tt-card__text--lg">{{ $tx2 }}</div>
                                    <a class="tt-card__link" href="{{ $ti2 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti2 }}">{{ __('nav.show_original') }}</a>
                                </div>
                                <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down><span class="tt-card__downIcon">&#8964;</span></button>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- CARD 3 — RIGHT TOP (linkedin) --}}
                @php
                $c3=$topics[3]; $s3=$src($c3,'linkedin'); $i3=$img($c3);
                $tx3=$t($c3['text']??[]); $ti3=$urlWithLocale($c3['original_url']??'#');
                $pr3=$urlWithLocale($c3['privacy_url']??'/{locale}/pages/privacy-policy');
                $tm3=(string)($c3['time_ago']??'—'); $pf3=(string)($c3['profile_name']??'Globaltrding');
                @endphp
                <div class="tt-slot tt-slot--rightTop" data-slot="rightTop">
                    <article class="tt-card" data-social-card data-tt-card data-source="{{ $s3 }}">
                        <div class="tt-card__consent"><div class="tt-consent__box">
                            @php $platform0 = $s0 === 'instagram' ? 'Instagram' : 'LinkedIn'; @endphp
                            <div class="tt-consent__text">
                                {!! __('ui.social_consent_text', [
                                    'platform' => $platform0,
                                    'link' => '<a href="' . $pr0 . '" target="_blank" rel="noopener">' . __('ui.privacy_policy') . '</a>',
                                ]) !!}
                            </div>
                            <a href="#" class="tt-consent__btn" data-social-accept>{{ __('cookie.accept_all') }}</a>
                        </div></div>
                        <div>
                            <div class="tt-card__badge {{ $s3==='instagram' ? 'tt-card__badge--ig' : 'tt-card__badge--li' }}">{{ $s3==='instagram' ? 'IG' : 'in' }}</div>
                            @if($s3 ==='instagram')
                                <div class="tt-card__media">
                                    @if($i3)<img src="{{ $i3 }}" alt="" class="tt-card__img">@endif
                                </div>
                            @else
                                <div class="tt-card__content">
                                    <div class="tt-card__media">
                                        @if($i3)<img src="{{ $i3 }}" alt="" class="tt-card__img">@endif
                                    </div>
                                </div>
                            @endif
                            <div class="tt-card__body">
                                <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf3 }}</span><span class="tt-card__time">{{ $tm3 }}</span></div>
                                <div class="tt-card__scroll" data-tt-scroll>
                                    <div class="tt-card__text">{{ $tx3 }}</div>
                                    <a class="tt-card__link" href="{{ $ti3 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti3 }}">{{ __('nav.show_original') }}</a>
                                </div>
                                <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down><span class="tt-card__downIcon">&#8964;</span></button>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- CARD 4 — RIGHT BOTTOM (linkedin) --}}
                @php
                $c4=$topics[4]; $s4=$src($c4,'linkedin'); $i4=$img($c4);
                $tx4=$t($c4['text']??[]); $ti4=$urlWithLocale($c4['original_url']??'#');
                $pr4=$urlWithLocale($c4['privacy_url']??'/{locale}/pages/privacy-policy');
                $tm4=(string)($c4['time_ago']??'—'); $pf4=(string)($c4['profile_name']??'Globaltrding');
                @endphp
                <div class="tt-slot tt-slot--rightBottom" data-slot="rightBottom">
                    <article class="tt-card" data-social-card data-tt-card data-source="{{ $s4 }}">
                        <div class="tt-card__consent">
                            <div class="tt-consent__box">
                                @php $platform0 = $s0 === 'instagram' ? 'Instagram' : 'LinkedIn'; @endphp
                                <div class="tt-consent__text">
                                    {!! __('ui.social_consent_text', [
                                        'platform' => $platform0,
                                        'link' => '<a href="' . $pr0 . '" target="_blank" rel="noopener">' . __('ui.privacy_policy') . '</a>',
                                    ]) !!}
                                </div>
                                <a href="#" class="tt-consent__btn" data-social-accept>{{ __('cookie.accept_all') }}t</a>
                                </div>
                            </div>
                        <div>
                            <div class="tt-card__badge {{ $s4==='instagram' ? 'tt-card__badge--ig' : 'tt-card__badge--li' }}">{{ $s4==='instagram' ? 'IG' : 'in' }}</div>
                            @if($s4 === 'instagram')
                                <div class="tt-card__media">
                                    @if($i4)<img src="{{ $i4 }}" alt="" class="tt-card__img">@endif
                                    <div class="tt-card__badge {{ $s4==='instagram' ? 'tt-card__badge--ig' : 'tt-card__badge--li' }}">{{ $s4==='instagram' ? 'IG' : 'in' }}</div>
                                </div>
                            @else
                                <div class="tt-card__content">
                                    <div class="tt-card__media">
                                        @if($i4)<img src="{{ $i4 }}" alt="" class="tt-card__img">@endif
                                        <div class="tt-card__badge {{ $s4==='instagram' ? 'tt-card__badge--ig' : 'tt-card__badge--li' }}">{{ $s4==='instagram' ? 'IG' : 'in' }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="tt-card__body">
                                <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf4 }}</span><span class="tt-card__time">{{ $tm4 }}</span></div>
                                <div class="tt-card__scroll" data-tt-scroll>
                                    <div class="tt-card__text">{{ $tx4 }}</div>
                                    <a class="tt-card__link" href="{{ $ti4 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti4 }}">{{ __('nav.show_original') }}</a>
                                </div>
                                <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down><span class="tt-card__downIcon">&#8964;</span></button>
                            </div>
                        </div>
                    </article>
                </div>

            </div>{{-- /.tt-rig --}}
        </div>{{-- /.tt-stage__scene --}}

        {{-- Bottom nav bar --}}
        <div class="tt-nav">
            <div class="tt-nav__bg"></div>
            <div class="tt-nav__inner">
                <div class="tt-nav__title">{{ $sectionTitle ?: 'Trending Topics' }}</div>
                <div class="tt-nav__labels">
                    <span class="tt-nav__label is-active">#Trending</span>
                </div>
            </div>
        </div>

    </section>

{{-- FEATURED NEWS --}}
@elseif ($type === 'featuredNews')
    @php
    $title = $data['title'] ?? 'Featured News';
    $lead = $data['lead'] ?? '';
    $limit = (int)($data['limit'] ?? 3);
    $showAll = (bool)($data['show_view_all'] ?? true);
    $viewAllLabel = $data['view_all_label'] ?? 'View all →';

    $posts = \App\Models\NewsPost::query()
        ->where('is_published', true)
        ->where('is_featured', true)
        ->orderByDesc('published_at')
        ->orderByDesc('id')
        ->limit(max(1, min(12, $limit)))
        ->get();
    @endphp

    <section class="border-t border-slate-200">
        <div class="mx-auto max-w-7xl px-4 py-10">
            <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>
                @if($lead)<p class="mt-2 text-slate-600">{{ $lead }}</p>@endif
            </div>

            @if($showAll)
                <a href="/{{ $locale }}/news" class="text-sm text-slate-600 hover:text-slate-900 hover:underline">
                {{ $viewAllLabel }}
                </a>
            @endif
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-3">
                @foreach($posts as $post)
                    @php
                    $pt = data_get($post->title, $locale) ?: data_get($post->title, $fallback) ?: '';
                    $pe = data_get($post->excerpt, $locale) ?: data_get($post->excerpt, $fallback) ?: '';
                    $img = $post->cover_image_path ? Storage::disk('public')->url($post->cover_image_path) : null;
                    $vid = $post->cover_video_path ? Storage::disk('public')->url($post->cover_video_path) : null;
                    $poster = $post->cover_poster_path ? Storage::disk('public')->url($post->cover_poster_path) : null;
                    @endphp

                    <a href="/{{ $locale }}/news/{{ $post->slug }}" class="rounded-xl border border-slate-200 bg-white overflow-hidden hover:shadow-sm transition">
                        <div class="aspect-[16/9] bg-slate-100">
                            @if($vid)
                                <video class="w-full h-full object-cover" muted playsinline preload="metadata" @if($poster) poster="{{ $poster }}" @endif>
                                    <source src="{{ $vid }}" type="video/mp4">
                                </video>
                            @elseif($img)
                                <img src="{{ $img }}" alt="" class="w-full h-full object-cover hover:scale-[1.015]">
                            @endif
                        </div>

                        <div class="p-5">
                            <div class="text-lg font-semibold leading-snug">{{ $pt }}</div>
                            @if($pe)<div class="mt-2 text-sm text-slate-600">{{ $pe }}</div>@endif
                        </div>
                    </a>
                @endforeach
            </div>

            @if($posts->isEmpty())
                <p class="mt-6 text-slate-600">{{ __('news.no_posts') }}</p>
            @endif
        </div>
    </section>

@endif