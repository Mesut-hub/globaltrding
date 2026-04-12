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
        $publicHeroVideo = config('home.hero_public_video'); // e.g. '/media/hero.mp4'
        $publicHeroVideoUrl = $publicHeroVideo ? url($publicHeroVideo) : null;  

        $minH = $data['min_h'] ?? '100vh';
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

    <section class="relative text-white hero-shell"
                data-hero>
        <div class="absolute inset-0 overflow-hidden bg-slate-950">
            @if ($mediaType === 'image')
                @if ($mediaUrl)
                    <img src="{{ $mediaUrl }}" class="h-full w-full object-cover" alt="">
                @elseif ($posterUrl)
                    <img src="{{ $posterUrl }}" class="h-full w-full object-cover" alt="">
                @endif
            @else
                @php
                    $videoSrc = $publicHeroVideoUrl ?: $mediaUrl;
                @endphp
                @if ($videoSrc)
                    <video class="h-full w-full object-cover opacity-100"
                        autoplay muted loop playsinline preload="metadata"
                        @if($posterUrl) poster="{{ $posterUrl }}" @endif>
                        <source src="{{ $videoSrc }}" type="video/mp4">
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

        <div class="relative mx-auto max-w-7xl px-4 hero-home__content">
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
        // Expect exactly 5 topics in this order:
        // 0 left_top (instagram)
        // 1 left_bottom (instagram)
        // 2 center (linkedin - big)
        // 3 right_top (linkedin)
        // 4 right_bottom (linkedin)
        //
        // Each topic item example:
        // [
        //   'source' => 'instagram'|'linkedin',
        //   'image_path' => 'pages/trending/xx.jpg' (optional),
        //   'title' => ['en' => '...'] (optional),
        //   'text' => ['en' => '...'],
        //   'profile_name' => 'BASF' (or your brand),
        //   'time_ago' => '7 days ago',
        //   'original_url' => 'https://...',
        //   'privacy_url' => '/{locale}/pages/privacy-policy'
        // ]
        $sectionTitle = $t($data['title'] ?? ['en' => 'Trending Topics']);
        $bgPath = $data['background_image_path'] ?? null;
        $bgUrl = $bgPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($bgPath) : null;

        $topics = is_array($data['topics'] ?? null) ? $data['topics'] : [];

        $getImg = function ($it) {
            $p = $it['image_path'] ?? null;
            return $p ? \Illuminate\Support\Facades\Storage::disk('public')->url($p) : null;
        };

        // Fill missing with empty items to avoid errors
        for ($i = count($topics); $i < 5; $i++) $topics[$i] = [];
    @endphp

    <section class="tt-stage" data-tt>
        {{-- Confirm overlay for "Show original post" (BASF-like) --}}
         <div class="tt-confirm hidden" data-tt-confirm aria-hidden="true">
             <div class="tt-confirm__dialog" role="dialog" aria-modal="true" aria-label="Leave page confirmation">
                 <div class="tt-confirm__text">
                     You are now leaving this website. Do you want to continue?
                 </div>
                 <div class="tt-confirm__actions">
                     <button type="button" class="tt-confirm__btn tt-confirm__btn--secondary" data-tt-confirm-cancel>
                         Cancel
                     </button>
                     <button type="button" class="tt-confirm__btn tt-confirm__btn--primary" data-tt-confirm-leave>
                         Leave page
                     </button>
                 </div>
             </div>
         </div>
        <div class="tt-stage__bg">
            @if ($bgUrl)
                <img src="{{ $bgUrl }}" alt="" class="tt-stage__bgImg">
            @else
                {{-- fallback --}}
                <div class="tt-stage__bgFallback"></div>
            @endif
            <div class="tt-stage__bgOverlay"></div>
        </div>

        <div class="tt-stage__inner">
            <div class="tt-rig">
                {{-- LEFT TOP (IG) --}}
                @php
                    $it = $topics[0] ?? [];
                    $src = $it['source'] ?? 'instagram';
                    $img = $getImg($it);
                    $text = $t($it['text'] ?? []);
                    $orig = $urlWithLocale($it['original_url'] ?? '#');
                    $privacy = $urlWithLocale($it['privacy_url'] ?? '/{locale}/pages/privacy-policy');
                    $timeAgo = (string) ($it['time_ago'] ?? '—');
                    $profile = (string) ($it['profile_name'] ?? 'Globaltrding');
                @endphp

                <article class="tt-card tt-card--sm tt-slot tt-slot--leftTop" data-social-card data-tt-card data-slot="leftTop" data-source="{{ $src }}">
                    <div class="tt-card__consent">
                        <div class="tt-consent__box">
                            <div class="tt-consent__text">
                                I agree to the transmission of my personal data to Instagram in order to be shown content provided by Instagram.
                                I have read the <a href="{{ $privacy }}" target="_blank" rel="noopener">privacy policy</a>.
                            </div>
                            <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                        </div>
                    </div>

                    <div class="tt-card__content">
                        <div class="tt-card__media">
                            @if ($img)
                                <img src="{{ $img }}" alt="" class="tt-card__img">
                            @endif
                            <div class="tt-card__badge tt-card__badge--ig">IG</div>
                        </div>

                        <div class="tt-card__body">
                            <div class="tt-card__meta">
                                <span class="tt-card__profile">{{ $profile }}</span>
                                <span class="tt-card__time">{{ $timeAgo }}</span>
                            </div>
                            <div class="tt-card__scroll" data-tt-scroll>
                                 <div class="tt-card__text">{{ $text }}</div>
                                 <a class="tt-card__link" href="{{ $orig }}" target="_blank" rel="noopener"
                                    data-tt-original data-url="{{ $orig }}">Show original post</a>
                            </div>
                            <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
                                <span class="tt-card__downIcon">⌄</span>
                            </button>
                        </div>
                    </div>
                </article>

                {{-- LEFT BOTTOM (IG) --}}
                @php
                    $it = $topics[1] ?? [];
                    $src = $it['source'] ?? 'instagram';
                    $img = $getImg($it);
                    $text = $t($it['text'] ?? []);
                    $orig = $urlWithLocale($it['original_url'] ?? '#');
                    $privacy = $urlWithLocale($it['privacy_url'] ?? '/{locale}/pages/privacy-policy');
                    $timeAgo = (string) ($it['time_ago'] ?? '—');
                    $profile = (string) ($it['profile_name'] ?? 'Globaltrding');
                @endphp

                <article class="tt-card tt-card--sm tt-slot tt-slot--leftBottom" data-social-card data-tt-card data-slot="leftBottom" data-source="{{ $src }}">
                    <div class="tt-card__consent">
                        <div class="tt-consent__box">
                            <div class="tt-consent__text">
                                I agree to the transmission of my personal data to Instagram in order to be shown content provided by Instagram.
                                I have read the <a href="{{ $privacy }}" target="_blank" rel="noopener">privacy policy</a>.
                            </div>
                            <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                        </div>
                    </div>

                    <div class="tt-card__content">
                        <div class="tt-card__media">
                            @if ($img)
                                <img src="{{ $img }}" alt="" class="tt-card__img">
                            @endif
                            <div class="tt-card__badge tt-card__badge--ig">IG</div>
                        </div>

                        <div class="tt-card__body">
                            <div class="tt-card__meta">
                                <span class="tt-card__profile">{{ $profile }}</span>
                                <span class="tt-card__time">{{ $timeAgo }}</span>
                            </div>
                            <div class="tt-card__scroll" data-tt-scroll>
                                 <div class="tt-card__text">{{ $text }}</div>
                                 <a class="tt-card__link" href="{{ $orig }}" target="_blank" rel="noopener"
                                    data-tt-original data-url="{{ $orig }}">Show original post</a>
                            </div>
                            <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
                                <span class="tt-card__downIcon">⌄</span>
                            </button>
                        </div>
                    </div>
                </article>

                {{-- CENTER BIG (LI) --}}
                @php
                    $it = $topics[2] ?? [];
                    $img = $getImg($it);
                    $text = $t($it['text'] ?? []);
                    $orig = $urlWithLocale($it['original_url'] ?? '#');
                    $privacy = $urlWithLocale($it['privacy_url'] ?? '/{locale}/pages/privacy-policy');
                    $timeAgo = (string) ($it['time_ago'] ?? '—');
                    $profile = (string) ($it['profile_name'] ?? 'Globaltrding');
                    $title = $t($it['title'] ?? []);
                @endphp

                <article class="tt-card tt-card--lg tt-slot tt-slot--center" data-social-card data-tt-card data-slot="center" data-source="linkedin">
                    <div class="tt-card__consent">
                        <div class="tt-consent__box">
                            <div class="tt-consent__text">
                                I agree to the transmission of my personal data to LinkedIn in order to be shown content provided by LinkedIn.
                                I have read the <a href="{{ $privacy }}" target="_blank" rel="noopener">privacy policy</a>.
                            </div>
                            <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                        </div>
                    </div>

                    <div class="tt-card__content">
                        <div class="tt-card__media">
                            <div class="tt-card__badge tt-card__badge--li">in</div>
                        </div>

                        <div class="tt-card__body tt-card__body--lg">
                            <div class="tt-card__meta">
                                <span class="tt-card__profile">{{ $profile }}</span>
                                <span class="tt-card__time">{{ $timeAgo }}</span>
                            </div>

                            @if ($title)
                                <div class="tt-card__title">{{ $title }}</div>
                            @endif

                            <div class="tt-card__scroll tt-card__scroll--lg" data-tt-scroll>
                                <div class="tt-card__text tt-card__text--lg">{{ $text }}</div>
                                <a class="tt-card__link" href="{{ $orig }}" target="_blank" rel="noopener"
                                   data-tt-original data-url="{{ $orig }}">Show original post</a>
                            </div>

                            <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
                                <span class="tt-card__downIcon">⌄</span>
                            </button>
                        </div>
                    </div>
                </article>

                {{-- RIGHT TOP (LI) --}}
                @php
                    $it = $topics[3] ?? [];
                    $img = $getImg($it);
                    $text = $t($it['text'] ?? []);
                    $orig = $urlWithLocale($it['original_url'] ?? '#');
                    $privacy = $urlWithLocale($it['privacy_url'] ?? '/{locale}/pages/privacy-policy');
                    $timeAgo = (string) ($it['time_ago'] ?? '—');
                    $profile = (string) ($it['profile_name'] ?? 'Globaltrding');
                @endphp

                <article class="tt-card tt-card--sm tt-card--liTall tt-slot tt-slot--rightTop" data-social-card data-tt-card data-slot="rightTop" data-source="linkedin">
                    <div class="tt-card__consent">
                        <div class="tt-consent__box">
                            <div class="tt-consent__text">
                                I agree to the transmission of my personal data to LinkedIn in order to be shown content provided by LinkedIn.
                                I have read the <a href="{{ $privacy }}" target="_blank" rel="noopener">privacy policy</a>.
                            </div>
                            <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                        </div>
                    </div>

                    <div class="tt-card__content">
                        <div class="tt-card__media">
                            @if ($img)
                                <img src="{{ $img }}" alt="" class="tt-card__img">
                            @endif
                            <div class="tt-card__badge tt-card__badge--li">in</div>
                        </div>

                        <div class="tt-card__body">
                            <div class="tt-card__meta">
                                <span class="tt-card__profile">{{ $profile }}</span>
                                <span class="tt-card__time">{{ $timeAgo }}</span>
                            </div>
                            <div class="tt-card__scroll" data-tt-scroll>
                                <div class="tt-card__text">{{ $text }}</div>
                                <a class="tt-card__link" href="{{ $orig }}" target="_blank" rel="noopener"
                                   data-tt-original data-url="{{ $orig }}">Show original post</a>
                            </div>
                            <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
                                <span class="tt-card__downIcon">⌄</span>
                            </button>
                        </div>
                    </div>
                </article>

                {{-- RIGHT BOTTOM (LI) --}}
                @php
                    $it = $topics[4] ?? [];
                    $img = $getImg($it);
                    $text = $t($it['text'] ?? []);
                    $orig = $urlWithLocale($it['original_url'] ?? '#');
                    $privacy = $urlWithLocale($it['privacy_url'] ?? '/{locale}/pages/privacy-policy');
                    $timeAgo = (string) ($it['time_ago'] ?? '—');
                    $profile = (string) ($it['profile_name'] ?? 'Globaltrding');
                @endphp

                <article class="tt-card tt-card--sm tt-card--liTall tt-slot tt-slot--rightBottom" data-social-card data-tt-card data-slot="rightBottom" data-source="linkedin">
                    <div class="tt-card__consent">
                        <div class="tt-consent__box">
                            <div class="tt-consent__text">
                                I agree to the transmission of my personal data to LinkedIn in order to be shown content provided by LinkedIn.
                                I have read the <a href="{{ $privacy }}" target="_blank" rel="noopener">privacy policy</a>.
                            </div>
                            <a href="#" class="tt-consent__btn" data-social-accept>Accept</a>
                        </div>
                    </div>

                    <div class="tt-card__content">
                        <div class="tt-card__media">
                            @if ($img)
                                <img src="{{ $img }}" alt="" class="tt-card__img">
                            @endif
                            <div class="tt-card__badge tt-card__badge--li">in</div>
                        </div>

                        <div class="tt-card__body">
                            <div class="tt-card__meta">
                                <span class="tt-card__profile">{{ $profile }}</span>
                                <span class="tt-card__time">{{ $timeAgo }}</span>
                            </div>
                            <div class="tt-card__scroll" data-tt-scroll>
                                <div class="tt-card__text">{{ $text }}</div>
                                <a class="tt-card__link" href="{{ $orig }}" target="_blank" rel="noopener"
                                   data-tt-original data-url="{{ $orig }}">Show original post</a>
                            </div>
                            <button type="button" class="tt-card__down" aria-label="Scroll down" data-tt-down>
                                <span class="tt-card__downIcon">⌄</span>
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        {{-- Bottom nav bar like BASF --}}
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