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
     HERO (video OR image)
     ========================================================= --}}
@if ($type === 'hero')
@php
$publicHeroVideo    = config('home.hero_public_video');
$publicHeroVideoUrl = $publicHeroVideo ? url($publicHeroVideo) : null;
$minH       = $data['min_h'] ?? '100vh';
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
$textOffset    = (int)   ($data['text_offset_px'] ?? 290);
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
                <video class="h-full w-full object-cover opacity-100"
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
             style="background: linear-gradient(to bottom,
                rgba(0,0,0,{{ $overlayTop }}),
                rgba(0,0,0,{{ $overlayMid }}),
                rgba(0,0,0,{{ $overlayBottom }}));"></div>
    </div>
    <div class="relative mx-auto max-w-7xl px-4 hero-home__content">
        <div class="max-w-3xl">
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight leading-tight">
                {{ $title }}
            </h1>
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
            <a href="{{ $viewAllUrl }}"
               class="text-sm text-slate-600 hover:text-slate-900 hover:underline">View all →</a>
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
                $img   = $ind->cover_image_path
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
$title    = $t($data['title']  ?? []);
$text     = $t($data['text']   ?? []);
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
               class="group rounded-xl border border-slate-200 bg-white overflow-hidden hover:border-slate-300 hover:shadow-sm transition">
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

     $data['topics'] — exactly 5 items, indices 0-4:
       [0] left-top     — default source: instagram
       [1] left-bottom  — default source: instagram
       [2] center       — default source: linkedin  (tall card)
       [3] right-top    — default source: linkedin
       [4] right-bottom — default source: linkedin

     Each item fields:
       source        => 'instagram' | 'linkedin'  (must be exactly this string)
       image_path    => storage path              (optional)
       title         => ['en'=>'...']             (optional; used in center)
       text          => ['en'=>'...']
       profile_name  => 'Globaltrding'
       time_ago      => '3 days ago'
       original_url  => 'https://...'
       privacy_url   => '/{locale}/pages/privacy-policy'

     CONSENT RULES:
       Instagram: content is served from your own CMS storage.
         No personal data is sent to Instagram servers.
         These cards are ALWAYS fully visible — never gated.
       LinkedIn: external content. Consent overlay shown until
         user clicks Accept. JS manages this via .needs-consent.
     ========================================================= --}}
@elseif ($type === 'trending_topics')
@php

$sectionTitle = $t($data['title'] ?? ['en' => 'Trending Topics']);
$bgPath  = $data['background_image_path'] ?? null;
$bgUrl   = $bgPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($bgPath) : null;
$topics  = is_array($data['topics'] ?? null) ? array_values($data['topics']) : [];
for ($i = count($topics); $i < 5; $i++) $topics[$i] = [];

// Safe image URL helper
$getImg = function ($it) {
    $p = $it['image_path'] ?? null;
    return $p ? \Illuminate\Support\Facades\Storage::disk('public')->url($p) : null;
};

// Sanitise source: must be exactly 'instagram' or 'linkedin', never empty.
// JS uses getAttribute('data-source') for the consent gate logic.
$src = function ($it, string $default) : string {
    $s = strtolower(trim($it['source'] ?? ''));
    return in_array($s, ['instagram','linkedin'], true) ? $s : $default;
};

@endphp

<section class="tt-stage" data-tt>

    {{-- ── Confirm overlay ("Show original post") ─────────────── --}}
    <div class="tt-confirm hidden" data-tt-confirm aria-hidden="true">
        <div class="tt-confirm__dialog"
             role="dialog" aria-modal="true" aria-label="Leave page confirmation">
            <div class="tt-confirm__text">
                You will now be redirected to the selected social media channel.
            </div>
            <div class="tt-confirm__actions">
                <button type="button" class="tt-confirm__btn" data-tt-confirm-cancel>
                    Cancel
                </button>
                <button type="button" class="tt-confirm__btn tt-confirm__btn--primary"
                        data-tt-confirm-leave>
                    Leave page
                </button>
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

            {{-- ════════════════════════════════════════════
                 CARD 0 — LEFT TOP  (instagram by default)
                 ════════════════════════════════════════════ --}}
            @php
            $it0     = $topics[0] ?? [];
            $src0    = $src($it0, 'instagram');
            $img0    = $getImg($it0);
            $text0   = $t($it0['text']         ?? []);
            $orig0   = $urlWithLocale($it0['original_url']  ?? '#');
            $priv0   = $urlWithLocale($it0['privacy_url']   ?? '/{locale}/pages/privacy-policy');
            $time0   = (string) ($it0['time_ago']    ?? '—');
            $prof0   = (string) ($it0['profile_name']?? 'Globaltrding');
            @endphp
            <article class="tt-card tt-slot tt-slot--leftTop"
                     data-social-card data-tt-card
                     data-slot="leftTop"
                     data-source="{{ $src0 }}">

                {{-- Consent overlay — only shown when JS adds .needs-consent --}}
                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <div class="tt-consent__text">
                            I agree to the transmission of my personal data to
                            {{ $src0 === 'instagram' ? 'Instagram' : 'LinkedIn' }}
                            in order to be shown content provided by
                            {{ $src0 === 'instagram' ? 'Instagram' : 'LinkedIn' }}.
                            I have read the
                            <a href="{{ $priv0 }}" target="_blank" rel="noopener">privacy policy</a>.
                        </div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div>
                </div>

                <div class="tt-card__content">
                    <div class="tt-card__media">
                        @if ($img0)<img src="{{ $img0 }}" alt="" class="tt-card__img">@endif
                        <div class="tt-card__badge tt-card__badge--ig">IG</div>
                    </div>
                    <div class="tt-card__body">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $prof0 }}</span>
                            <span class="tt-card__time">{{ $time0 }}</span>
                        </div>
                        <div class="tt-card__scroll" data-tt-scroll>
                            <div class="tt-card__text">{{ $text0 }}</div>
                            <a class="tt-card__link" href="{{ $orig0 }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $orig0 }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
                            <span class="tt-card__downIcon">&#8964;</span>
                        </button>
                    </div>
                </div>
            </article>

            {{-- ════════════════════════════════════════════
                 CARD 1 — LEFT BOTTOM  (instagram by default)
                 ════════════════════════════════════════════ --}}
            @php
            $it1   = $topics[1] ?? [];
            $src1  = $src($it1, 'instagram');
            $img1  = $getImg($it1);
            $text1 = $t($it1['text'] ?? []);
            $orig1 = $urlWithLocale($it1['original_url'] ?? '#');
            $priv1 = $urlWithLocale($it1['privacy_url']  ?? '/{locale}/pages/privacy-policy');
            $time1 = (string) ($it1['time_ago']     ?? '—');
            $prof1 = (string) ($it1['profile_name'] ?? 'Globaltrding');
            @endphp
            <article class="tt-card tt-slot tt-slot--leftBottom"
                     data-social-card data-tt-card
                     data-slot="leftBottom"
                     data-source="{{ $src1 }}">

                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <div class="tt-consent__text">
                            I agree to the transmission of my personal data to
                            {{ $src1 === 'instagram' ? 'Instagram' : 'LinkedIn' }}
                            in order to be shown content provided by
                            {{ $src1 === 'instagram' ? 'Instagram' : 'LinkedIn' }}.
                            I have read the
                            <a href="{{ $priv1 }}" target="_blank" rel="noopener">privacy policy</a>.
                        </div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div>
                </div>

                <div class="tt-card__content">
                    <div class="tt-card__media">
                        @if ($img1)<img src="{{ $img1 }}" alt="" class="tt-card__img">@endif
                        <div class="tt-card__badge tt-card__badge--ig">IG</div>
                    </div>
                    <div class="tt-card__body">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $prof1 }}</span>
                            <span class="tt-card__time">{{ $time1 }}</span>
                        </div>
                        <div class="tt-card__scroll" data-tt-scroll>
                            <div class="tt-card__text">{{ $text1 }}</div>
                            <a class="tt-card__link" href="{{ $orig1 }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $orig1 }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
                            <span class="tt-card__downIcon">&#8964;</span>
                        </button>
                    </div>
                </div>
            </article>

            {{-- ════════════════════════════════════════════
                 CARD 2 — CENTER  (linkedin, tall)
                 ════════════════════════════════════════════
                 FIX: body gets tt-card__body--lg so it fills 100% height.
                 FIX: media block is conditional — only rendered when image exists.
                      Without image, badge floats top-right via absolute position
                      and body expands to fill the entire card.
                 ════════════════════════════════════════════ --}}
            @php
            $it2    = $topics[2] ?? [];
            $img2   = $getImg($it2);
            $text2  = $t($it2['text']  ?? []);
            $title2 = $t($it2['title'] ?? []);
            $orig2  = $urlWithLocale($it2['original_url'] ?? '#');
            $priv2  = $urlWithLocale($it2['privacy_url']  ?? '/{locale}/pages/privacy-policy');
            $time2  = (string) ($it2['time_ago']     ?? '—');
            $prof2  = (string) ($it2['profile_name'] ?? 'Globaltrding');
            @endphp
            <article class="tt-card tt-slot tt-slot--center"
                     data-social-card data-tt-card
                     data-slot="center"
                     data-source="linkedin">

                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <div class="tt-consent__text">
                            I agree to the transmission of my personal data to LinkedIn
                            in order to be shown content provided by LinkedIn.
                            I have read the
                            <a href="{{ $priv2 }}" target="_blank" rel="noopener">privacy policy</a>.
                        </div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div>
                </div>

                <div class="tt-card__content">

                    {{-- Media: only when image is provided.
                         Without an image the body fills 100% and badge floats in corner. --}}
                    @if ($img2)
                        <div class="tt-card__media">
                            <img src="{{ $img2 }}" alt="" class="tt-card__img">
                            <div class="tt-card__badge tt-card__badge--li">in</div>
                        </div>
                    @else
                        {{-- No image: position badge in top-right corner of the card --}}
                        <div class="tt-card__badge tt-card__badge--li"
                             style="position:absolute;top:8px;right:8px;z-index:3">in</div>
                    @endif

                    {{-- tt-card__body--lg: flex:1 fills remaining card height,
                         giving the scroll area room to grow and the down button room to show. --}}
                    <div class="tt-card__body tt-card__body--lg">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $prof2 }}</span>
                            <span class="tt-card__time">{{ $time2 }}</span>
                        </div>
                        @if ($title2)
                            <div class="tt-card__title">{{ $title2 }}</div>
                        @endif
                        <div class="tt-card__scroll" data-tt-scroll>
                            <div class="tt-card__text tt-card__text--lg">{{ $text2 }}</div>
                            <a class="tt-card__link" href="{{ $orig2 }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $orig2 }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
                            <span class="tt-card__downIcon">&#8964;</span>
                        </button>
                    </div>

                </div>
            </article>

            {{-- ════════════════════════════════════════════
                 CARD 3 — RIGHT TOP  (linkedin)
                 ════════════════════════════════════════════ --}}
            @php
            $it3   = $topics[3] ?? [];
            $src3  = $src($it3, 'linkedin');
            $img3  = $getImg($it3);
            $text3 = $t($it3['text'] ?? []);
            $orig3 = $urlWithLocale($it3['original_url'] ?? '#');
            $priv3 = $urlWithLocale($it3['privacy_url']  ?? '/{locale}/pages/privacy-policy');
            $time3 = (string) ($it3['time_ago']     ?? '—');
            $prof3 = (string) ($it3['profile_name'] ?? 'Globaltrding');
            @endphp
            <article class="tt-card tt-slot tt-slot--rightTop"
                     data-social-card data-tt-card
                     data-slot="rightTop"
                     data-source="{{ $src3 }}">

                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <div class="tt-consent__text">
                            I agree to the transmission of my personal data to LinkedIn
                            in order to be shown content provided by LinkedIn.
                            I have read the
                            <a href="{{ $priv3 }}" target="_blank" rel="noopener">privacy policy</a>.
                        </div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div>
                </div>

                <div class="tt-card__content">
                    <div class="tt-card__media">
                        @if ($img3)<img src="{{ $img3 }}" alt="" class="tt-card__img">@endif
                        <div class="tt-card__badge tt-card__badge--li">in</div>
                    </div>
                    <div class="tt-card__body">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $prof3 }}</span>
                            <span class="tt-card__time">{{ $time3 }}</span>
                        </div>
                        <div class="tt-card__scroll" data-tt-scroll>
                            <div class="tt-card__text">{{ $text3 }}</div>
                            <a class="tt-card__link" href="{{ $orig3 }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $orig3 }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
                            <span class="tt-card__downIcon">&#8964;</span>
                        </button>
                    </div>
                </div>
            </article>

            {{-- ════════════════════════════════════════════
                 CARD 4 — RIGHT BOTTOM  (linkedin)
                 ════════════════════════════════════════════ --}}
            @php
            $it4   = $topics[4] ?? [];
            $src4  = $src($it4, 'linkedin');
            $img4  = $getImg($it4);
            $text4 = $t($it4['text'] ?? []);
            $orig4 = $urlWithLocale($it4['original_url'] ?? '#');
            $priv4 = $urlWithLocale($it4['privacy_url']  ?? '/{locale}/pages/privacy-policy');
            $time4 = (string) ($it4['time_ago']     ?? '—');
            $prof4 = (string) ($it4['profile_name'] ?? 'Globaltrding');
            @endphp
            <article class="tt-card tt-slot tt-slot--rightBottom"
                     data-social-card data-tt-card
                     data-slot="rightBottom"
                     data-source="{{ $src4 }}">

                <div class="tt-card__consent">
                    <div class="tt-consent__box">
                        <div class="tt-consent__text">
                            I agree to the transmission of my personal data to LinkedIn
                            in order to be shown content provided by LinkedIn.
                            I have read the
                            <a href="{{ $priv4 }}" target="_blank" rel="noopener">privacy policy</a>.
                        </div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div>
                </div>

                <div class="tt-card__content">
                    <div class="tt-card__media">
                        @if ($img4)<img src="{{ $img4 }}" alt="" class="tt-card__img">@endif
                        <div class="tt-card__badge tt-card__badge--li">in</div>
                    </div>
                    <div class="tt-card__body">
                        <div class="tt-card__meta">
                            <span class="tt-card__profile">{{ $prof4 }}</span>
                            <span class="tt-card__time">{{ $time4 }}</span>
                        </div>
                        <div class="tt-card__scroll" data-tt-scroll>
                            <div class="tt-card__text">{{ $text4 }}</div>
                            <a class="tt-card__link" href="{{ $orig4 }}" target="_blank" rel="noopener"
                               data-tt-original data-url="{{ $orig4 }}">Show original post</a>
                        </div>
                        <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
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