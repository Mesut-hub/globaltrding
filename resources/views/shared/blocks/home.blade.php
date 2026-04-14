@php
$type = $block['type'] ?? null;
$data = $block['data'] ?? [];
$locale   = app()->getLocale();
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
$posterUrl  = $posterPath ? Storage::disk('public')->url($posterPath) : null;
@endphp

{{-- =========================================================
     HERO
     ========================================================= --}}
@if ($type === 'hero')
@php
$publicHeroVideo    = config('home.hero_public_video');
$publicHeroVideoUrl = $publicHeroVideo ? url($publicHeroVideo) : null;
$title      = $t($data['title']    ?? []);
$subtitle   = $t($data['subtitle'] ?? []);
$cta1Label  = $t($data['cta1_label'] ?? []);
$cta1Url    = $urlWithLocale($data['cta1_url'] ?? null);
$cta2Label  = $t($data['cta2_label'] ?? []);
$cta2Url    = $urlWithLocale($data['cta2_url'] ?? null);
$mediaType  = $data['media_type'] ?? 'video';
$mediaPath  = $data['media_path'] ?? null;
$mediaUrl   = $mediaPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($mediaPath) : null;
$overlayTop    = (float) ($data['overlay_top']    ?? 0.45);
$overlayMid    = (float) ($data['overlay_mid']    ?? 0.15);
$overlayBottom = (float) ($data['overlay_bottom'] ?? 0.55);
@endphp
<section class="relative text-white hero-shell" data-hero>
    <div class="absolute inset-0 overflow-hidden bg-slate-950">
        @if ($mediaType === 'image')
            @if ($mediaUrl)
                <img src="{{ $mediaUrl }}" class="h-full w-full object-cover" alt="">
            @elseif ($posterUrl)
                <img src="{{ $posterUrl }}" class="h-full w-full object-cover" alt="">
            @endif
        @else
            @php $videoSrc = $publicHeroVideoUrl ?: $mediaUrl; @endphp
            @if ($videoSrc)
                <video class="h-full w-full object-cover"
                       autoplay muted loop playsinline preload="metadata"
                       @if($posterUrl) poster="{{ $posterUrl }}" @endif>
                    <source src="{{ $videoSrc }}" type="video/mp4">
                </video>
            @endif
            @if ($posterUrl)
                <img src="{{ $posterUrl }}" alt=""
                     class="absolute inset-0 h-full w-full object-cover"
                     style="{{ $mediaUrl ? 'display:none;' : '' }}"
                     data-hero-poster>
            @endif
        @endif
        <div class="absolute inset-0"
             style="background:linear-gradient(to bottom,
                rgba(0,0,0,{{ $overlayTop }}),
                rgba(0,0,0,{{ $overlayMid }}),
                rgba(0,0,0,{{ $overlayBottom }}));"></div>
    </div>
    <div class="relative mx-auto max-w-7xl px-4 hero-home__content">
        <div class="max-w-3xl">
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight leading-tight">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-5 text-slate-200 text-lg">{{ $subtitle }}</p>
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
    <a href="/{{ $locale }}/collaboration" class="floating-mail"
       aria-label="Collaboration" title="Collaboration">✉</a>
    <style>@media (prefers-reduced-motion:reduce){video{display:none}}</style>
</section>

{{-- =========================================================
     MARKET BELT
     ========================================================= --}}
@elseif ($type === 'market_belt')
@php
$beltSlugs = 'usd-try,eur-try,gbp-try';
$dataUrl   = "/{$locale}/market/data?instruments=" . urlencode($beltSlugs);
@endphp
<section class="border-b border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-3">
        <div class="flex flex-wrap items-center gap-2"
             data-market-belt data-market-url="{{ $dataUrl }}">
            @foreach (explode(',', $beltSlugs) as $slug)
                @php
                $labels = ['usd-try'=>'USD/TRY','eur-try'=>'EUR/TRY',
                           'gbp-try'=>'GBP/TRY','gold-gram-try'=>'Gold (g)','brent-usd'=>'Brent'];
                @endphp
                <a href="/{{ $locale }}/market?instrument={{ $slug }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                   data-instrument="{{ $slug }}">
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
     INDUSTRIES SLIDER
     ========================================================= --}}
@elseif ($type === 'industries_slider')
@php
$sectionTitle = $t($data['title'] ?? ['en' => 'Industries']);
$viewAllUrl   = $urlWithLocale($data['view_all_url'] ?? '/{locale}/industries');
$industries   = \App\Models\Industry::query()
    ->where('is_published', true)
    ->orderBy('sort_order')
    ->limit(12)->get();
@endphp
<section class="mx-auto max-w-7xl px-4 py-12" data-industry-slider>
    <div class="flex items-end justify-between gap-4">
        <h2 class="text-2xl font-semibold tracking-tight">{{ $sectionTitle }}</h2>
        <div class="flex items-center gap-3">
            <a href="{{ $viewAllUrl }}" class="text-sm text-slate-600 hover:underline">View all →</a>
            <button type="button" class="ind-btn" data-ind="prev" aria-label="Previous">‹</button>
            <button type="button" class="ind-btn" data-ind="next" aria-label="Next">›</button>
        </div>
    </div>
    <div class="mt-6 overflow-hidden">
        <div class="flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-2"
             data-ind="track">
            @foreach ($industries as $ind)
                @php
                $title = data_get($ind->title, $locale) ?: data_get($ind->title, $fallback) ?: $ind->slug;
                $img   = $ind->cover_image_path
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($ind->cover_image_path)
                    : null;
                @endphp
                <a href="/{{ $locale }}/industries/{{ $ind->slug }}"
                   class="snap-start shrink-0 w-[85%] sm:w-[45%] lg:w-[28%] rounded-xl border border-slate-200 bg-white overflow-hidden hover:shadow-sm transition">
                    <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                        @if ($img)
                            <img src="{{ $img }}" alt="{{ $title }}"
                                 class="h-full w-full object-cover hover:scale-[1.015] transition" />
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="text-xl font-light tracking-tight">{{ $title }}</div>
                        <div class="mt-2 text-sm text-slate-700 hover:underline">Discover more →</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- =========================================================
     CTA
     ========================================================= --}}
@elseif ($type === 'cta')
@php
$title    = $t($data['title'] ?? []);
$text     = $t($data['text']  ?? []);
$btnLabel = $t($data['button_label'] ?? []);
$btnUrl   = $urlWithLocale($data['button_url'] ?? '#');
@endphp
<section class="mx-auto max-w-7xl px-4 pb-12">
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 sm:p-10">
        <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
            <div class="lg:col-span-8">
                <h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>
                @if ($text)<p class="mt-3 text-slate-600">{{ $text }}</p>@endif
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
     CARDS GRID
     ========================================================= --}}
@elseif ($type === 'cards')
@php
$title = $t($data['title'] ?? []);
$items = $data['items'] ?? [];
@endphp
<section class="mx-auto max-w-7xl px-4 py-12">
    @if ($title)<h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>@endif
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($items as $item)
            @php
            $itemTitle = $t($item['title'] ?? []);
            $itemText  = $t($item['text']  ?? []);
            $itemUrl   = $urlWithLocale($item['url'] ?? '#');
            $imgUrl    = ($item['image_path'] ?? null)
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($item['image_path'])
                : null;
            @endphp
            <a href="{{ $itemUrl }}"
               class="group rounded-xl border border-slate-200 bg-white overflow-hidden hover:shadow-sm transition">
                <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                    @if ($imgUrl)
                        <img src="{{ $imgUrl }}" alt="{{ $itemTitle }}"
                             class="h-full w-full object-cover group-hover:scale-[1.015] transition" />
                    @endif
                </div>
                <div class="p-4">
                    <div class="text-lg font-semibold leading-snug">{{ $itemTitle }}</div>
                    @if ($itemText)<div class="mt-2 text-sm text-slate-600">{{ $itemText }}</div>@endif
                    <div class="mt-3 text-sm text-slate-700 group-hover:underline">Find out more →</div>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- =========================================================
     TRENDING TOPICS
     =========================================================

     $data['topics'] — array of exactly 5 items, in this order:
       [0] left-top     (source: instagram, by default)
       [1] left-bottom  (source: instagram, by default)
       [2] center       (source: linkedin,  tall card)
       [3] right-top    (source: linkedin)
       [4] right-bottom (source: linkedin)

     Each item:
       source        => 'instagram' | 'linkedin'
       image_path    => storage path (optional for center)
       title         => ['en' => '...']  (optional, shown in center)
       text          => ['en' => '...']
       profile_name  => 'Globaltrding'
       time_ago      => '3 days ago'
       original_url  => 'https://...'
       privacy_url   => '/{locale}/pages/privacy-policy'

     IMPORTANT:
     • data-source MUST be exactly "instagram" or "linkedin" (lowercase).
       JS uses getAttribute('data-source') to decide gating.
     • Instagram cards are never gated — their content is served
       locally from your CMS, no external data transfer.
     • LinkedIn cards show the consent gate until the user accepts.
     ========================================================= --}}
@elseif ($type === 'trending_topics')
@php

$sectionTitle = $t($data['title'] ?? ['en' => 'Trending Topics']);
$bgPath  = $data['background_image_path'] ?? null;
$bgUrl   = $bgPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($bgPath) : null;
$topics  = is_array($data['topics'] ?? null) ? array_values($data['topics']) : [];
for ($i = count($topics); $i < 5; $i++) $topics[$i] = [];

// Returns a normalised array of card variables for topic at index $idx.
// $defaultSource is the fallback if no source is set in the CMS.
$cv = function (int $idx, string $defaultSource) use ($topics, $t, $urlWithLocale) {
    $it  = $topics[$idx] ?? [];
    $src = strtolower(trim($it['source'] ?? $defaultSource));
    // Ensure source is exactly 'instagram' or 'linkedin' — never empty
    if (!in_array($src, ['instagram', 'linkedin'], true)) $src = $defaultSource;

    $imgPath = $it['image_path'] ?? null;
    $imgUrl  = $imgPath
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($imgPath)
        : null;

    return [
        'src'     => $src,
        'img'     => $imgUrl,
        'title'   => $t($it['title']   ?? []),
        'text'    => $t($it['text']    ?? []),
        'orig'    => $urlWithLocale($it['original_url'] ?? '#'),
        'privacy' => $urlWithLocale($it['privacy_url']  ?? '/{locale}/pages/privacy-policy'),
        'timeAgo' => (string) ($it['time_ago']     ?? '—'),
        'profile' => (string) ($it['profile_name'] ?? 'Globaltrding'),
    ];
};

// Source display name for consent text
$srcName = fn(string $s): string => match($s) {
    'instagram' => 'Instagram',
    'linkedin'  => 'LinkedIn',
    default     => ucfirst($s),
};

@endphp

<section class="tt-stage" data-tt>

    {{-- ── "Leave page" confirm overlay ─────────────────────── --}}
    {{-- Shown when user clicks "Show original post"            --}}
    <div class="tt-confirm hidden" data-tt-confirm
         aria-hidden="true" role="dialog" aria-modal="true"
         aria-label="Leave page confirmation">
        <div class="tt-confirm__dialog">
            <p class="tt-confirm__text">
                You will now be redirected to the selected social media channel.
            </p>
            <div class="tt-confirm__actions">
                <button type="button" class="tt-confirm__btn"
                        data-tt-confirm-cancel>cancel</button>
                <button type="button" class="tt-confirm__btn tt-confirm__btn--primary"
                        data-tt-confirm-leave>leave page</button>
            </div>
        </div>
    </div>

    {{-- ── Background ─────────────────────────────────────────── --}}
    <div class="tt-stage__bg">
        @if ($bgUrl)
            <img src="{{ $bgUrl }}" alt="" class="tt-stage__bgImg">
        @else
            <div class="tt-stage__bgFallback"></div>
        @endif
        <div class="tt-stage__bgOverlay"></div>
    </div>

    {{-- ── 3-D scene ──────────────────────────────────────────── --}}
    <div class="tt-stage__inner">
        <div class="tt-rig">

            {{-- ══════════════════════════════════════════════════
                 CARD 0 — LEFT TOP  (instagram by default)
                 ══════════════════════════════════════════════════ --}}
            @php $c = $cv(0, 'instagram'); @endphp
            <article class="tt-card tt-slot tt-slot--leftTop"
                     data-social-card data-tt-card
                     data-slot="leftTop"
                     data-source="{{ $c['src'] }}">

                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <p class="tt-consent__text">
                            I agree to the transmission of my personal data to
                            {{ $srcName($c['src']) }} in order to be shown content
                            provided by {{ $srcName($c['src']) }}.
                            I have read the
                            <a href="{{ $c['privacy'] }}" target="_blank" rel="noopener">privacy policy</a>.
                        </p>
                        <button type="button" class="tt-consent__btn" data-social-accept>Accept</button>
                    </div>
                </div>

                <div class="tt-card__content">
                    <div class="tt-card__media">
                        @if ($c['img'])<img src="{{ $c['img'] }}" alt="" class="tt-card__img">@endif
                        <div class="tt-card__badge tt-card__badge--ig">IG</div>
                    </div>
                    <div class="tt-card__body">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $c['profile'] }}</span>
                            <span class="tt-card__time">{{ $c['timeAgo'] }}</span>
                        </div>
                        <div class="tt-card__scroll" data-tt-scroll>
                            <p class="tt-card__text">{{ $c['text'] }}</p>
                            <a class="tt-card__link"
                               href="{{ $c['orig'] }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $c['orig'] }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down"
                                aria-label="Scroll down" data-tt-down>
                            <span class="tt-card__downIcon">&#8964;</span>
                        </button>
                    </div>
                </div>
            </article>

            {{-- ══════════════════════════════════════════════════
                 CARD 1 — LEFT BOTTOM  (instagram by default)
                 ══════════════════════════════════════════════════ --}}
            @php $c = $cv(1, 'instagram'); @endphp
            <article class="tt-card tt-slot tt-slot--leftBottom"
                     data-social-card data-tt-card
                     data-slot="leftBottom"
                     data-source="{{ $c['src'] }}">

                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <p class="tt-consent__text">
                            I agree to the transmission of my personal data to
                            {{ $srcName($c['src']) }} in order to be shown content
                            provided by {{ $srcName($c['src']) }}.
                            I have read the
                            <a href="{{ $c['privacy'] }}" target="_blank" rel="noopener">privacy policy</a>.
                        </p>
                        <button type="button" class="tt-consent__btn" data-social-accept>Accept</button>
                    </div>
                </div>

                <div class="tt-card__content">
                    <div class="tt-card__media">
                        @if ($c['img'])<img src="{{ $c['img'] }}" alt="" class="tt-card__img">@endif
                        <div class="tt-card__badge tt-card__badge--ig">IG</div>
                    </div>
                    <div class="tt-card__body">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $c['profile'] }}</span>
                            <span class="tt-card__time">{{ $c['timeAgo'] }}</span>
                        </div>
                        <div class="tt-card__scroll" data-tt-scroll>
                            <p class="tt-card__text">{{ $c['text'] }}</p>
                            <a class="tt-card__link"
                               href="{{ $c['orig'] }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $c['orig'] }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down"
                                aria-label="Scroll down" data-tt-down>
                            <span class="tt-card__downIcon">&#8964;</span>
                        </button>
                    </div>
                </div>
            </article>

            {{-- ══════════════════════════════════════════════════
                 CARD 2 — CENTER  (linkedin, tall)
                 ══════════════════════════════════════════════════ --}}
            @php $c = $cv(2, 'linkedin'); @endphp
            <article class="tt-card tt-slot tt-slot--center"
                     data-social-card data-tt-card
                     data-slot="center"
                     data-source="linkedin">

                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <p class="tt-consent__text">
                            I agree to the transmission of my personal data to LinkedIn
                            in order to be shown content provided by LinkedIn.
                            I have read the
                            <a href="{{ $c['privacy'] }}" target="_blank" rel="noopener">privacy policy</a>.
                        </p>
                        <button type="button" class="tt-consent__btn" data-social-accept>Accept</button>
                    </div>
                </div>

                <div class="tt-card__content">
                    {{-- Media: only rendered when an image exists.
                         Without an image the body expands to fill the full card. --}}
                    @if ($c['img'])
                        <div class="tt-card__media">
                            <img src="{{ $c['img'] }}" alt="" class="tt-card__img">
                            <div class="tt-card__badge tt-card__badge--li">in</div>
                        </div>
                    @else
                        {{-- No image: badge floats top-right inside body space --}}
                        <div style="position:relative;height:0;overflow:visible">
                            <div class="tt-card__badge tt-card__badge--li"
                                 style="position:absolute;top:8px;right:8px;z-index:2">in</div>
                        </div>
                    @endif

                    {{-- tt-card__body--lg expands to fill remaining height --}}
                    <div class="tt-card__body tt-card__body--lg">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $c['profile'] }}</span>
                            <span class="tt-card__time">{{ $c['timeAgo'] }}</span>
                        </div>
                        @if ($c['title'])
                            <div class="tt-card__title">{{ $c['title'] }}</div>
                        @endif
                        <div class="tt-card__scroll" data-tt-scroll>
                            <p class="tt-card__text tt-card__text--lg">{{ $c['text'] }}</p>
                            <a class="tt-card__link"
                               href="{{ $c['orig'] }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $c['orig'] }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down"
                                aria-label="Scroll down" data-tt-down>
                            <span class="tt-card__downIcon">&#8964;</span>
                        </button>
                    </div>
                </div>
            </article>

            {{-- ══════════════════════════════════════════════════
                 CARD 3 — RIGHT TOP  (linkedin)
                 ══════════════════════════════════════════════════ --}}
            @php $c = $cv(3, 'linkedin'); @endphp
            <article class="tt-card tt-slot tt-slot--rightTop"
                     data-social-card data-tt-card
                     data-slot="rightTop"
                     data-source="{{ $c['src'] }}">

                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <p class="tt-consent__text">
                            I agree to the transmission of my personal data to
                            {{ $srcName($c['src']) }} in order to be shown content
                            provided by {{ $srcName($c['src']) }}.
                            I have read the
                            <a href="{{ $c['privacy'] }}" target="_blank" rel="noopener">privacy policy</a>.
                        </p>
                        <button type="button" class="tt-consent__btn" data-social-accept>Accept</button>
                    </div>
                </div>

                <div class="tt-card__content">
                    <div class="tt-card__media">
                        @if ($c['img'])<img src="{{ $c['img'] }}" alt="" class="tt-card__img">@endif
                        <div class="tt-card__badge tt-card__badge--li">in</div>
                    </div>
                    <div class="tt-card__body">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $c['profile'] }}</span>
                            <span class="tt-card__time">{{ $c['timeAgo'] }}</span>
                        </div>
                        <div class="tt-card__scroll" data-tt-scroll>
                            <p class="tt-card__text">{{ $c['text'] }}</p>
                            <a class="tt-card__link"
                               href="{{ $c['orig'] }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $c['orig'] }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down"
                                aria-label="Scroll down" data-tt-down>
                            <span class="tt-card__downIcon">&#8964;</span>
                        </button>
                    </div>
                </div>
            </article>

            {{-- ══════════════════════════════════════════════════
                 CARD 4 — RIGHT BOTTOM  (linkedin)
                 ══════════════════════════════════════════════════ --}}
            @php $c = $cv(4, 'linkedin'); @endphp
            <article class="tt-card tt-slot tt-slot--rightBottom"
                     data-social-card data-tt-card
                     data-slot="rightBottom"
                     data-source="{{ $c['src'] }}">

                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <p class="tt-consent__text">
                            I agree to the transmission of my personal data to
                            {{ $srcName($c['src']) }} in order to be shown content
                            provided by {{ $srcName($c['src']) }}.
                            I have read the
                            <a href="{{ $c['privacy'] }}" target="_blank" rel="noopener">privacy policy</a>.
                        </p>
                        <button type="button" class="tt-consent__btn" data-social-accept>Accept</button>
                    </div>
                </div>

                <div class="tt-card__content">
                    <div class="tt-card__media">
                        @if ($c['img'])<img src="{{ $c['img'] }}" alt="" class="tt-card__img">@endif
                        <div class="tt-card__badge tt-card__badge--li">in</div>
                    </div>
                    <div class="tt-card__body">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $c['profile'] }}</span>
                            <span class="tt-card__time">{{ $c['timeAgo'] }}</span>
                        </div>
                        <div class="tt-card__scroll" data-tt-scroll>
                            <p class="tt-card__text">{{ $c['text'] }}</p>
                            <a class="tt-card__link"
                               href="{{ $c['orig'] }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $c['orig'] }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down"
                                aria-label="Scroll down" data-tt-down>
                            <span class="tt-card__downIcon">&#8964;</span>
                        </button>
                    </div>
                </div>
            </article>

        </div>{{-- /.tt-rig --}}
    </div>{{-- /.tt-stage__inner --}}

    {{-- Bottom nav bar --}}
    <div class="tt-nav">
        <div class="tt-nav__bg"></div>
        <div class="tt-nav__inner">
            <div class="tt-nav__title">{{ $sectionTitle }}</div>
            <div class="tt-nav__labels">
                <span class="tt-nav__label is-active">#Trending</span>
            </div>
        </div>
    </div>

</section>

@endif