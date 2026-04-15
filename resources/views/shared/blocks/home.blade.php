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
@endphp

{{-- HERO --}}
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
$overlayTop    = (float)($data['overlay_top']    ?? 0.45);
$overlayMid    = (float)($data['overlay_mid']    ?? 0.15);
$overlayBottom = (float)($data['overlay_bottom'] ?? 0.55);
@endphp
<section class="relative text-white hero-shell" data-hero>
    <div class="absolute inset-0 overflow-hidden bg-slate-950">
        @if ($mediaType === 'image')
            @if ($mediaUrl)<img src="{{ $mediaUrl }}" class="h-full w-full object-cover" alt="">
            @elseif ($posterUrl)<img src="{{ $posterUrl }}" class="h-full w-full object-cover" alt="">@endif
        @else
            @php $videoSrc = $publicHeroVideoUrl ?: $mediaUrl; @endphp
            @if ($videoSrc)
                <video class="h-full w-full object-cover" autoplay muted loop playsinline preload="metadata"
                       @if($posterUrl) poster="{{ $posterUrl }}" @endif>
                    <source src="{{ $videoSrc }}" type="video/mp4">
                </video>
            @endif
            @if ($posterUrl)
                <img src="{{ $posterUrl }}" alt="" class="absolute inset-0 h-full w-full object-cover"
                     style="{{ $mediaUrl ? 'display:none;' : '' }}" data-hero-poster>
            @endif
        @endif
        <div class="absolute inset-0"
             style="background:linear-gradient(to bottom,rgba(0,0,0,{{ $overlayTop }}),rgba(0,0,0,{{ $overlayMid }}),rgba(0,0,0,{{ $overlayBottom }}));"></div>
    </div>
    <div class="relative mx-auto max-w-7xl px-4 hero-home__content">
        <div class="max-w-3xl">
            <h1 class="text-4xl sm:text-5xl font-semibold tracking-tight leading-tight">{{ $title }}</h1>
            @if ($subtitle)<p class="mt-5 text-slate-200 text-lg">{{ $subtitle }}</p>@endif
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $cta1Url }}" class="rounded-md bg-white px-5 py-2.5 text-slate-900 font-medium hover:bg-slate-100">{{ $cta1Label ?: 'Discover more' }}</a>
                <a href="{{ $cta2Url }}" class="rounded-md border border-white/30 px-5 py-2.5 font-medium hover:bg-white/10">{{ $cta2Label ?: 'Contact' }}</a>
            </div>
        </div>
    </div>
    <a href="/{{ $locale }}/collaboration" class="floating-mail" aria-label="Collaboration" title="Collaboration">✉</a>
    <style>@media (prefers-reduced-motion:reduce){video{display:none}}</style>
</section>

{{-- MARKET BELT --}}
@elseif ($type === 'market_belt')
@php $beltSlugs = 'usd-try,eur-try,gbp-try'; $dataUrl = "/{$locale}/market/data?instruments=".urlencode($beltSlugs); @endphp
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
            <a href="/{{ $locale }}/market" class="ml-auto text-sm text-slate-600 hover:underline">View market →</a>
        </div>
    </div>
</section>

{{-- INDUSTRIES SLIDER --}}
@elseif ($type === 'industries_slider')
@php
$sectionTitle = $t($data['title'] ?? ['en'=>'Industries']);
$viewAllUrl   = $urlWithLocale($data['view_all_url'] ?? '/{locale}/industries');
$industries   = \App\Models\Industry::query()->where('is_published',true)->orderBy('sort_order')->limit(12)->get();
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
        <div class="flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-2" data-ind="track">
            @foreach ($industries as $ind)
                @php
                $title = data_get($ind->title,$locale) ?: data_get($ind->title,$fallback) ?: $ind->slug;
                $img   = $ind->cover_image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($ind->cover_image_path) : null;
                @endphp
                <a href="/{{ $locale }}/industries/{{ $ind->slug }}" class="snap-start shrink-0 w-[85%] sm:w-[45%] lg:w-[28%] rounded-xl border border-slate-200 bg-white overflow-hidden hover:shadow-sm transition">
                    <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                        @if ($img)<img src="{{ $img }}" alt="{{ $title }}" class="h-full w-full object-cover hover:scale-[1.015] transition"/>@endif
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

{{-- CTA --}}
@elseif ($type === 'cta')
@php $title=$t($data['title']??[]); $text=$t($data['text']??[]); $btnLabel=$t($data['button_label']??[]); $btnUrl=$urlWithLocale($data['button_url']??'#'); @endphp
<section class="mx-auto max-w-7xl px-4 pb-12">
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

{{-- CARDS GRID --}}
@elseif ($type === 'cards')
@php $title=$t($data['title']??[]); $items=$data['items']??[]; @endphp
<section class="mx-auto max-w-7xl px-4 py-12">
    @if ($title)<h2 class="text-2xl font-semibold tracking-tight">{{ $title }}</h2>@endif
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($items as $item)
            @php
            $iTitle=$t($item['title']??[]); $iText=$t($item['text']??[]);
            $iUrl=$urlWithLocale($item['url']??'#');
            $iImg=($item['image_path']??null) ? \Illuminate\Support\Facades\Storage::disk('public')->url($item['image_path']) : null;
            @endphp
            <a href="{{ $iUrl }}" class="group rounded-xl border border-slate-200 bg-white overflow-hidden hover:shadow-sm transition">
                <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                    @if ($iImg)<img src="{{ $iImg }}" alt="{{ $iTitle }}" class="h-full w-full object-cover group-hover:scale-[1.015] transition"/>@endif
                </div>
                <div class="p-4">
                    <div class="text-lg font-semibold leading-snug">{{ $iTitle }}</div>
                    @if ($iText)<div class="mt-2 text-sm text-slate-600">{{ $iText }}</div>@endif
                    <div class="mt-3 text-sm text-slate-700 group-hover:underline">Find out more →</div>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════════════════════════
     TRENDING TOPICS
     ═══════════════════════════════════════════════════════════════════════════

     STRUCTURE (critical for correct 3D hit-boxes):
       .tt-stage                 overflow:hidden, NO perspective
         .tt-stage__bg           background + overlays
         .tt-stage__scene        perspective:1100px ONLY here
           .tt-rig               transform-style:preserve-3d, JS animates
             div.tt-slot         position:absolute, sized, transform moves hit-box
               article.tt-card  position:relative width:100% height:100%
                 .tt-card__consent  absolute overlay
                 .tt-card__content  flex column

     WHY two elements (.tt-slot wrapper + .tt-card inside):
       .tt-slot is position:absolute and carries the transform.
       In CSS, pointer-events hit-testing for position:absolute uses the
       TRANSFORMED bounding box (correct in all modern browsers).
       .tt-card is position:relative so it fills the slot naturally,
       and overflow:hidden clips the image/content to the card boundary.

     $data['topics'] — 5 items, indices 0-4:
       [0] left-top     default source: instagram
       [1] left-bottom  default source: instagram
       [2] center       default source: linkedin (tall)
       [3] right-top    default source: linkedin
       [4] right-bottom default source: linkedin

     Each item: source, image_path, title (optional), text,
                profile_name, time_ago, original_url, privacy_url
     ═══════════════════════════════════════════════════════════════════════════ --}}
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
            <div class="tt-confirm__text">
                You will now be redirected to the selected social media channel.
            </div>
            <div class="tt-confirm__actions">
                <button type="button" class="tt-confirm__btn" data-tt-confirm-cancel>Cancel</button>
                <button type="button" class="tt-confirm__btn tt-confirm__btn--primary" data-tt-confirm-leave>Leave page</button>
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
                        <div class="tt-consent__text">I agree to the transmission of my personal data to {{ $s0==='instagram'?'Instagram':'LinkedIn' }} in order to be shown content provided by {{ $s0==='instagram'?'Instagram':'LinkedIn' }}. I have read the <a href="{{ $pr0 }}" target="_blank" rel="noopener">privacy policy</a>.</div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div></div>
                    <div class="tt-card__content">
                        <div class="tt-card__media">
                            @if($i0)<img src="{{ $i0 }}" alt="" class="tt-card__img">@endif
                            <div class="tt-card__badge tt-card__badge--ig">IG</div>
                        </div>
                        <div class="tt-card__body">
                            <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf0 }}</span><span class="tt-card__time">{{ $tm0 }}</span></div>
                            <div class="tt-card__scroll" data-tt-scroll>
                                <div class="tt-card__text">{{ $tx0 }}</div>
                                <a class="tt-card__link" href="{{ $ti0 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti0 }}">Show original post</a>
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
                        <div class="tt-consent__text">I agree to the transmission of my personal data to {{ $s1==='instagram'?'Instagram':'LinkedIn' }} in order to be shown content provided by {{ $s1==='instagram'?'Instagram':'LinkedIn' }}. I have read the <a href="{{ $pr1 }}" target="_blank" rel="noopener">privacy policy</a>.</div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div></div>
                    <div class="tt-card__content">
                        <div class="tt-card__media">
                            @if($i1)<img src="{{ $i1 }}" alt="" class="tt-card__img">@endif
                            <div class="tt-card__badge tt-card__badge--ig">IG</div>
                        </div>
                        <div class="tt-card__body">
                            <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf1 }}</span><span class="tt-card__time">{{ $tm1 }}</span></div>
                            <div class="tt-card__scroll" data-tt-scroll>
                                <div class="tt-card__text">{{ $tx1 }}</div>
                                <a class="tt-card__link" href="{{ $ti1 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti1 }}">Show original post</a>
                            </div>
                            <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down><span class="tt-card__downIcon">&#8964;</span></button>
                        </div>
                    </div>
                </article>
            </div>

            {{-- CARD 2 — CENTER (linkedin, tall) --}}
            @php
            $c2=$topics[2]; $i2=$img($c2);
            $tx2=$t($c2['text']??[]); $tt2=$t($c2['title']??[]); $ti2=$urlWithLocale($c2['original_url']??'#');
            $pr2=$urlWithLocale($c2['privacy_url']??'/{locale}/pages/privacy-policy');
            $tm2=(string)($c2['time_ago']??'—'); $pf2=(string)($c2['profile_name']??'Globaltrding');
            @endphp
            <div class="tt-slot tt-slot--center" data-slot="center">
                <article class="tt-card" data-social-card data-tt-card data-source="linkedin">
                    <div class="tt-card__consent"><div class="tt-consent__box">
                        <div class="tt-consent__text">I agree to the transmission of my personal data to LinkedIn in order to be shown content provided by LinkedIn. I have read the <a href="{{ $pr2 }}" target="_blank" rel="noopener">privacy policy</a>.</div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div></div>
                    <div class="tt-card__content">
                        {{-- Media: only when image provided; badge floats in corner otherwise --}}
                        @if($i2)
                            <div class="tt-card__media">
                                <img src="{{ $i2 }}" alt="" class="tt-card__img">
                                <div class="tt-card__badge tt-card__badge--li">in</div>
                            </div>
                        @else
                            <div class="tt-card__badge tt-card__badge--li" style="position:absolute;top:8px;right:8px;z-index:3">in</div>
                        @endif
                        {{-- body--lg: flex:1 fills remaining card height --}}
                        <div class="tt-card__body tt-card__body--lg">
                            <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf2 }}</span><span class="tt-card__time">{{ $tm2 }}</span></div>
                            @if($tt2)<div class="tt-card__title">{{ $tt2 }}</div>@endif
                            <div class="tt-card__scroll" data-tt-scroll>
                                <div class="tt-card__text tt-card__text--lg">{{ $tx2 }}</div>
                                <a class="tt-card__link" href="{{ $ti2 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti2 }}">Show original post</a>
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
                        <div class="tt-consent__text">I agree to the transmission of my personal data to LinkedIn in order to be shown content provided by LinkedIn. I have read the <a href="{{ $pr3 }}" target="_blank" rel="noopener">privacy policy</a>.</div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div></div>
                    <div class="tt-card__content">
                        <div class="tt-card__media">
                            @if($i3)<img src="{{ $i3 }}" alt="" class="tt-card__img">@endif
                            <div class="tt-card__badge tt-card__badge--li">in</div>
                        </div>
                        <div class="tt-card__body">
                            <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf3 }}</span><span class="tt-card__time">{{ $tm3 }}</span></div>
                            <div class="tt-card__scroll" data-tt-scroll>
                                <div class="tt-card__text">{{ $tx3 }}</div>
                                <a class="tt-card__link" href="{{ $ti3 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti3 }}">Show original post</a>
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
                    <div class="tt-card__consent"><div class="tt-consent__box">
                        <div class="tt-consent__text">I agree to the transmission of my personal data to LinkedIn in order to be shown content provided by LinkedIn. I have read the <a href="{{ $pr4 }}" target="_blank" rel="noopener">privacy policy</a>.</div>
                        <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                    </div></div>
                    <div class="tt-card__content">
                        <div class="tt-card__media">
                            @if($i4)<img src="{{ $i4 }}" alt="" class="tt-card__img">@endif
                            <div class="tt-card__badge tt-card__badge--li">in</div>
                        </div>
                        <div class="tt-card__body">
                            <div class="tt-card__meta"><span class="tt-card__profile">{{ $pf4 }}</span><span class="tt-card__time">{{ $tm4 }}</span></div>
                            <div class="tt-card__scroll" data-tt-scroll>
                                <div class="tt-card__text">{{ $tx4 }}</div>
                                <a class="tt-card__link" href="{{ $ti4 }}" target="_blank" rel="noopener" data-tt-original data-url="{{ $ti4 }}">Show original post</a>
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
            <div class="tt-nav__title">{{ $sectionTitle }}</div>
            <div class="tt-nav__labels">
                <span class="tt-nav__label is-active">#Trending</span>
            </div>
        </div>
    </div>

</section>

@endif