@php
    $type = $block['type'] ?? null;
    $data = $block['data'] ?? [];
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');
@endphp

@if ($type === 'hero')
    @php
        $height = $data['height'] ?? 'screen'; // screen|xl|lg
        $pos = $data['content_position'] ?? 'left'; // left|center|right
        $align = $data['content_align'] ?? 'left'; // left|center|right

        $overlayColor = $data['overlay_color'] ?? '#000000';
        $overlayOpacity = is_numeric($data['overlay_opacity'] ?? null) ? (float) $data['overlay_opacity'] : 0.45;
        $overlayOpacity = max(0, min(1, $overlayOpacity));

        $videoPath = $data['video'] ?? null;
        $videoUrl = $videoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($videoPath) : null;

        $thumbs = is_array($data['thumbs'] ?? null) ? $data['thumbs'] : [];

        $images = $data['images'] ?? [];
        $imageUrls = collect(is_array($images) ? $images : [])
            ->map(fn ($p) => $p ? \Illuminate\Support\Facades\Storage::disk('public')->url($p) : null)
            ->filter()
            ->values()
            ->all();

        $slides = $data['slides'] ?? [];
        $slides = is_array($slides) ? $slides : [];

        $autoplay = (bool) ($data['autoplay'] ?? true);
        $interval = (int) ($data['interval_ms'] ?? 4500);
        $pauseOnHover = (bool) ($data['pause_on_hover'] ?? true);

        $heightClass = match($height) {
            'xl' => 'gt-hero--xl',
            'lg' => 'gt-hero--lg',
            default => 'gt-hero--screen',
        };

        $contentPosClass = match($pos) {
            'center' => 'gt-hero__content--center',
            'right' => 'gt-hero__content--right',
            default => 'gt-hero__content--left',
        };

        $textAlignClass = match($align) {
            'center' => 'text-center',
            'right' => 'text-right',
            default => 'text-left',
        };
        $titleSize = $data['title_size'] ?? 'xl';
        $leadSize = $data['lead_size'] ?? 'md';
        $maxW = is_numeric($data['content_max_width'] ?? null) ? (int) $data['content_max_width'] : 760;
        $offX = is_numeric($data['content_offset_x'] ?? null) ? (int) $data['content_offset_x'] : 140;
        $offY = is_numeric($data['content_offset_y'] ?? null) ? (int) $data['content_offset_y'] : -90;

        $titleClass = match($titleSize) {
            'md' => 'gt-hero__title--md',
            'lg' => 'gt-hero__title--lg',
            default => 'gt-hero__title--xl',
        };
        $leadClass = match($leadSize) {
            'sm' => 'gt-hero__lead--sm',
            'lg' => 'gt-hero__lead--lg',
            default => 'gt-hero__lead--md',
        };
    @endphp

    <section class="gt-hero {{ $heightClass }}"
             data-hero
             data-hero-autoplay="{{ $autoplay ? '1' : '0' }}"
             data-hero-interval="{{ $interval }}"
             data-hero-pause-hover="{{ $pauseOnHover ? '1' : '0' }}">
        <div class="gt-hero__media">
            @if ($videoUrl)
                <video class="gt-hero__video" autoplay muted loop playsinline>
                    <source src="{{ $videoUrl }}">
                </video>
            @elseif (count($imageUrls))
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
            @else
                <div class="gt-hero__placeholder"></div>
            @endif

            <div class="gt-hero__overlay" style="background: {{ $overlayColor }}; opacity: {{ $overlayOpacity }};"></div>

            {{-- content --}}
            @php
                // initial slide content
                $s0 = $slides[0] ?? [];
                $kicker = $s0['kicker'] ?? '';
                $title = $s0['title'] ?? '';
                $lead = $s0['lead'] ?? '';
                $ctaLabel = $s0['cta_label'] ?? null;
                $ctaUrl = $s0['cta_url'] ?? null;
            @endphp

            <div class="gt-hero__content {{ $contentPosClass }} {{ $textAlignClass }}"
                 style="max-width: {{ $maxW }}px; transform: translate({{ $offX }}px, {{ $offY }}px);"
                 data-hero-content
                 data-hero-slides='@json($slides, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)'>
                @if ($kicker)
                    <div class="gt-hero__kicker" data-hero-kicker>{{ $kicker }}</div>
                @else
                    <div class="gt-hero__kicker hidden" data-hero-kicker></div>
                @endif

                <h1 class="gt-hero__title {{ $titleClass }}" data-hero-title>{{ $title }}</h1>

                @if ($lead)
                    <p class="gt-hero__lead {{ $leadClass }} {{ $lead ? '' : 'hidden' }}" data-hero-lead>{{ $lead }}</p>
                @else
                    <p class="gt-hero__lead hidden" data-hero-lead></p>
                @endif

                <div class="gt-hero__cta" data-hero-cta-wrap>
                    @if ($ctaLabel && $ctaUrl)
                        <a href="{{ $ctaUrl }}" class="gt-btn gt-btn--primary" data-hero-cta>{{ $ctaLabel }}</a>
                    @else
                        <a href="#" class="gt-btn gt-btn--primary hidden" data-hero-cta></a>
                    @endif
                </div>
            </div>
        </div>
    </section>

@elseif ($type === 'sectionHeading')
    @php
        $title = $data['title'] ?? '';
        $lead = $data['lead'] ?? '';
    @endphp

    <section>
        <h2 class="text-2xl md:text-3xl font-semibold tracking-tight">{{ $title }}</h2>
        @if ($lead)
            <p class="mt-3 text-slate-600 max-w-3xl">{{ $lead }}</p>
        @endif
    </section>

@elseif ($type === 'insightsGrid')
    @php
        $heading = $data['heading'] ?? 'Company insights';
        $accent = $data['accent'] ?? 'blue';

        $panelClass = match($accent) {
            'dark' => 'bg-slate-900 text-white',
            'slate' => 'bg-slate-700 text-white',
            default => 'bg-sky-600 text-white',
        };

        $topImgPath = $data['top_left_image'] ?? null;
        $topImgUrl = $topImgPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($topImgPath) : null;

        $kicker = $data['top_right_kicker'] ?? '';
        $title = $data['top_right_title'] ?? '';
        $text = $data['top_right_text'] ?? '';
        $ctaLabel = $data['top_right_cta_label'] ?? '';
        $ctaUrl = $data['top_right_cta_url'] ?? '';

        $bottom = is_array($data['bottom_tiles'] ?? null) ? $data['bottom_tiles'] : [];

        $panelTextColor = $data['panel_text_color'] ?? '#ffffff';
        $row2LinkColor = $data['row2_link_color'] ?? '#0ea5e9';
    @endphp

    <section class="gt-insights">
        <h2 class="gt-insights__heading">{{ $heading }}</h2>

        {{-- Row 1 --}}
        <div class="gt-insights__row gt-insights__row--top">
            <div class="gt-insights__tile gt-insights__tile--image1">
                @if ($topImgUrl)
                    <img src="{{ $topImgUrl }}" alt="" class="gt-insights__img">
                @endif
            </div>

            <div class="gt-insights__tile gt-insights__tile--panel1 {{ $panelClass }}" style="color: {{ $panelTextColor }};">
                @if ($kicker)
                    <div class="gt-insights__kicker">{{ $kicker }}</div>
                @endif
                <div class="gt-insights__title">{{ $title }}</div>
                @if ($text)
                    <div class="gt-insights__text">{{ $text }}</div>
                @endif
                @if ($ctaLabel && $ctaUrl)
                    <a href="{{ $ctaUrl }}" class="gt-insights__cta">{{ $ctaLabel }}</a>
                @endif
            </div>
        </div>

        {{-- Row 2 --}}
        <div class="gt-insights__row gt-insights__row--bottom">
            @foreach ($bottom as $tile)
                @php
                    $t = $tile['type'] ?? 'image';

                    $imgPath = $tile['image'] ?? null;
                    $imgUrl = $imgPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($imgPath) : null;

                    // image tile
                    $k = $tile['kicker'] ?? '';
                    $tt = $tile['title'] ?? '';
                    $lead = $tile['lead'] ?? '';
                    $cl = $tile['cta_label'] ?? '';
                    $cu = $tile['cta_url'] ?? '';
                @endphp

                @if ($t === 'panel')
                    @php
                        $tx = $tile['panel_excerpt'] ?? '';
                        $body = $tile['panel_body'] ?? '';
                        $showChart = (bool)($tile['show_chart'] ?? false);
                        $pts = is_array($tile['chart_points'] ?? null) ? $tile['chart_points'] : [];
                        $scale = $tile['chart_scale'] ?? 'linear';
                        $mode = $tile['chart_mode'] ?? 'absolute';
                        $auto = (bool)($tile['chart_auto_minmax'] ?? true);
                        $minFixed = $tile['chart_min'] ?? null;
                        $maxFixed = $tile['chart_max'] ?? null;
                    @endphp
                    <div class="gt-insights__tile gt-insights__tile--panel2 {{ $panelClass }}" style="color: {{ $panelTextColor }};">
                        @if($showChart)
                            <div class="mb-3 opacity-90">
                            @include('shared.blocks.partials.sparkline', [
                                'points' => $pts,
                                'scale' => $scale,
                                'mode' => $mode,
                                'auto' => $auto,
                                'minFixed' => $minFixed,
                                'maxFixed' => $maxFixed,
                            ])
                            </div>
                        @endif
                        @if($tt)<div class="gt-insights__title">{{ $tt }}</div>@endif
                        @if ($tx || $lead)<div class="gt-insights__text">{{ $tx }}</div>@endif
                        @if($body)<div class="gt-insights__text">{{ $body }}</div>@endif
                        @if ($cl && $cu)
                            <a href="{{ $cu }}" class="gt-insights__cta">{{ $cl }}</a>
                        @endif
                    </div>
                @else
                    <div class="gt-insights__tile gt-insights__tile--image gt-insights__tile--image2">
                        <div class="gt-insights__media">
                            @if ($imgUrl)
                                <img src="{{ $imgUrl }}" alt="" class="gt-insights__img">
                            @endif
                        </div>
                        <div class="gt-insights__below">
                            @if ($k)
                                <div class="gt-insights__belowKicker">{{ $k }}</div>
                            @endif

                            <div class="gt-insights__belowTitle">{{ $tt }}</div>

                            @if ($lead)
                                <div class="gt-insights__belowText">{{ $lead }}</div>
                            @endif

                            @if ($cl && $cu)
                                <a href="{{ $cu }}" class="gt-insights__belowCta" style="color: {{ $row2LinkColor }};">
                                    {{ $cl }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </section>

@elseif ($type === 'cardsCarousel')
    @php
        $bg = $data['bg'] ?? 'white';
        $title = $data['title'] ?? '';
        $lead = $data['lead'] ?? '';
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];

        $titleSize = $data['title_size'] ?? 'lg';
        $textSize = $data['text_size'] ?? 'md';

        $autoplay = (bool) ($data['autoplay'] ?? false);
        $autoplayMs = (int) ($data['autoplay_ms'] ?? 4500);
        $pauseHover = (bool) ($data['pause_on_hover'] ?? true);

        $wrapClass = match($bg) {
            'dark' => 'bg-slate-900 text-white border-white/10',
            'slate' => 'bg-slate-50 text-slate-900 border-slate-200',
            default => 'bg-white text-slate-900 border-slate-200',
        };

        $titleClass = $titleSize === 'md' ? 'text-xl md:text-2xl' : 'text-2xl md:text-3xl';
        $textClass = $textSize === 'sm' ? 'text-sm' : 'text-base';

        $id = 'cc_' . substr(md5(json_encode($data)), 0, 8);
    @endphp

    <section class="mt-8 rounded-2xl border {{ $wrapClass }} p-6 md:p-10"
             data-carousel
             data-carousel-autoplay="{{ $autoplay ? '1' : '0' }}"
             data-carousel-interval="{{ max(1500, $autoplayMs) }}"
             data-carousel-pause-hover="{{ $pauseHover ? '1' : '0' }}">
        @if ($title)
            <h3 class="font-semibold tracking-tight {{ $titleClass }}">{{ $title }}</h3>
        @endif
        @if ($lead)
            <p class="mt-2 opacity-90 max-w-3xl {{ $textClass }}">{{ $lead }}</p>
        @endif

        <div class="mt-6 relative">
            <button type="button" class="gt-car__nav gt-car__nav--prev" data-carousel-prev aria-label="Previous">‹</button>
            <button type="button" class="gt-car__nav gt-car__nav--next" data-carousel-next aria-label="Next">›</button>

            <div class="gt-car__track" data-carousel-track>
                @foreach ($items as $it)
                    @php
                        $img = $it['image'] ?? null;
                        $imgUrl = $img ? \Illuminate\Support\Facades\Storage::disk('public')->url($img) : null;
                        $t = $it['title'] ?? '';
                        $txt = $it['text'] ?? '';
                        $url = $it['url'] ?? null;
                    @endphp

                    <a class="gt-car__card {{ $bg === 'dark' ? 'gt-car__card--dark' : '' }}"
                       href="{{ $url ?: '#' }}"
                       {{ $url ? '' : 'tabindex=-1 aria-disabled=true' }}>
                        @if ($imgUrl)
                            <img src="{{ $imgUrl }}" alt="" class="gt-car__img">
                        @endif
                        <div class="gt-car__body">
                            <div class="gt-car__title">{{ $t }}</div>
                            @if ($txt)
                                <div class="gt-car__text">{{ $txt }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

@elseif ($type === 'split')
    @php
        $side = $data['image_side'] ?? 'left';
        $imgPath = $data['image'] ?? null;
        $imgUrl = $imgPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($imgPath) : null;
        $title = $data['title'] ?? '';
        $html = $data['html'] ?? '';
        $ctaLabel = $data['cta_label'] ?? null;
        $ctaUrl = $data['cta_url'] ?? null;
    @endphp

    <section class="grid md:grid-cols-2 gap-6 items-center">
        @if ($side === 'left')
            <div>
                @if ($imgUrl)
                    <img src="{{ $imgUrl }}" alt="" class="w-full rounded-2xl border border-slate-200" />
                @endif
            </div>
        @endif

        <div>
            <h3 class="text-xl md:text-2xl font-semibold tracking-tight">{{ $title }}</h3>
            <div class="mt-3 prose prose-slate max-w-none">
                {!! $html !!}
            </div>

            @if ($ctaLabel && $ctaUrl)
                <div class="mt-5">
                    <a href="{{ $ctaUrl }}" class="inline-flex rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                        {{ $ctaLabel }}
                    </a>
                </div>
            @endif
        </div>

        @if ($side === 'right')
            <div>
                @if ($imgUrl)
                    <img src="{{ $imgUrl }}" alt="" class="w-full rounded-2xl border border-slate-200" />
                @endif
            </div>
        @endif
    </section>

@elseif ($type === 'cards')
    @php
        $title = $data['title'] ?? '';
        $lead = $data['lead'] ?? '';
        $items = $data['items'] ?? [];
    @endphp

    <section>
        @if ($title)
            <h3 class="text-xl md:text-2xl font-semibold tracking-tight">{{ $title }}</h3>
        @endif
        @if ($lead)
            <p class="mt-2 text-slate-600 max-w-3xl">{{ $lead }}</p>
        @endif

        <div class="mt-6 grid md:grid-cols-3 gap-4">
            @foreach ($items as $it)
                @php
                    $imgPath = $it['image'] ?? null;
                    $imgUrl = $imgPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($imgPath) : null;
                    $t = $it['title'] ?? '';
                    $text = $it['text'] ?? '';
                    $url = $it['url'] ?? null;
                @endphp

                <a href="{{ $url ?: '#' }}"
                   class="block rounded-2xl border border-slate-200 bg-white p-5 hover:bg-slate-50 {{ $url ? '' : 'pointer-events-none' }}">
                    @if ($imgUrl)
                        <img src="{{ $imgUrl }}" alt="" class="mb-4 h-36 w-full object-cover rounded-xl border border-slate-100" />
                    @endif
                    <div class="font-semibold">{{ $t }}</div>
                    @if ($text)
                        <div class="mt-2 text-sm text-slate-600">{{ $text }}</div>
                    @endif
                </a>
            @endforeach
        </div>
    </section>

@elseif ($type === 'metrics')
    @php
        $bg = $data['bg'] ?? 'slate';
        $title = $data['title'] ?? '';
        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        $animate = (bool) ($data['animate'] ?? true);

        $wrapClass = match($bg) {
            'dark' => 'bg-slate-900 text-white border-white/10',
            'white' => 'bg-white text-slate-900 border-slate-200',
            default => 'bg-slate-50 text-slate-900 border-slate-200',
        };

        $n = max(1, count($items));

        // auto-size: fewer items => bigger numbers, more items => smaller
        $valueClass = $n <= 3 ? 'gt-m__val--xl' : ($n === 4 ? 'gt-m__val--lg' : 'gt-m__val--md');
    @endphp

    <section class="mt-8 rounded-2xl border {{ $wrapClass }} p-6 md:p-10">
        @if ($title)
            <h3 class="text-xl font-semibold tracking-tight">{{ $title }}</h3>
        @endif

        <div class="mt-5 grid gap-4"
             style="grid-template-columns: repeat({{ min($n, 4) }}, minmax(0, 1fr));">
            @foreach ($items as $it)
                @php
                    $raw = (string) ($it['value'] ?? '');
                    $label = (string) ($it['label'] ?? '');

                    // extract numeric part for animation, keep suffix (e.g. "12,000+")
                    $num = preg_replace('/[^0-9.]/', '', $raw);
                    $suffix = trim(str_replace($num, '', $raw));
                    $numVal = is_numeric($num) ? (float) $num : null;
                @endphp

                <div class="rounded-xl border {{ $bg === 'dark' ? 'border-white/10 bg-white/5' : 'border-slate-200 bg-white' }} p-4">
                    <div class="gt-m__value {{ $valueClass }}"
                         @if ($animate && $numVal !== null)
                             data-countup="{{ $numVal }}"
                             data-countup-suffix="{{ e($suffix) }}"
                         @endif
                    >
                        {{ $raw }}
                    </div>
                    <div class="mt-1 text-sm opacity-80">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </section>

@elseif ($type === 'mediaText')
    @php
        $side = $data['media_side'] ?? 'left';
        $mediaType = $data['media_type'] ?? 'image';
        $ratio = $data['media_width'] ?? '50-50';
        $maxH = is_numeric($data['media_max_h'] ?? null) ? (int)$data['media_max_h'] : null;

        $imgUrl = !empty($data['image']) ? Storage::disk('public')->url($data['image']) : null;
        $vidUrl = !empty($data['video']) ? Storage::disk('public')->url($data['video']) : null;
        $posterUrl = !empty($data['poster']) ? Storage::disk('public')->url($data['poster']) : null;

        $title = $data['title'] ?? '';
        $excerpt = $data['excerpt'] ?? '';
        $html = $data['body_html'] ?? '';
        $ctaLabel = $data['cta_label'] ?? null;
        $ctaUrl = $data['cta_url'] ?? null;

        // Ratio to Tailwind spans (12-col grid)
        [$mediaClass, $textClass] = match($ratio) {
            '30-70' => ['md:col-span-5',  'md:col-span-11'],
            '40-60' => ['md:col-span-7',  'md:col-span-9' ],
            '60-40' => ['md:col-span-9',  'md:col-span-7' ],
            '70-30' => ['md:col-span-11', 'md:col-span-5' ],
            default => ['md:col-span-8',  'md:col-span-8' ],  // 50-50
        };

        $mediaStyle = $maxH ? "max-height:{$maxH}px; height:{$maxH}px;" : '';
    @endphp

    <section class="grid md:grid-cols-16 gap-6 py-16 items-center">
        @if ($side === 'left')
            <div class="{{ $mediaClass }}">
                @include('shared.blocks.partials.media', ['mediaType' => $mediaType, 'imgUrl' => $imgUrl, 'vidUrl' => $vidUrl, 'posterUrl' => $posterUrl, 'mediaStyle' => $mediaStyle])
            </div>
        @endif

        <div class="{{ $textClass }}">
            <h3 class="text-xl md:text-2xl font-semibold tracking-tight">{{ $title }}</h3>
            @if ($excerpt)<p class="mt-2 text-slate-600">{{ $excerpt }}</p>@endif
            @if ($html)<div class="mt-3 prose prose-slate max-w-none">{!! $html !!}</div>@endif
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
                @include('shared.blocks.partials.media', ['mediaType' => $mediaType, 'imgUrl' => $imgUrl, 'vidUrl' => $vidUrl, 'posterUrl' => $posterUrl, 'mediaStyle' => $mediaStyle])
            </div>
        @endif
    </section>
@endif

@if ($type === 'richText')
    <div class="prose max-w-none">
        {!! $data['html'] ?? '' !!}
    </div>

@elseif ($type === 'image')
    @php
        $path = $data['path'] ?? null;
        $url = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
        $caption = $data['caption'] ?? '';
    @endphp

    @if ($url)
        <figure class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-50">
            <img src="{{ $url }}" alt="" class="w-full h-auto object-cover" />
            @if ($caption)
                <figcaption class="px-4 py-3 text-sm text-slate-600">{{ $caption }}</figcaption>
            @endif
        </figure>
    @endif

@elseif ($type === 'video')
    @php
        $path = $data['path'] ?? null;
        $url = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
        $caption = $data['caption'] ?? '';
    @endphp

    @if ($url)
        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-black">
            <video controls class="w-full h-auto">
                <source src="{{ $url }}" />
            </video>
        </div>
        @if ($caption)
            <div class="mt-2 text-sm text-slate-600">{{ $caption }}</div>
        @endif
    @endif

@elseif ($type === 'cta')
    @php
        $label = $data['label'] ?? 'Discover more';
        $url = $data['url'] ?? '#';
    @endphp

    <a href="{{ $url }}" class="inline-flex rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
        {{ $label }}
    </a>
@endif