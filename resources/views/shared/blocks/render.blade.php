@php
    $type     = $block['type'] ?? null;
    $data     = $block['data'] ?? [];
    $locale   = app()->getLocale();
    $fallback = config('locales.default', 'en');

    $t = function ($value, string $locale, string $fallback): string {
        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }
        if (is_array($value)) {
            $v = data_get($value, $locale);
            if (is_string($v) || is_numeric($v)) return (string) $v;
            $v = data_get($value, $fallback);
            if (is_string($v) || is_numeric($v)) return (string) $v;
            foreach ($value as $vv) {
                if (is_string($vv) || is_numeric($vv)) {
                    $vv = trim((string) $vv);
                    if ($vv !== '') return $vv;
                }
            }
        }
        return '';
    };

    // FIX: use ($t) — without it $t is undefined inside the closure body
    $th = function ($value, string $locale, string $fallback) use ($t): string {
        return $t($value, $locale, $fallback);
    };
@endphp

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- HERO                                                               --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@if ($type === 'hero')
    @php
        $height        = $data['height']           ?? 'screen';
        $pos           = $data['content_position'] ?? 'left';
        $align         = $data['content_align']    ?? 'left';
        $overlayColor  = $data['overlay_color']    ?? '#000000';
        $overlayOpacity = is_numeric($data['overlay_opacity'] ?? null)
                            ? max(0, min(1, (float) $data['overlay_opacity']))
                            : 0.45;

        $mediaType = $data['media_type'] ?? 'video';
        $videoPath  = $data['video'] ?? null;
        $videoUrl   = $videoPath ? Storage::disk('public')->url($videoPath) : null;
        $imageUrls  = collect(is_array($data['images'] ?? null) ? $data['images'] : [])
                        ->map(fn ($p) => $p ? Storage::disk('public')->url($p) : null)
                        ->filter()->values()->all();

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

        // Resolve the first slide for initial SSR render
        $s0         = $slides[0] ?? [];
        $heroKicker = $t($s0['kicker']    ?? '', $locale, $fallback);
        $heroTitle  = $t($s0['title']     ?? '', $locale, $fallback);
        $heroLead   = $t($s0['lead']      ?? '', $locale, $fallback);
        $heroCta1    = $t($s0['cta1_label'] ?? '', $locale, $fallback);
        $heroCta1Url = $s0['cta1_url'] ?? null;
        $heroCta2    = $t($s0['cta2_label'] ?? '', $locale, $fallback);
        $heroCta2Url = $s0['cta2_url'] ?? null;
        $heroCta3    = $t($s0['cta3_label'] ?? '', $locale, $fallback);
        $heroCta3Url = $s0['cta3_url'] ?? null;

        // Pre-resolve ALL slides for the JS slider so it receives plain strings
        // Pre-resolve ALL slides for the JS slider so it receives plain strings
        $slidesForJs = collect($slides)->map(fn ($s) => [
            'kicker'    => $t($s['kicker']     ?? '', $locale, $fallback),
            'title'     => $t($s['title']      ?? '', $locale, $fallback),
            'lead'      => $t($s['lead']       ?? '', $locale, $fallback),
            'cta1_label' => $t($s['cta1_label'] ?? '', $locale, $fallback),
            'cta1_url'   => $s['cta1_url'] ?? null,
            'cta2_label' => $t($s['cta2_label'] ?? '', $locale, $fallback),
            'cta2_url'   => $s['cta2_url'] ?? null,
        ])->all();
    @endphp
    <section class="relative text-white hero-shell {{ $heightClass }}" 
            data-hero
            data-hero-autoplay="{{ $autoplay ? '1' : '0' }}"
            data-hero-interval="{{ $interval }}"
            data-hero-pause-hover="{{ $pauseOnHover ? '1' : '0' }}">
        <div class="gt-hero__media">
            @if ($mediaType === 'video')
                <video class="gt-hero__video" autoplay muted loop playsinline preload="metadata">
                    <source src="{{ $videoUrl }}">
                </video>
            @elseif ($mediaType === 'image' && count($imageUrls))
                <div class="gt-hero__slider" data-hero-slider>
                    @foreach ($imageUrls as $i => $u)
                        <div class="gt-hero__slide {{ $i === 0 ? 'is-active' : '' }}" data-hero-slide="{{ $i }}">
                            <img src="{{ $u }}" alt="" class="gt-hero__img">
                        </div>
                    @endforeach
                    @if (count($imageUrls) > 1)
                        <button type="button" class="gt-hero__nav gt-hero__nav--prev" data-hero-prev aria-label="Previous">‹</button>
                        <button type="button" class="gt-hero__nav gt-hero__nav--next" data-hero-next aria-label="Next">›</button>
                    @endif
                </div>
            @elseif ($mediaType === 'multimedia')
                @php
                    $multimediaSlides = [];
                    if ($videoUrl) $multimediaSlides[] = ['type' => 'video', 'url' => $videoUrl];
                    foreach ($imageUrls as $iu) $multimediaSlides[] = ['type' => 'image', 'url' => $iu];
                @endphp
                @if (count($multimediaSlides))
                    <div class="gt-hero__slider" data-hero-slider>
                        @foreach ($multimediaSlides as $mi => $ms)
                            <div class="gt-hero__slide {{ $mi === 0 ? 'is-active' : '' }}" data-hero-slide="{{ $mi }}">
                                @if ($ms['type'] === 'video')
                                    <video class="gt-hero__video" autoplay muted loop playsinline preload="metadata">
                                        <source src="{{ $ms['url'] }}" type="video/mp4">
                                    </video>
                                @else
                                    <img src="{{ $ms['url'] }}" alt="" class="gt-hero__img">
                                @endif
                            </div>
                        @endforeach
                        @if (count($multimediaSlides) > 1)
                            <button type="button" class="gt-hero__nav gt-hero__nav--prev" data-hero-prev aria-label="Previous">‹</button>
                            <button type="button" class="gt-hero__nav gt-hero__nav--next" data-hero-next aria-label="Next">›</button>
                        @endif
                    </div>
                @endif
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

                <h1 class=" {{ $titleClass }}" data-hero-title>{{ $heroTitle }}</h1>

                @if ($heroLead)
                    <p class=" {{ $leadClass }}" data-hero-lead>{{ $heroLead }}</p>
                @else
                    <p class="gt-hero__lead hidden" data-hero-lead></p>
                @endif

                <div class="mt-8 flex flex-wrap gap-3" data-hero-cta-wrap>
                    @if ($heroCta1 && $heroCta1Url && $heroCta2 && $heroCta2Url && $heroCta3 && $heroCta3Url)
                        <a href="{{ $heroCta1Url }}" class="rounded-md bg-white px-5 py-2.5 text-slate-900 font-medium hover:bg-slate-100">{{ $heroCta1 }}</a>
                        <a href="{{ $heroCta2Url }}" class="rounded-md border border-white/30 px-5 py-2.5 font-medium hover:bg-white/10">{{ $heroCta2 }}</a>
                        <a href="{{ $heroCta3Url }}" class="rounded-md border border-white/30 px-5 py-2.5 font-medium hover:bg-white/10">{{ $heroCta3 }}</a>
                    @elseif ($heroCta1 && $heroCta1Url && $heroCta2 && $heroCta2Url)
                        <a href="{{ $heroCta1Url }}" class="rounded-md bg-white px-5 py-2.5 text-slate-900 font-medium hover:bg-slate-100">{{ $heroCta1 }}</a>
                        <a href="{{ $heroCta2Url }}" class="rounded-md border border-white/30 px-5 py-2.5 font-medium hover:bg-white/10">{{ $heroCta2 }}</a>
                    @elseif ($heroCta1 && $heroCta1Url)
                        <a href="{{ $heroCta1Url }}" class="rounded-md bg-white px-5 py-2.5 text-slate-900 font-medium hover:bg-slate-100">{{ $heroCta1 }}</a>
                    @else
                        <a href="#" class="gt-btn gt-btn--primary hidden" data-hero-cta></a>
                    @endif
                </div>
            </div>
        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- MARKET BELT                                                               --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
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
{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- SECTION HEADING                                                    --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'sectionHeading')
    @php
        $title = $t($data['title'] ?? '', $locale, $fallback);
        $lead  = $t($data['lead']  ?? '', $locale, $fallback);
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12">
        <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">{{ $title }}</h2>
        @if ($lead)
            <p class="mt-3 text-slate-600 max-w-7xl">{{ $lead }}</p>
        @endif
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- INSIGHTS GRID                                                      --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'insightsGrid')
    @php
        $heading        = $t($data['heading'] ?? '', $locale, $fallback) ?: 'Company insights';
        $accent         = $data['accent'] ?? 'blue';
        $panelTextColor = $data['panel_text_color'] ?? '#ffffff';
        $row2LinkColor  = $data['row2_link_color']  ?? '#0ea5e9';

        $panelClass = match ($accent) {
            'dark'  => 'bg-slate-900  text-white',
            'slate' => 'bg-slate-700  text-white',
            default => 'bg-sky-600    text-white',
        };

        // Top-row data
        $topImgPath  = $data['top_left_image']     ?? null;
        $topImgUrl   = $topImgPath ? Storage::disk('public')->url($topImgPath) : null;
        $topKicker   = $t($data['top_right_kicker']     ?? '', $locale, $fallback);
        $topTitle    = $t($data['top_right_title']      ?? '', $locale, $fallback);
        $topText     = $t($data['top_right_text']       ?? '', $locale, $fallback);
        $topCtaLabel = $t($data['top_right_cta_label']  ?? '', $locale, $fallback);
        $topCtaUrl   = $data['top_right_cta_url'] ?? '';

        // Bottom tiles
        $bottomTiles = is_array($data['bottom_tiles'] ?? null) ? $data['bottom_tiles'] : [];
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-12 at-start" aria-label="{{ $heading }}">

        {{-- ── Heading ─────────────────────────────────────────────────── --}}
        <h2 class="gt-insights__heading">{{ $heading }}</h2>

        {{-- ── Row 1: Feature tile ─────────────────────────────────────── --}}
        <div class="gt-insights__row gt-insights__row--top">

            {{-- Left: large image --}}
            <div class="gt-insights__tile gt-insights__tile--image1" aria-hidden="true">
                @if ($topImgUrl)
                    <img
                        src="{{ $topImgUrl }}"
                        alt="{{ strip_tags($topKicker ?: $topTitle) }}"
                        class="gt-insights__img"
                        loading="lazy"
                    >
                @else
                    <div class="gt-insights__placeholder-img"></div>
                @endif
            </div>

            {{-- Right: text panel --}}
            <div
                class="gt-insights__tile gt-insights__tile--panel1 {{ $panelClass }}"
                style="color: {{ $panelTextColor }};"
            >
                @if ($topKicker)
                    <span class="gt-insights__kicker">{{ $topKicker }}</span>
                @endif

                @if ($topTitle)
                    <h3 class="gt-insights__title">{{ $topTitle }}</h3>
                @endif

                @if ($topText)
                    <p class="gt-insights__text">{{ $topText }}</p>
                @endif

                @if ($topCtaLabel && $topCtaUrl)
                    <a href="{{ $topCtaUrl }}" class="gt-insights__cta">
                        {{ $topCtaLabel }}
                    </a>
                @endif
            </div>

        </div>{{-- /row--top --}}

        {{-- ── Row 2: Three-column grid ────────────────────────────────── --}}
        @if (count($bottomTiles))
            <div class="gt-insights__row gt-insights__row--bottom">

                @foreach ($bottomTiles as $tile)
                    @php
                        $tileType    = $tile['type'] ?? 'image';
                        $tileImgPath = $tile['image'] ?? null;
                        $tileImgUrl  = $tileImgPath ? Storage::disk('public')->url($tileImgPath) : null;

                        $tileKicker   = $t($tile['kicker']    ?? '', $locale, $fallback);
                        $tileTitle    = $t($tile['title']      ?? '', $locale, $fallback);
                        $tileLead     = $t($tile['lead']       ?? '', $locale, $fallback);
                        $tileCtaLabel = $t($tile['cta_label']  ?? '', $locale, $fallback);
                        $tileCtaUrl   = (string) ($tile['cta_url'] ?? '');
                    @endphp

                    @if ($tileType === 'panel')
                        {{-- ────────────────────────────────────────────────── --}}
                        {{-- PREMIUM DATA PANEL TILE                            --}}
                        {{-- ────────────────────────────────────────────────── --}}
                        @php
                            $tileExcerpt = $t($tile['panel_excerpt'] ?? '', $locale, $fallback);
                            $tileBody    = $t($tile['panel_body']    ?? '', $locale, $fallback);
                            $showChart   = (bool) ($tile['show_chart'] ?? false);
                            $source      = $tile['chart_source'] ?? 'manual';

                            // ── Resolve chart points ──────────────────────
                            $pts = [];
                            if ($showChart) {
                                if ($source === 'manual') {
                                    $pts = is_array($tile['chart_points'] ?? null)
                                        ? $tile['chart_points'] : [];

                                } elseif ($source === 'url_json') {
                                    $cUrl = (string) ($tile['chart_url'] ?? '');
                                    if ($cUrl !== '') {
                                        try {
                                            $pts = app(\App\Services\ChartDataClient::class)->fromUrl($cUrl);
                                        } catch (\Throwable) { $pts = []; }
                                    }

                                } elseif ($source === 'market_instrument') {
                                    $slug = (string) ($tile['chart_instrument'] ?? '');
                                    $days = max(5, min(120, (int) ($tile['chart_days'] ?? 14)));
                                    if ($slug !== '') {
                                        try {
                                            $inst = \App\Models\MarketInstrument::query()
                                                ->where('slug', $slug)->first();
                                            if ($inst) {
                                                $pts = \App\Models\MarketPoint::query()
                                                    ->where('market_instrument_id', $inst->id)
                                                    ->orderBy('date', 'desc')
                                                    ->limit($days + 2)
                                                    ->get(['value', 'date'])
                                                    ->reverse()->values()
                                                    ->map(fn($r) => [
                                                        'value' => (float) $r->value,
                                                        'date'  => $r->date?->format('d M'),
                                                    ])->all();
                                            }
                                        } catch (\Throwable) { $pts = []; }
                                    }
                                }
                            }

                            $scale    = $tile['chart_scale']       ?? 'linear';
                            $mode     = $tile['chart_mode']        ?? 'absolute';
                            $autoMm   = (bool) ($tile['chart_auto_minmax'] ?? true);
                            $minFixed = $tile['chart_min']         ?? null;
                            $maxFixed = $tile['chart_max']         ?? null;

                            // Determine trend direction for supplementary badge
                            $latestVal = !empty($pts)
                                ? (float)(is_array(end($pts)) ? (end($pts)['value'] ?? 0) : end($pts))
                                : null;
                            $prevVal = count($pts) > 1
                                ? (float)(is_array($pts[count($pts)-2]) ? ($pts[count($pts)-2]['value'] ?? 0) : $pts[count($pts)-2])
                                : null;
                            $hasTrend = $latestVal !== null && $prevVal !== null;
                            $trendUp  = $hasTrend && $latestVal >= $prevVal;
                        @endphp

                        <div
                            class="gt-insights__tile gt-insights__tile--panel2 {{ $panelClass }}"
                            style="color: {{ $panelTextColor }};"
                            role="region"
                            aria-label="{{ $tileTitle }}"
                        >
                            {{-- ① Header ──────────────────────────────── --}}
                            @if ($tileTitle)
                                <div class="gt-insights__panel-header">
                                    <div class="gt-insights__panel-title">{{ $tileTitle }}</div>
                                </div>
                            @endif

                            {{-- ② Chart ──────────────────────────────── --}}
                            @if ($showChart && count($pts) > 1)
                                <div class="gt-insights__panel-chart" aria-hidden="true">
                                    @include('shared.blocks.partials.sparkline', [
                                        'points'    => $pts,
                                        'scale'     => $scale,
                                        'mode'      => $mode,
                                        'auto'      => $autoMm,
                                        'minFixed'  => $minFixed,
                                        'maxFixed'  => $maxFixed,
                                        'showGrid'  => true,
                                        'showAxes'  => true,
                                        'gridLines' => 3,
                                        'premium'   => true,
                                    ])
                                </div>
                            @endif

                            {{-- ③ Data body ────────────────────────────── --}}
                            @if ($tileExcerpt || $tileLead || $tileBody)
                                <div class="gt-insights__panel-body">
                                    @if ($tileLead)
                                        <div class="gt-insights__panel-lead">
                                            {{ $tileLead }}
                                        </div>
                                    @endif
                                    @if ($tileExcerpt || $tileLead)
                                        <div class="gt-insights__panel-excerpt">
                                            {{ $tileExcerpt ?: $tileLead }}
                                        </div>
                                    @endif
                                    @if ($tileBody)
                                        <div class="gt-insights__panel-text">{{ $tileBody }}</div>
                                    @endif
                                </div>
                            @endif

                            {{-- ④ Footer CTA ──────────────────────────── --}}
                            @if ($tileCtaLabel && $tileCtaUrl)
                                <div class="gt-insights__panel-footer">
                                    <a href="{{ $tileCtaUrl }}" class="gt-insights__panel-cta">
                                        {{ $tileCtaLabel }}
                                    </a>
                                </div>
                            @endif
                        </div>

                    @else
                        {{-- ────────────────────────────────────────────────── --}}
                        {{-- IMAGE CARD TILE                                     --}}
                        {{-- ────────────────────────────────────────────────── --}}
                        <div class="gt-insights__tile gt-insights__tile--image2">
                            {{-- Image --}}
                            <div class="gt-insights__media" aria-hidden="true">
                                @if ($tileImgUrl)
                                    <img
                                        src="{{ $tileImgUrl }}"
                                        alt="{{ strip_tags($tileTitle) }}"
                                        class="gt-insights__img"
                                        loading="lazy"
                                    >
                                @endif
                            </div>

                            {{-- Below-image content --}}
                            <div class="gt-insights__below">
                                @if ($tileKicker)
                                    <span class="gt-insights__belowKicker">{{ $tileKicker }}</span>
                                @endif

                                @if ($tileTitle)
                                    <h4 class="gt-insights__belowTitle">{{ $tileTitle }}</h4>
                                @endif

                                @if ($tileLead)
                                    <p class="gt-insights__belowText">{{ $tileLead }}</p>
                                @endif

                                @if ($tileCtaLabel && $tileCtaUrl)
                                    <a
                                        href="{{ $tileCtaUrl }}"
                                        class="gt-insights__belowCta"
                                        style="color: {{ $row2LinkColor }};"
                                    >
                                        {{ $tileCtaLabel }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                @endforeach

            </div>{{-- /row--bottom --}}
        @endif

    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- CARDS CAROUSEL                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'cardsCarousel')
    @php
        $bg          = $data['bg']    ?? 'white';
        $carTitle    = $t($data['title'] ?? '', $locale, $fallback);
        $carLead     = $t($data['lead']  ?? '', $locale, $fallback);
        $items       = is_array($data['items'] ?? null) ? $data['items'] : [];
        $titleSize   = $data['title_size']    ?? 'lg';
        $textSize    = $data['text_size']     ?? 'md';
        $autoplay    = (bool) ($data['autoplay']      ?? false);
        $autoplayMs  = (int)  ($data['autoplay_ms']   ?? 4500);
        $pauseHover  = (bool) ($data['pause_on_hover'] ?? true);

        $wrapClass  = match($bg) {
            'dark'  => 'bg-slate-900 text-white border-white/10',
            'slate' => 'bg-slate-50 text-slate-900 border-slate-200',
            default => 'bg-white text-slate-900 border-slate-200',
        };
        $titleClass = $titleSize === 'md' ? 'text-xl md:text-2xl' : 'text-2xl md:text-3xl';
        $textClass  = $textSize  === 'sm' ? 'text-sm' : 'text-base';
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="mt-8 rounded-2xl border {{ $wrapClass }} p-6 md:p-10"
            data-carousel
            data-carousel-autoplay="{{ $autoplay ? '1' : '0' }}"
            data-carousel-interval="{{ max(1500, $autoplayMs) }}"
            data-carousel-pause-hover="{{ $pauseHover ? '1' : '0' }}">
            @if ($carTitle) <h3 class="font-semibold tracking-tight {{ $titleClass }}">{{ $carTitle }}</h3> @endif
            @if ($carLead)  <p class="mt-2 opacity-90 max-w-3xl {{ $textClass }}">{{ $carLead }}</p>        @endif

            <div class="mt-6 relative">
                <button type="button" class="gt-car__nav gt-car__nav--prev" data-carousel-prev aria-label="Previous">‹</button>
                <button type="button" class="gt-car__nav gt-car__nav--next" data-carousel-next aria-label="Next">›</button>
                <div class="gt-car__track" data-carousel-track>
                    @foreach ($items as $it)
                    @php
                        $imgPath  = $it['image'] ?? null;
                        $imgUrl   = $imgPath ? Storage::disk('public')->url($imgPath) : null;
                        // FIX: was "$t = $it['title']" which destroyed the $t closure
                        $cardTitle = $t($it['title'] ?? '', $locale, $fallback);
                        $cardText  = $t($it['text']  ?? '', $locale, $fallback);
                        $cardUrl   = $it['url'] ?? null;
                    @endphp
                    <a class="gt-car__card {{ $bg === 'dark' ? 'gt-car__card--dark' : '' }}"
                    href="{{ $cardUrl ?: '#' }}"
                    {{ $cardUrl ? '' : 'tabindex=-1 aria-disabled=true' }}>
                        @if ($imgUrl) <img src="{{ $imgUrl }}" alt="" class="gt-car__img"> @endif
                        <div class="gt-car__body">
                            <div class="gt-car__title">{{ $cardTitle }}</div>
                            @if ($cardText) <div class="gt-car__text">{{ $cardText }}</div> @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- SPLIT                                                              --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'split')
    @php
        $side      = $data['image_side'] ?? 'left';
        $imgPath   = $data['image'] ?? null;
        $imgUrl    = $imgPath ? Storage::disk('public')->url($imgPath) : null;
        $splitTitle = $t($data['title']     ?? '', $locale, $fallback);
        $splitHtml  = $th($data['html']     ?? '', $locale, $fallback);
        $ctaLbl    = $t($data['cta_label'] ?? '', $locale, $fallback);
        $ctaLabel  = $ctaLbl !== '' ? $ctaLbl : null;
        $ctaUrl    = $data['cta_url'] ?? null;
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="grid md:grid-cols-2 gap-6 items-center">
            @if ($side === 'left')
                <div>
                    @if ($imgUrl) <img src="{{ $imgUrl }}" alt="" class="w-full rounded-2xl border border-slate-200" /> @endif
                </div>
            @endif
            <div>
                <h3 class="text-xl md:text-2xl font-semibold tracking-tight">{{ $splitTitle }}</h3>
                @if ($splitHtml)
                    <div class="mt-3 prose prose-slate max-w-none">{!! $splitHtml !!}</div>
                @endif
                @if ($ctaLabel && $ctaUrl)
                    <div class="mt-5">
                        <a href="{{ $ctaUrl }}"
                        class="inline-flex rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                            {{ $ctaLabel }}
                        </a>
                    </div>
                @endif
            </div>
            @if ($side === 'right')
                <div>
                    @if ($imgUrl) <img src="{{ $imgUrl }}" alt="" class="w-full rounded-2xl border border-slate-200" /> @endif
                </div>
            @endif
        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- CARDS                                                              --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'cards')
    @php
        $cardsTitle = $t($data['title'] ?? '', $locale, $fallback);
        $cardsLead  = $t($data['lead']  ?? '', $locale, $fallback);
        $items      = $data['items'] ?? [];
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12" data-industry-slider>
        <div class="flex items-end justify-between gap-4">
            @if ($cardsTitle) <h2 class="text-xl md:text-2xl font-semibold tracking-tight">{{ $cardsTitle }}</h2> @endif
            @if ($cardsLead)  <p class="mt-2 text-slate-600 max-w-3xl">{{ $cardsLead }}</p>                       @endif
        </div>
        <div class="mt-6 overflow-hidden">
            <div class="flex gap-4 overflow-x-auto overflow-x-hidden snap-x snap-mandatory scroll-smooth pb-2" data-ind="track">
                @foreach ($items as $it)
                    @php
                        $imgPath  = $it['image'] ?? null;
                        $imgUrl   = $imgPath ? Storage::disk('public')->url($imgPath) : null;
                        // FIX: was "$t = $it['title']" which destroyed the $t closure
                        $cardTitle = $t($it['title'] ?? '', $locale, $fallback);
                        $cardText  = $t($it['text']  ?? '', $locale, $fallback);
                        $cardHtml  = $t($it['body_html']  ?? '', $locale, $fallback);
                        $ctaLabel  = $t($it['cta_label'] ?? '', $locale, $fallback);
                        $cardUrl   = $it['url'] ?? null;
                    @endphp
                    <div class="snap-start shrink-0 w-[85%] sm:w-[45%] lg:w-[32%] rounded-xl border border-slate-200 bg-white overflow-hidden hover:shadow-sm transition">
                        <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                            @if ($imgUrl)<img src="{{ $imgUrl }}" alt="" class="h-full w-full object-cover hover:scale-[1.015] transition" />@endif
                        </div>
                        <div class="p-4">
                            <div class="mt-2 text-lg text-slate-600">{{ $cardTitle }}</div>
                            @if ($cardText) <div class="text-xl font-semibold leading-snug">{{ $cardText }}</div> @endif
                            @if ($cardHtml) <div class="mt-3 prose prose-slate max-w-none">{{ $cardHtml }}</div> @endif
                            <a href="{{ $cardUrl }}" class="inline-flex items-center rounded-md px-4 py-2 text-blue-600 hover:text-blue-800">{{ $ctaLabel }} →</a>
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

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- METRICS                                                            --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'metrics')
    @php
        $bg          = $data['bg']    ?? 'slate';
        $metricTitle = $t($data['title'] ?? '', $locale, $fallback);
        $items       = is_array($data['items'] ?? null) ? $data['items'] : [];
        $animate     = (bool) ($data['animate'] ?? true);

        $wrapClass = match($bg) {
            'dark'  => 'bg-slate-900 text-white border-white/10',
            'white' => 'bg-white text-slate-900 border-slate-200',
            default => 'bg-slate-50 text-slate-900 border-slate-200',
        };

        $n          = max(1, count($items));
        $valueClass = $n <= 3 ? 'gt-m__val--xl' : ($n === 4 ? 'gt-m__val--lg' : 'gt-m__val--md');
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="mt-8 rounded-2xl border {{ $wrapClass }} p-6 md:p-10">
            @if ($metricTitle) <h3 class="text-xl font-semibold tracking-tight">{{ $metricTitle }}</h3> @endif
            <div class="mt-5 grid gap-4"
                style="grid-template-columns: repeat({{ min($n, 4) }}, minmax(0, 1fr));">
                @foreach ($items as $it)
                @php
                    $raw    = (string) ($it['value'] ?? '');
                    $label  = $t($it['label'] ?? '', $locale, $fallback);
                    $num    = preg_replace('/[^0-9.]/', '', $raw);
                    $suffix = trim(str_replace($num, '', $raw));
                    $numVal = is_numeric($num) ? (float) $num : null;
                @endphp
                <div class="rounded-xl border {{ $bg === 'dark' ? 'border-white/10 bg-white/5' : 'border-slate-200 bg-white' }} p-4">
                    <div class="gt-m__value {{ $valueClass }}"
                        @if ($animate && $numVal !== null)
                            data-countup="{{ $numVal }}"
                            data-countup-suffix="{{ e($suffix) }}"
                        @endif>
                        {{ $raw }}
                    </div>
                    <div class="mt-1 text-sm opacity-80">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- MEDIA TEXT                                                         --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'mediaText')
    @php
        $side      = $data['media_side'] ?? 'left';
        $mediaType = $data['media_type'] ?? 'image';
        $ratio     = $data['media_width'] ?? '50-50';
        $maxH      = is_numeric($data['media_max_h'] ?? null) ? (int) $data['media_max_h'] : null;

        $imgUrl    = !empty($data['image'])  ? Storage::disk('public')->url($data['image'])  : null;
        $vidUrl    = !empty($data['video'])  ? Storage::disk('public')->url($data['video'])  : null;
        $posterUrl = !empty($data['poster']) ? Storage::disk('public')->url($data['poster']) : null;

        $mtTitle    = $t($data['title']     ?? '', $locale, $fallback);
        $mtExcerpt  = $t($data['excerpt']   ?? '', $locale, $fallback);
        $mtHtml     = $th($data['body_html'] ?? '', $locale, $fallback);
        $ctaLbl    = $t($data['cta_label'] ?? '', $locale, $fallback);
        $ctaLabel  = $ctaLbl !== '' ? $ctaLbl : null;
        $ctaUrl    = $data['cta_url'] ?? null;

        [$mediaClass, $textClass] = match($ratio) {
            '30-70' => ['md:col-span-5',  'md:col-span-11'],
            '40-60' => ['md:col-span-7',  'md:col-span-9'],
            '60-40' => ['md:col-span-9',  'md:col-span-7'],
            '70-30' => ['md:col-span-11', 'md:col-span-5'],
            default => ['md:col-span-8',  'md:col-span-8'],
        };
        $mediaStyle = $maxH ? "max-height:{$maxH}px; height:{$maxH}px;" : '';
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="grid md:grid-cols-16 gap-6 items-center">
            @if ($side === 'left')
                <div class="{{ $mediaClass }}">
                    @include('shared.blocks.partials.media', compact('mediaType','imgUrl','vidUrl','posterUrl','mediaStyle'))
                </div>
            @endif
            <div class="{{ $textClass }}">
                <h3 class="text-xl md:text-2xl font-semibold tracking-tight">{{ $mtTitle }}</h3>
                @if ($mtExcerpt) <p class="mt-2 text-slate-600">{{ $mtExcerpt }}</p>          @endif
                @if ($mtHtml)    <div class="mt-3 prose prose-slate max-w-none">{!! $mtHtml !!}</div> @endif
                @if ($ctaLabel && $ctaUrl)
                    <div class="mt-5">
                        <a href="{{ $ctaUrl }}"
                        class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                            {{ $ctaLabel }}
                        </a>
                    </div>
                @endif
            </div>
            @if ($side === 'right')
                <div class="{{ $mediaClass }}">
                    @include('shared.blocks.partials.media', compact('mediaType','imgUrl','vidUrl','posterUrl','mediaStyle'))
                </div>
            @endif
        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- MEDIA TEXT LINKS 3                                                 --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'mediaTextLinks3')
    @php
        $layout     = $data['layout']     ?? 'media-text-links';
        $mediaType  = $data['media_type'] ?? 'image';
        $mediaWidth = max(30, min(40, (int) ($data['media_width'] ?? 35)));
        $textWidth  = max(30, min(40, (int) ($data['text_width']  ?? 35)));
        $linksWidth = max(30, min(40, (int) ($data['links_width'] ?? 35)));
        $maxH       = is_numeric($data['media_max_h'] ?? null) ? (int) $data['media_max_h'] : null;

        $imgUrl    = !empty($data['image'])  ? Storage::disk('public')->url($data['image'])  : null;
        $vidUrl    = !empty($data['video'])  ? Storage::disk('public')->url($data['video'])  : null;
        $posterUrl = !empty($data['poster']) ? Storage::disk('public')->url($data['poster']) : null;

        $mtl3Title    = $t($data['title']      ?? '', $locale, $fallback);
        $mtl3Excerpt  = $t($data['excerpt']    ?? '', $locale, $fallback);
        $mtl3Html     = $th($data['body_html'] ?? '', $locale, $fallback);
        $ctaLbl      = $t($data['cta_label']  ?? '', $locale, $fallback);
        $ctaLabel    = $ctaLbl !== '' ? $ctaLbl : null;
        $ctaUrl      = $data['cta_url'] ?? null;
        $linksTitle  = $t($data['links_title'] ?? '', $locale, $fallback);
        $links       = is_array($data['links'] ?? null) ? $data['links'] : [];

        $mediaStyle = $maxH ? "max-height:{$maxH}px; height:{$maxH}px;" : '';

        $gridCols = match($layout) {
            'media-text-links' => "{$mediaWidth}% {$textWidth}% {$linksWidth}%",
            'links-media-text' => "{$linksWidth}% {$mediaWidth}% {$textWidth}%",
            'links-text-media' => "{$linksWidth}% {$textWidth}% {$mediaWidth}%",
            'text-media-links' => "{$textWidth}% {$mediaWidth}% {$linksWidth}%",
            default            => "{$mediaWidth}% {$textWidth}% {$linksWidth}%",
        };
        $colOrder = match($layout) {
            'media-text-links' => ['media','text','links'],
            'links-media-text' => ['links','media','text'],
            'links-text-media' => ['links','text','media'],
            'text-media-links' => ['text','media','links'],
            default            => ['media','text','links'],
        };

        $linksPadColor = $data['links_pad_color'] ?? '#ffffff';
        $linksRowColor = $data['links_row_color'] ?? '#0ea5e9';
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="gt-mtl3__grid" style="grid-template-columns: {{ $gridCols }};">
            @foreach ($colOrder as $col)
                @if ($col === 'media')
                    <div class="gt-mtl3__media">
                        @include('shared.blocks.partials.media', compact('mediaType','imgUrl','vidUrl','posterUrl','mediaStyle'))
                    </div>
                @elseif ($col === 'text')
                    <div class="gt-mtl3__text">
                        @if ($mtl3Title)   <h3 class="gt-mtl3__title">{{ $mtl3Title }}</h3>                               @endif
                        @if ($mtl3Excerpt) <p class="gt-mtl3__excerpt">{{ $mtl3Excerpt }}</p>                              @endif
                        @if ($mtl3Html)    <div class="gt-mtl3__body prose prose-slate max-w-none">{!! $mtl3Html !!}</div> @endif
                        @if ($ctaLabel && $ctaUrl)
                            <div class="gt-mtl3__cta">
                                <a href="{{ $ctaUrl }}" class="gt-btn gt-btn--primary">{{ $ctaLabel }}</a>
                            </div>
                        @endif
                    </div>
                @elseif ($col === 'links')
                    <aside class="gt-mtl3__links" style="background: {{ $linksPadColor }};">
                        @if (count($links))
                            <div class="gt-mtl3__title">{{ $linksTitle }}</div>
                            <div class="gt-mtl3__linksList">
                                @foreach ($links as $row)
                                @php
                                    $linksNo  = (string) ($row['linksNo'] ?? '');
                                    $lnkLabel = $t($row['label']  ?? '', $locale, $fallback);
                                    $lnkUrl   = (string) ($row['url']    ?? '#');
                                    $lnkHint  = $t($row['hint']   ?? '', $locale, $fallback);
                                    $lnkTarget = (string) ($row['target'] ?? '_self');
                                @endphp
                                <a class="gt-mtl3__link"
                                href="{{ $lnkUrl }}"
                                target="{{ $lnkTarget }}"
                                @if ($lnkTarget === '_blank') rel="noopener" @endif>
                                    <span class="gt-mtl3__linkLabel">{{ $lnkLabel }}</span>
                                    @if ($lnkHint)
                                        <span class="gt-mtl3__linkHint">
                                            <span class="gt-mtl3__linksNo">{{ $linksNo }}</span>
                                            <span class="gt-mtl3__hintText" style="color: {{ $linksRowColor }};">{{ $lnkHint }}</span>
                                            <span class="gt-mtl3__chev">›</span>
                                        </span>
                                    @endif
                                </a>
                                @endforeach
                            </div>
                        @endif
                    </aside>
                @endif
            @endforeach
        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- DROPDOWN LINKS (accordion)                                         --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'dropdownLinks')
    @php
        $dlHeading = $t($data['heading'] ?? '', $locale, $fallback) ?: null;
        $items     = is_array($data['items'] ?? null) ? $data['items'] : [];
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12">
        @if ($dlHeading) <h3 class="gt-acc__heading">{{ $dlHeading }}</h3> @endif
        <div class="gt-acc__list">
            @foreach ($items as $i => $row)
            @php
                $rowTitle     = $t($row['title']     ?? '', $locale, $fallback);
                $rowContent   = $t($row['content']   ?? '', $locale, $fallback);
                $rowLinkLabel = $t($row['link_label'] ?? '', $locale, $fallback);
                $rowLinkUrl   = (string) ($row['link_url'] ?? '');
                $rowTarget    = (string) ($row['target']   ?? '_self');

                $side          = $row['media_side']  ?? 'left';
                $mediaType     = $row['media_type']  ?? 'image';
                $ratio         = $row['media_width'] ?? '50-50';
                $maxH          = is_numeric($row['media_max_h'] ?? null) ? (int) $row['media_max_h'] : null;
                $imgUrl        = !empty($row['image'])  ? Storage::disk('public')->url($row['image'])  : null;
                $vidUrl        = !empty($row['video'])  ? Storage::disk('public')->url($row['video'])  : null;
                $posterUrl     = !empty($row['poster']) ? Storage::disk('public')->url($row['poster']) : null;
                $insideTitle   = $t($row['row_title']  ?? '', $locale, $fallback);
                $insideExcerpt = $t($row['excerpt']    ?? '', $locale, $fallback);
                $insideHtml    = $th($row['body_html'] ?? '', $locale, $fallback);
                $insideCtaLbl  = $t($row['cta_label']  ?? '', $locale, $fallback);
                $insideCtaUrl  = $row['cta_url'] ?? '';
                $mediaStyle    = $maxH ? "max-height:{$maxH}px; height:{$maxH}px;" : '';

                [$mediaClass, $textClass] = match($ratio) {
                    '30-70' => ['lg:col-span-4', 'lg:col-span-8'],
                    '40-60' => ['lg:col-span-5', 'lg:col-span-7'],
                    '60-40' => ['lg:col-span-7', 'lg:col-span-5'],
                    '70-30' => ['lg:col-span-8', 'lg:col-span-4'],
                    default => ['lg:col-span-6', 'lg:col-span-6'],
                };
                $hasInnerPanel = ($insideTitle || $insideExcerpt || $insideHtml || $imgUrl || $vidUrl);
            @endphp
            <details class="gt-acc__item" @if ($i === 0) open @endif>
                <summary class="gt-acc__summary">
                    <span class="gt-acc__title">{{ $rowTitle }}</span>
                    <span class="gt-acc__icon" aria-hidden="true"></span>
                </summary>
                <div class="gt-acc__body">
                    @if ($rowContent) <p class="gt-acc__text">{{ $rowContent }}</p> @endif
                    @if ($rowLinkUrl)
                        <a class="gt-acc__link"
                        href="{{ $rowLinkUrl }}"
                        target="{{ $rowTarget }}"
                        @if ($rowTarget === '_blank') rel="noopener" @endif>
                            {{ $rowLinkLabel ?: __('ui.learn_more') }} →
                        </a>
                    @endif
                    @if ($hasInnerPanel)
                        <div class="gt-acc__panel">
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                                @if ($side === 'left')
                                    <div class="{{ $mediaClass }}">
                                        @include('shared.blocks.partials.media', compact('mediaType','imgUrl','vidUrl','posterUrl','mediaStyle'))
                                    </div>
                                @endif
                                <div class="{{ $textClass }}">
                                    @if ($insideTitle)   <h4 class="text-xl font-semibold tracking-tight">{{ $insideTitle }}</h4>    @endif
                                    @if ($insideExcerpt) <p class="mt-2 text-slate-600">{{ $insideExcerpt }}</p>                     @endif
                                    @if ($insideHtml)    <div class="mt-3 prose prose-slate max-w-none">{!! $insideHtml !!}</div>    @endif
                                    @if ($insideCtaLbl && $insideCtaUrl)
                                        <div class="mt-5">
                                            <a href="{{ $insideCtaUrl }}"
                                            class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                                                {{ $insideCtaLbl }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                @if ($side === 'right')
                                    <div class="{{ $mediaClass }}">
                                        @include('shared.blocks.partials.media', compact('mediaType','imgUrl','vidUrl','posterUrl','mediaStyle'))
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </details>
            @endforeach
        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TWO COLS  (product PDP)                                            --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'twoCols')
    @php
        $hasAccess        = (bool) ($hasProductAccess ?? false);
        $publicVisible    = (bool) ($data['public_visible']    ?? true);
        $publicClickablel = (bool) ($data['public_clickable_l'] ?? false);
        $publicClickabler = (bool) ($data['public_clickable_r'] ?? false);
        $blockHidden      = (! $hasAccess && ! $publicVisible);
        $blockLockedl     = (! $hasAccess && ! $publicClickablel);
        $blockLockedr     = (! $hasAccess && ! $publicClickabler);

        $bg     = $data['bg']      ?? '#ffffff';
        $cardBg = $data['card_bg'] ?? '#ffffff';
        $ctaBg  = $data['cta_bg']  ?? '#0f172a';
        $Text   = $data['text']    ?? '#0f172a';
        $Html   = $data['html']    ?? '#0f172a';
        $layout = $data['layout']  ?? 'text_media';
        $mediaType = $data['media_type'] ?? 'image';

        $leftTitle  = $t($data['left_title']  ?? '', $locale, $fallback);
        $leftHtml   = $th($data['left_html']  ?? '', $locale, $fallback);
        $rightTitle = $t($data['right_title'] ?? '', $locale, $fallback);
        $rightHtml  = $th($data['right_html'] ?? '', $locale, $fallback);
        $ctaLabell   = $t($data['cta_label_l']   ?? '', $locale, $fallback);
        $ctaLabelr   = $t($data['cta_label_r']   ?? '', $locale, $fallback);
        $ctalUrl    = $data['ctaL_url'] ?? null;
        $ctarUrl    = $data['ctaR_url'] ?? null;

        $imgUrl    = !empty($data['image'])  ? Storage::disk('public')->url($data['image'])  : null;
        $vidUrl    = !empty($data['video'])  ? Storage::disk('public')->url($data['video'])  : null;
        $posterUrl = !empty($data['poster']) ? Storage::disk('public')->url($data['poster']) : null;
    @endphp
    @if (! $blockHidden)
    <section class="gt-twoCols" style="background: {{ $bg }};">
        <div class="gt-twoCols__grid">
            @if ($layout === 'media_text')
                <div class="gt-twoCols__media">
                    @include('shared.blocks.partials.media', compact('mediaType','imgUrl','vidUrl','posterUrl') + ['mediaStyle' => ''])
                </div>
            @endif

            <div class="gt-twoCols__col" style="background: {{ $cardBg }};">
                @if ($leftTitle) <h3 class="gt-twoCols__h" style="color: {{ $Html }};">{{ $leftTitle }}</h3>                              @endif
                @if ($leftHtml)  <div class="prose1 prose-slate max-w-none" style="color: {{ $Html }};">{!! $leftHtml !!}</div>           @endif
                @if ($ctaLabell && $ctalUrl)
                    <div class="gt-twoCols__cta">
                        @if ($blockLockedl)
                            <span class="gt-btn gt-btn--primary is-disabled" aria-disabled="true"
                                style="background: {{ $ctaBg }}; color: {{ $Text }};">{{ $ctaLabell }}</span>
                        @else
                            <a class="gt-btn gt-btn--primary" href="{{ $ctalUrl }}"
                            style="background: {{ $ctaBg }}; color: {{ $Text }};">{{ $ctaLabell }}</a>
                        @endif
                    </div>
                @endif
            </div>

            @if ($layout === 'text_text')
                <div class="gt-twoCols__col" style="background: {{ $cardBg }};">
                    @if ($rightTitle) <h3 class="gt-twoCols__h" style="color: {{ $Html }};">{{ $rightTitle }}</h3>                            @endif
                    @if ($rightHtml)  <div class="prose1 prose-slate max-w-none" style="color: {{ $Html }};">{!! $rightHtml !!}</div>         @endif
                    @if ($ctaLabelr && $ctarUrl)
                        <div class="gt-twoCols__cta">
                            @if ($blockLockedr)
                                <span class="gt-btn gt-btn--primary is-disabled" aria-disabled="true"
                                    style="background: {{ $ctaBg }}; color: {{ $Text }};">{{ $ctaLabelr }}</span>
                            @else
                                <a class="gt-btn gt-btn--primary" href="{{ $ctarUrl }}"
                                style="background: {{ $ctaBg }}; color: {{ $Text }};">{{ $ctaLabelr }}</a>
                            @endif
                        </div>
                    @endif
                </div>
            @elseif ($layout === 'text_media')
                <div class="gt-twoCols__media">
                    @include('shared.blocks.partials.media', compact('mediaType','imgUrl','vidUrl','posterUrl') + ['mediaStyle' => ''])
                </div>
            @endif
        </div>
    </section>
@endif
{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- Industries Slider                                                  --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'industries_slider')
    @php
        $sectionTitle = $t($data['title'] ?? [], $locale, $fallback) ?: 'Industries';
        $viewAllUrl   = '/' . $locale . '/industries';
        $industries   = \App\Models\Industry::query()
                            ->where('is_published', true)
                            ->orderBy('sort_order')
                            ->limit(12)
                            ->get();
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12" data-industry-slider>
        <div class="flex items-end justify-between gap-4">
            <h2 class="text-4xl font-semibold tracking-tight">{{ $sectionTitle }}</h2>
            <div class="flex items-center gap-3">
                <a href="{{ $viewAllUrl }}" class="text-sm text-slate-600 hover:underline">{{ __('ui.view_all') }} →</a>
            </div>
        </div>
        <div class="mt-6 overflow-hidden">
            <div class="flex gap-4 overflow-x-auto overflow-x-hidden snap-x snap-mandatory scroll-smooth pb-2" data-ind="track">
                @foreach ($industries as $ind)
                    @php
                        $title = data_get($ind->title,$locale) ?: data_get($ind->title,$fallback) ?: $ind->slug;
                        $img   = $ind->cover_image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($ind->cover_image_path) : null;
                        $iUrl = '/' . $locale . '/industries/' . $ind->slug;
                    @endphp
                    <a href="{{ $iUrl }}" class="snap-start shrink-0 w-[85%] sm:w-[45%] lg:w-[28%] rounded-xl border border-slate-200 bg-white overflow-hidden hover:shadow-sm transition">
                        <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                            @if ($img)<img src="{{ $img }}" alt="{{ $title }}" class="h-full w-full object-cover hover:scale-[1.015] transition"/>@endif
                        </div>
                        <div class="p-4">
                            <div class="text-xl font-light tracking-tight">{{ $title }}</div>
                            <div class="mt-2 text-sm text-slate-700 hover:underline">Discover more →</div>
                        </div>
                    </a>
                @endforeach
                @if (count($industries) > 3)
                    <button type="button" class="ind-btn ind-btn--prev" data-ind="prev" aria-label="Previous">‹</button>
                <button type="button" class="ind-btn ind-btn--next" data-ind="next" aria-label="Next">›</button>
                @endif
            </div>
        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- 4 col grids                                                  --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'colsGrids')
    @php
        $cgGap      = $data['grid_type'] ?? 'gaped';
        $cgAlign    = $data['item-align'] ?? 'left';
        $cgCols     = (int) ($data['columns'] ?? 3);
        $cgColClass = match ($cgCols) {
            2 => 'xl:grid-cols-2',
            3 => 'lg:grid-cols-3',
            4 => 'sm:grid-cols-4',
            5 => 'grid-cols-5',
            default => 'sm:grid-cols-2 lg:grid-cols-3',
        };
        $cgGapClass = $cgGap === 'gapless' ? '' : 'gap-6';
        $cgAlignClass = $cgAlign === 'center' ? 'place-items-center' : '';
        $cgKicker  = $t($data['kicker']  ?? '', $locale, $fallback);
        $cgHeading  = $t($data['heading_tabs']  ?? '', $locale, $fallback);
        $cgSubtitle = $t($data['subtitle_tabs'] ?? '', $locale, $fallback);
        $cgItems    = is_array($data['items'] ?? null) ? $data['items'] : [];
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-12">
        @if ($cgKicker || $cgHeading || $cgSubtitle)
            <div class="mb-10">
                @if ($cgKicker)
                    <div class="mt-4 text-lg font-semibold text-slate-400">{{ $cgKicker }}</div>
                @endif
                @if ($cgHeading)
                    <h2 class="mt-4 text-4xl font-semibold tracking-tight">{{ $cgHeading }}</h2>
                @endif
                @if ($cgSubtitle)
                    <p class="mt-4 text-2xl text-slate-600">{{ $cgSubtitle }}</p>
                @endif
            </div>
        @endif

        <div class="grid {{ $cgGapClass }} {{ $cgColClass }}">
            @foreach ($cgItems as $item)
                @php
                    $itKicker = $t($item['kicker_tabs'] ?? '', $locale, $fallback);
                    $itTitle = $t($item['title_tabs']   ?? '', $locale, $fallback);
                    $itExc   = $t($item['excerpt_tabs'] ?? '', $locale, $fallback);
                    $itImg   = ! empty($item['cover_image_path'])
                        ? Storage::disk('public')->url($item['cover_image_path'])
                        : null;
                    $itUrl   = $item['link_url'] ?? null;
                    $itCta   = $t($item['cta_tabs'] ?? '', $locale, $fallback);
                    $itCtaUrl = $item['cta_url'] ?? null;
                @endphp

                @if ($itUrl)
                    <a href="{{ $itUrl }}" class="group rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 hover:shadow-sm transition">
                @else
                    <div class="group rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 hover:shadow-sm transition">
                @endif
                    @if ($itImg)
                        <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                            <img src="{{ $itImg }}" alt="{{ $itTitle }}"
                                 class="h-full w-full object-cover {{ $itUrl ? 'group-hover:scale-[1.015] transition' : '' }}" />
                        </div>
                    @endif
                    @if ($cgColClass === 'grid-cols-5')
                        <div class="m-2 p-4 {{ $cgAlignClass }}">
                            @if ($itKicker)
                                <div class="mt-2 font-semibold text-slate-400" style="font-size: 0.80rem">{{ $itKicker }}</div>
                            @endif
                            @if ($itTitle)
                                <div class="mt-1 text-sm font-light tracking-tight {{ $itUrl ? 'group-hover:underline' : '' }}">{{ $itTitle }}</div>
                            @endif
                            @if ($itExc)
                                <p class="mt-2 text-slate-600" style="font-size: 0.80rem">{{ $itExc }}</p>
                            @endif
                            @if ($itCta && $itCtaUrl)
                                <div class="mt-4">
                                    <a href="{{ $itCtaUrl }}" class="mt-2 font-medium text-blue-600 transition-colors duration-150 ease-in-out hover:underline" style="font-size: 0.80rem">{{ $itCta }} -&gt</a>
                                </div>
                            @endif
                        </div>
                    @elseif ($cgColClass === 'sm:grid-cols-4')
                        <div class="m-2 p-4 {{ $cgAlignClass }}">
                            @if ($itKicker)
                                <div class="mt-2 text-sm font-semibold text-slate-400">{{ $itKicker }}</div>
                            @endif
                            @if ($itTitle)
                                <div class="mt-1 text-lg font-light tracking-tight {{ $itUrl ? 'group-hover:underline' : '' }}">{{ $itTitle }}</div>
                            @endif
                            @if ($itExc)
                                <p class="mt-2 text-sm text-slate-600">{{ $itExc }}</p>
                            @endif
                            @if ($itCta && $itCtaUrl)
                                <div class="mt-4">
                                    <a href="{{ $itCtaUrl }}" class="mt-2 text-sm font-medium text-blue-600 transition-colors duration-150 ease-in-out hover:underline">{{ $itCta }} -&gt</a>
                                </div>
                            @endif
                        </div>
                    @elseif ($cgColClass === 'lg:grid-cols-3')
                        <div class="m-4 p-4 {{ $cgAlignClass }}">
                            @if ($cgKicker)
                                <div class="mt-2 text-lg font-semibold text-slate-400">{{ $cgKicker }}</div>
                            @endif
                            @if ($itTitle)
                                <div class="mt-2 text-2xl font-light tracking-tight {{ $itUrl ? 'group-hover:underline' : '' }}">{{ $itTitle }}</div>
                            @endif
                            @if ($itExc)
                                <p class="mt-2 text-lg text-slate-600">{{ $itExc }}</p>
                            @endif
                            @if ($itCta && $itCtaUrl)
                                <div class="mt-4">
                                    <a href="{{ $itCtaUrl }}" class="mt-2 text-lg font-medium text-blue-600 transition-colors duration-150 ease-in-out hover:underline">{{ $itCta }} -&gt</a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="m-4 p-4 {{ $cgAlignClass }}">
                            @if ($itKicker)
                                <div class="m-2 mt-4 text-xl font-semibold text-slate-400">{{ $itKicker }}</div>
                            @endif
                            @if ($itTitle)
                                <div class="m-2 mt-4 text-3xl font-light tracking-tight {{ $itUrl ? 'group-hover:underline' : '' }}">{{ $itTitle }}</div>
                            @endif
                            @if ($itExc)
                                <p class="m-2 mt-4 text-xl text-slate-600">{{ $itExc }}</p>
                            @endif
                            @if ($itCta && $itCtaUrl)
                                <div class="mt-4">
                                    <a href="{{ $itCtaUrl }}" class="m-2 mt-4 text-xl font-medium text-blue-600 transition-colors duration-150 ease-in-out hover:underline">{{ $itCta }} -&gt</a>
                                </div>
                            @endif
                        </div>
                    @endif
                @if ($itUrl)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    </section>

{{-- CTA --}}
@elseif ($type === 'cta')
    @php
        $title    = $t($data['title']    ?? [], $locale, $fallback);
        $text     = $t($data['text']     ?? [], $locale, $fallback);
        $btnLabel = $t($data['button_label'] ?? [], $locale, $fallback);
        $btnUrl   = $data['button_url'] ?? '#';
    @endphp
    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 sm:p-10">
            <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-8">
                    <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>
                    @if ($text)
                        <p class="mt-3 text-slate-600">{{ $text }}</p>
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
{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- PD CARDS  (product PDP)                                            --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'pdcards')
    @php
        $hasAccess      = (bool) ($hasProductAccess ?? false);
        $publicVisible  = (bool) ($data['public_visible']  ?? true);
        $publicClickable = (bool) ($data['public_clickable'] ?? false);
        $blockLocked    = (! $hasAccess && ! $publicClickable);
        $blockHidden    = (! $hasAccess && ! $publicVisible);

        $bg      = $data['bg'] ?? '#ffffff';
        $heading = $t($data['heading'] ?? '', $locale, $fallback);
        $items   = is_array($data['items'] ?? null) ? $data['items'] : [];
    @endphp
    @if (! $blockHidden)
        <section class="gt-cards" style="background: {{ $bg }};">
            <div class="gt-cards__inner">
                @if ($heading) <h3 class="gt-cards__h">{{ $heading }}</h3> @endif
                <div class="gt-cards__grid">
                    @foreach ($items as $card)
                    @php
                        $cardBg           = $card['card_bg'] ?? '#ffffff';
                        $ctaBg            = $card['cta_bg']  ?? '#0f172a';
                        $cardText         = $card['text']    ?? '#0f172a';
                        $cardHtml         = $card['html']    ?? '#0f172a';
                        $cardExrt         = $card['exrt']    ?? '#475569';
                        $cardPublicVisible  = (bool) ($card['public_visible']  ?? true);
                        $cardPublicClickable = (bool) ($card['public_clickable'] ?? false);
                        $cardHidden  = (! $hasAccess && ! $cardPublicVisible);
                        $cardLocked  = (! $hasAccess && ! $cardPublicClickable);
                        $mediaType   = $card['media_type'] ?? 'image';
                        $imgUrl      = !empty($card['image'])  ? Storage::disk('public')->url($card['image'])  : null;
                        $vidUrl      = !empty($card['video'])  ? Storage::disk('public')->url($card['video'])  : null;
                        $posterUrl   = !empty($card['poster']) ? Storage::disk('public')->url($card['poster']) : null;
                        $cardTitle   = $t($card['title']     ?? '', $locale, $fallback);
                        $cardHtmlCnt = $th($card['body_html'] ?? ($card['html_content'] ?? ''), $locale, $fallback);
                        $cardExcerpt = $t($card['excerpt']   ?? '', $locale, $fallback);
                        $cardCta     = $t($card['cta_label'] ?? '', $locale, $fallback);
                        $cardCtaUrl  = $card['cta_url'] ?? null;
                    @endphp
                    @if (! $cardHidden)
                    <article class="gt-cards__card" style="background: {{ $cardBg }};">
                        <div class="gt-cards__media" style="background: {{ $cardBg }};">
                            @include('shared.blocks.partials.media', compact('mediaType','imgUrl','vidUrl','posterUrl') + ['mediaStyle' => ''])
                        </div>
                        <div class="gt-cards__body">
                            <div class="gt-cards__title" style="color: {{ $cardHtml }};">{{ $cardTitle }}</div>
                            @if ($cardExcerpt)
                                <div class="gt-cards__excerpt" style="color: {{ $cardExrt }};">{{ $cardExcerpt }}</div>
                            @endif
                            @if ($cardHtmlCnt)
                                <div class="prose prose-slate max-w-none" style="color: {{ $cardHtml }};">{!! $cardHtmlCnt !!}</div>
                            @endif
                            @if ($cardCta && $cardCtaUrl)
                                <div class="gt-cards__cta">
                                    @if ($cardLocked)
                                        <span class="gt-btn gt-btn--primary is-disabled" aria-disabled="true"
                                            style="background: {{ $ctaBg }}; color: {{ $cardText }};">{{ $cardCta }}</span>
                                    @else
                                        <a class="gt-btn gt-btn--primary" href="{{ $cardCtaUrl }}"
                                        style="background: {{ $ctaBg }}; color: {{ $cardText }};">{{ $cardCta }}</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </article>
                    @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- DOC DROPDOWN  (product PDP documents)                              --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'docDropdown')
    @php
        $ddHeading     = $t($data['heading'] ?? '', $locale, $fallback);
        $rows          = is_array($data['rows'] ?? null) ? $data['rows'] : [];
        $disableLinks  = (bool) ($disableDocLinks   ?? false);
        $publicEnabled = (bool) ($publicDocsEnabled ?? true);
        $hasAccess     = (bool) ($hasProductAccess  ?? false);
    @endphp
    <div class="gt-docdd">
        @if ($ddHeading)
            <h4 class="gt-docdd__heading">{{ $ddHeading }}</h4>
        @endif

        @foreach ($rows as $row)
            @php
                $rowTitle     = $t($row['title'] ?? '', $locale, $fallback);
                $rowFile = ! empty($row['file']) ? $row['file'] : null;
                if ($rowFile) {
                    $originalName = $row['original_name'] ?? basename($rowFile);
                    $rowUrl = url('/document-download') . '?' . http_build_query([
                        'path' => $rowFile,
                        'name' => $originalName,
                    ]);
                    $downloadName = $originalName;
                } else {
                    $rowUrl       = (string) ($row['url'] ?? '');
                    $downloadName = ! empty($row['original_name'])
                                        ? $row['original_name']
                                        : basename(urldecode(parse_url($rowUrl, PHP_URL_PATH) ?? ''));
                }
                $downloadName = ! empty($row['original_name'])
                                    ? $row['original_name']
                                    : basename(urldecode(parse_url($rowUrl, PHP_URL_PATH) ?? ''));
                $rowTarget    = (string) ($row['target'] ?? '_blank');
                $downloadable = (bool) ($row['downloadable'] ?? false);
                $canDownload  = $hasAccess || ($publicEnabled && $downloadable);
                $locked       = (! $canDownload && $disableLinks);

                // Derive a file-type badge from the resolved URL
                $ext = strtolower(pathinfo(parse_url($rowUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                $badge = match($ext) {
                    'pdf'                      => 'PDF',
                    'doc', 'docx'              => 'DOC',
                    'xls', 'xlsx', 'csv'       => 'XLS',
                    'ppt', 'pptx'              => 'PPT',
                    'zip', 'rar', '7z'         => 'ZIP',
                    default                    => strtoupper($ext) ?: 'FILE',
                };
            @endphp
            <div class="gt-docdd__row" data-docdd-doc>
                @if (! $canDownload)
                    <span class="gt-docdd__link gt-docdd__link--locked" aria-disabled="true">
                        <span class="gt-docdd__badge">{{ $badge }}</span>
                        {{ $rowTitle }}
                        <span class="gt-docdd__lockIcon" aria-hidden="true">🔒</span>
                    </span>
                @else
                    <a class="gt-docdd__link"
                        href="{{ $rowUrl }}"
                        data-doc-dl="{{ $rowUrl }}"
                        data-doc-name="{{ $downloadName }}"
                        onclick="event.preventDefault(); gtDocDownload(this)">
                            <span class="gt-docdd__badge">{{ $badge }}</span>
                            {{ $rowTitle }}
                            <span class="gt-docdd__dlIcon" aria-hidden="true">↓</span>
                    </a>
                @endif
            </div>
        @endforeach
    </div>

{{-- FEATURED NEWS --}}
@elseif ($type === 'featuredNews')
    @php
        $title        = $t($data['title']          ?? [], $locale, $fallback);
        $lead         = $t($data['lead']           ?? [], $locale, $fallback);
        $viewAllLabel = $t($data['view_all_label'] ?? [], $locale, $fallback) ?: 'View all →';
        $limit        = (int) ($data['limit']          ?? 3);
        $showAll      = (bool)($data['show_view_all']  ?? true);

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


    {{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- RICH TEXT                                                          --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'richText')
    @php
        $rtKicker = $t($data['kicker'] ?? '', $locale, $fallback);
        $rtHeading = $t($data['heading'] ?? '', $locale, $fallback);
        $rtHtml    = $th($data['html']   ?? '', $locale, $fallback);
    @endphp
    <section class="mt-4 gt-rich-text">
        @if ($rtKicker)
            <div class="m-2 text-lg font-semibold text-slate-500">{{ $rtKicker }}</div>
        @endif
        @if ($rtHeading)
            <h2 class="m-2 text-4xl md:text-3xl font-semibold tracking-tight mb-4">{{ $rtHeading }}</h2>
        @endif
        @if ($rtHtml)
            <div class="m-2 text-2xl prose prose-slate max-w-none">{!! $rtHtml !!}</div>
        @endif
    </section>
{{-- RICH TEXT 2                                                          --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'richText2')
    @php
        $rtKicker = $t($data['kicker'] ?? '', $locale, $fallback);
        $rtHeading = $t($data['heading'] ?? '', $locale, $fallback);
        $rtHtml    = $th($data['html']   ?? '', $locale, $fallback);
    @endphp
    <section class="mt-4 gt-rich-text">
        @if ($rtKicker)
            <div class="m-2 text-lg font-semibold text-slate-500">{{ $rtKicker }}</div>
        @endif
        @if ($rtHeading)
            <h2 class="m-2 text-3xl md:text-3xl font-semibold tracking-tight mb-4">{{ $rtHeading }}</h2>
        @endif
        @if ($rtHtml)
            <div class="m-2 text-xl prose prose-slate max-w-none">{!! $rtHtml !!}</div>
        @endif
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- IMAGE                                                              --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'image')
    @php
        $imgPath    = $data['path'] ?? null;
        $imgUrl     = $imgPath ? Storage::disk('public')->url($imgPath) : null;
        $imgCaption = $t($data['caption'] ?? '', $locale, $fallback);
    @endphp
    @if ($imgUrl)
        <figure class="gt-block-image">
            <img
                src="{{ $imgUrl }}"
                alt="{{ $imgCaption }}"
                class="gt-block-image__img w-full rounded-2xl border border-slate-200 object-cover"
                loading="lazy"
            >
            @if ($imgCaption)
                <figcaption class="gt-block-image__caption mt-2 text-sm text-slate-500 text-center">{{ $imgCaption }}</figcaption>
            @endif
        </figure>
    @endif

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- VIDEO                                                              --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'video')
    @php
        $vidPath    = $data['path'] ?? null;
        $vidUrl     = $vidPath ? Storage::disk('public')->url($vidPath) : null;
        $vidCaption = $t($data['caption'] ?? '', $locale, $fallback);
    @endphp
    @if ($vidUrl)
        <figure class="mx-auto max-w-7xl px-4 py-12">
            <video
                class="gt-block-video__player w-full rounded-2xl border border-slate-200"
                autoplay muted loop
                preload="metadata"
                playsinline
            >
                <source src="{{ $vidUrl }}">
            </video>
            @if ($vidCaption)
                <figcaption class="gt-block-video__caption mt-2 text-sm text-slate-500 text-center">{{ $vidCaption }}</figcaption>
            @endif
        </figure>
    @endif

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- TIMELINE                                                           --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'timeline')
    @php
        $tlKicker  = $t($data['kicker']  ?? [], $locale, $fallback);
        $tlHeading = $t($data['heading'] ?? [], $locale, $fallback);
        $tlItems   = is_array($data['items'] ?? null) ? $data['items'] : [];
    @endphp

    <section class="mt-20 rounded-2xl bg-slate-50 text-black py-20 px-4">
        <div class="mx-auto max-w-3xl">
            @if ($tlKicker)
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">{{ $tlKicker }}</p>
            @endif
            @if ($tlHeading)
                <h2 class="text-3xl md:text-4xl font-semibold tracking-tight mb-14">{{ $tlHeading }}</h2>
            @endif

            <div class="relative">
                {{-- Vertical line --}}
                <div class="absolute left-0 top-2 bottom-0 w-px bg-slate-700 pointer-events-none"></div>

                <div class="space-y-12">
                    @foreach ($tlItems as $item)
                        @php
                            $tlYear     = $item['year'] ?? '';
                            $tlCategory = $t($item['category'] ?? [], $locale, $fallback);
                            $tlTitle    = $t($item['title']    ?? [], $locale, $fallback);
                            $tlBody     = $t($item['body']     ?? [], $locale, $fallback);
                            $dotFilled  = (bool) ($item['dot_filled'] ?? false);
                        @endphp
                        <div class="relative pl-8">
                            {{-- Dot marker --}}
                            <div class="absolute -left-[3px] top-[6px] w-[7px] h-[7px] rounded-full border
                                        {{ $dotFilled
                                            ? 'bg-black border-black'
                                            : 'bg-slate-950 border-slate-400' }}">
                            </div>

                            @if ($tlYear || $tlCategory)
                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-2">
                                    {{ $tlYear }}{{ ($tlYear && $tlCategory) ? ' — ' : '' }}{{ $tlCategory }}
                                </p>
                            @endif
                            @if ($tlTitle)
                                <h3 class="text-base font-semibold leading-snug mb-2 text-slate-800">{{ $tlTitle }}</h3>
                            @endif
                            @if ($tlBody)
                                <p class="text-sm text-slate-600 leading-relaxed">{{ $tlBody }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- CTA WITH STATS                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'ctaStats')
    @php
        $csHeading  = $t($data['heading']  ?? [], $locale, $fallback);
        $csSubtitle = $t($data['subtitle'] ?? [], $locale, $fallback);
        $csButtons  = is_array($data['buttons'] ?? null) ? $data['buttons'] : [];
        $csStats    = is_array($data['stats']   ?? null) ? $data['stats']   : [];

        $csIcons = [
            'location'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-slate-400"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd"/></svg>',
            'calendar'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-slate-400"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd"/></svg>',
            'globe'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-slate-400"><path d="M16.555 5.412a8.028 8.028 0 00-3.503-2.81 14.898 14.898 0 011.601 4.123 8.025 8.025 0 001.902-1.313zM5.643 15.347a8.028 8.028 0 01-2.631-3.423A14.918 14.918 0 016.25 13.5c.382 0 .762-.013 1.139-.04a11.27 11.27 0 01-1.746 1.887zM10 2a8 8 0 100 16A8 8 0 0010 2zm0 3.5c.712 0 1.41.067 2.09.192a13.387 13.387 0 01-1.008 2.448A11.265 11.265 0 0110 8.25a11.265 11.265 0 01-1.082-.11 13.387 13.387 0 01-1.008-2.448A11.35 11.35 0 0110 5.5z"/></svg>',
            'shield'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-slate-400"><path fill-rule="evenodd" d="M9.661 2.237a.531.531 0 01.678 0 11.947 11.947 0 007.078 2.749.5.5 0 01.479.425c.069.52.104 1.05.104 1.589 0 5.162-3.26 9.563-7.834 11.256a.48.48 0 01-.332 0C5.26 16.563 2 12.162 2 7c0-.538.035-1.069.104-1.589a.5.5 0 01.48-.425 11.947 11.947 0 007.077-2.749z" clip-rule="evenodd"/></svg>',
            'clock'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-slate-400"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/></svg>',
            'star'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-slate-400"><path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd"/></svg>',
            'check'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-slate-400"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>',
            'users'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 shrink-0 text-slate-400"><path d="M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 17a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 2.636.818.818 0 01-.36.98A7.465 7.465 0 0114.5 16z"/></svg>',
            'db'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path d="M8 7c3.314 0 6-1.343 6-3s-2.686-3-6-3-6 1.343-6 3 2.686 3 6 3Z" /><path d="M8 8.5c1.84 0 3.579-.37 4.914-1.037A6.33 6.33 0 0 0 14 6.78V8c0 1.657-2.686 3-6 3S2 9.657 2 8V6.78c.346.273.72.5 1.087.683C4.42 8.131 6.16 8.5 8 8.5Z" /><path d="M8 12.5c1.84 0 3.579-.37 4.914-1.037.366-.183.74-.41 1.086-.684V12c0 1.657-2.686 3-6 3s-6-1.343-6-3v-1.22c.346.273.72.5 1.087.683C4.42 12.131 6.16 12.5 8 12.5Z" /></svg>',
            'cube'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4"><path d="M8.372 1.349a.75.75 0 0 0-.744 0l-4.81 2.748L8 7.131l5.182-3.034-4.81-2.748ZM14 5.357 8.75 8.43v6.005l4.872-2.784A.75.75 0 0 0 14 11V5.357ZM7.25 14.435V8.43L2 5.357V11c0 .27.144.518.378.651l4.872 2.784Z" /></svg>',
        ];
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-12">
        <div class="rounded-2xl bg-slate-950 text-white px-8 py-12 md:px-14 md:py-16">

            {{-- Heading + subtitle --}}
            @if ($csHeading || $csSubtitle)
                <div class="text-center mb-10">
                    @if ($csHeading)
                        <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">{{ $csHeading }}</h2>
                    @endif
                    @if ($csSubtitle)
                        <p class="mt-3 text-sm md:text-base text-slate-300 max-w-2xl mx-auto leading-relaxed">
                            {{ $csSubtitle }}
                        </p>
                    @endif
                </div>
            @endif

            {{-- CTA buttons --}}
            @if (count($csButtons))
                <div class="flex flex-wrap justify-center gap-3 mb-10">
                    @foreach ($csButtons as $btn)
                        @php
                            $btnLabel = $t($btn['label'] ?? [], $locale, $fallback);
                            $btnUrl   = $btn['url'] ?? '#';
                        @endphp
                        @if ($btnLabel)
                            <a href="{{ $btnUrl }}"
                               class="inline-flex items-center rounded-md border border-white/25 px-5 py-2.5 text-sm font-medium text-white hover:bg-white/10 transition-colors">
                                {{ $btnLabel }}
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Divider + stats --}}
            @if (count($csStats))
                <hr class="border-slate-700 mb-8">
                <div class="flex flex-wrap justify-center gap-x-8 gap-y-3">
                    @foreach ($csStats as $stat)
                        @php
                            $statText = $t($stat['text'] ?? [], $locale, $fallback);
                            $statIcon = $csIcons[$stat['icon'] ?? 'globe'] ?? $csIcons['globe'];
                        @endphp
                        @if ($statText)
                            <span class="flex items-center gap-1.5 text-sm text-slate-300">
                                {!! $statIcon !!}
                                {{ $statText }}
                            </span>
                        @endif
                    @endforeach
                </div>
            @endif

        </div>
    </section>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- FULL WIDTH CARDS                                                  --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@elseif ($type === 'fullWidthCards')
    @php
        $fwcCols     = (int) ($data['columns'] ?? 3);
        $fwcGap      = $data['grid_type'] ?? 'gaped';
        $fwcBg       = $data['section_bg_color'] ?? '#dce9f5';
        $fwcCardBg   = $data['card_bg_color'] ?? 'transparent';
        $fwcColClass = match ($fwcCols) {
            2       => 'md:grid-cols-2',
            4       => 'sm:grid-cols-2 lg:grid-cols-4',
            default => 'sm:grid-cols-2 lg:grid-cols-3',
        };
        $fwcGapClass = $fwcGap === 'gapless' ? '' : 'gap-8';

        $fwcKickerSize   = $data['kicker_size']      ?? 'text-sm';
        $fwcHeadingSize  = $data['heading_size']     ?? 'text-4xl';
        $fwcSubtitleSize = $data['subtitle_size']    ?? 'text-xl';
        $fwcItemKicker   = $data['item_kicker_size'] ?? 'text-sm';
        $fwcItemTitle    = $data['item_title_size']  ?? 'text-2xl';
        $fwcItemBody     = $data['item_body_size']   ?? 'text-base';
        $fwcItemCta      = $data['item_cta_size']    ?? 'text-sm';

        $fwcKicker   = $t($data['kicker']        ?? '', $locale, $fallback);
        $fwcHeading  = $t($data['heading_tabs']  ?? '', $locale, $fallback);
        $fwcSubtitle = $t($data['subtitle_tabs'] ?? '', $locale, $fallback);
        $fwcItems    = is_array($data['items'] ?? null) ? $data['items'] : [];

        // Determine card style — transparent means no inline style needed
        $cardBgStyle = ($fwcCardBg && $fwcCardBg !== 'transparent' && $fwcCardBg !== '#00000000')
            ? "background-color:{$fwcCardBg};"
            : '';
    @endphp

    <section class="w-full py-16 px-6" style="background-color:{{ $fwcBg }}" >
        @if ($fwcKicker || $fwcHeading || $fwcSubtitle)
            <div class="max-w-screen-2xl mx-auto mb-10">
                @if ($fwcKicker)
                    <div class="{{ $fwcKickerSize }} font-semibold uppercase tracking-widest text-slate-500 mb-2">
                        {{ $fwcKicker }}
                    </div>
                @endif
                @if ($fwcHeading)
                    <h2 class="{{ $fwcHeadingSize }} font-light tracking-tight text-slate-800">
                        {{ $fwcHeading }}
                    </h2>
                @endif
                @if ($fwcSubtitle)
                    <p class="mt-3 {{ $fwcSubtitleSize }} text-slate-600">{!! $fwcSubtitle !!}</p>
                @endif
            </div>
        @endif

        <div class="max-w-screen-2xl mx-auto grid {{ $fwcGapClass }} {{ $fwcColClass }}">
            @foreach ($fwcItems as $item)
                @php
                    $itKicker = $t($item['kicker_tabs']  ?? '', $locale, $fallback);
                    $itTitle  = $t($item['title_tabs']   ?? '', $locale, $fallback);
                    $itExc    = $t($item['excerpt_tabs'] ?? '', $locale, $fallback);
                    $itImg    = ! empty($item['cover_image_path'])
                        ? Storage::disk('public')->url($item['cover_image_path'])
                        : null;
                    $itUrl    = $item['link_url'] ?? null;
                    $itCta    = $t($item['cta_tabs'] ?? '', $locale, $fallback);
                    $itCtaUrl = $item['cta_url'] ?? null;
                @endphp

                @if ($itUrl)
                    <a href="{{ $itUrl }}" class="group flex flex-col overflow-hidden" style="{{ $cardBgStyle }}">
                @else
                    <div class="group flex flex-col overflow-hidden" style="{{ $cardBgStyle }}">
                @endif

                    @if ($itImg)
                        <div class="aspect-[16/9] overflow-hidden">
                            <img src="{{ $itImg }}" alt="{{ $itTitle }}"
                                class="h-full w-full object-cover transition duration-300 {{ $itUrl ? 'group-hover:scale-[1.02]' : '' }}" />
                        </div>
                    @endif

                    <div class="pt-6 pb-4 flex flex-col flex-1">
                        @if ($itKicker)
                            <div class="{{ $fwcItemKicker }} font-semibold uppercase tracking-widest text-slate-500 mb-2">
                                {{ $itKicker }}
                            </div>
                        @endif
                        @if ($itTitle)
                            <div class="{{ $fwcItemTitle }} font-light tracking-tight text-slate-800 mb-3 {{ $itUrl ? 'group-hover:underline' : '' }}">
                                {{ $itTitle }}
                            </div>
                        @endif
                        @if ($itExc)
                            <p class="{{ $fwcItemBody }} text-slate-600 leading-relaxed flex-1">
                                {{ $itExc }}
                            </p>
                        @endif
                        @if ($itCta && $itCtaUrl)
                            <div class="mt-5">
                                <a href="{{ $itCtaUrl }}"
                                   class="{{ $fwcItemCta }} font-medium text-blue-600 inline-flex items-center gap-2 hover:gap-3 transition-all duration-200">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="1.5"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 12h6m0 0l-3-3m3 3l-3 3"/>
                                    </svg>
                                    {{ $itCta }}
                                </a>
                            </div>
                        @endif
                    </div>

                @if ($itUrl)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    </section>
@endif