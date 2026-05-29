{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    data-ga-id="{{ config('services.google_analytics.id', '') }}"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if(app()->getLocale() === 'ar')
        <meta name="text-direction" content="rtl">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $locale = app()->getLocale();
        $navPayload = app(\App\Services\NavService::class)->payload();
        $siteSettings = class_exists(\App\Models\SiteSetting::class) ? \App\Models\SiteSetting::getCached() : [];
        $headerLogoPath = $siteSettings['header_logo_path'] ?? '/images/logo.png';
        $isProd = app()->environment('production');
    @endphp

    <script type="application/json" id="gt-nav-data">
    {!! json_encode($navPayload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
    </script>
    @php
        // Base URL for canonical/OG. In production, set APP_URL=https://globaltrding.com
        $appUrl = rtrim((string) config('app.url'), '/');

        // Safety net: if APP_URL is missing or still localhost in production, fallback to request host.
        if ($appUrl === '' || str_contains($appUrl, '127.0.0.1') || str_contains($appUrl, 'localhost')) {
            $appUrl = rtrim(request()->getSchemeAndHttpHost(), '/');
        }

        $currentUrl = $appUrl . request()->getRequestUri();

        $metaTitle = trim((string) View::yieldContent('meta_title', 'Globaltrding'));
        $metaDescription = trim((string) View::yieldContent('meta_description', 'Industrial equipment & raw materials supplier.'));

        // Optional overrides (set in child views if desired)
        $ogTitle = trim((string) View::yieldContent('og_title', $metaTitle));
        $ogDescription = trim((string) View::yieldContent('og_description', $metaDescription));
        $ogImage = trim((string) View::yieldContent('og_image', $appUrl . '/images/og-default.png'));
        $ogType = trim((string) View::yieldContent('og_type', 'website'));

        $twitterCard = trim((string) View::yieldContent('twitter_card', $ogImage !== '' ? 'summary_large_image' : 'summary'));
    @endphp

    @if (trim((string) View::yieldContent('article_published_time')) !== '')
        <meta property="article:published_time" content="@yield('article_published_time')">
    @endif
    @if (trim((string) View::yieldContent('article_modified_time')) !== '')
        <meta property="article:modified_time" content="@yield('article_modified_time')">
    @endif

    @php
        $seoService = app(\App\Services\SeoService::class);
        // $seoMeta is injected by controllers; fallback to empty
        $seoMeta = $seoMeta ?? [];
        $seoTitle       = $seoMeta['title']       ?? trim((string) View::yieldContent('meta_title', config('app.name')));
        $seoDescription = $seoMeta['description'] ?? trim((string) View::yieldContent('meta_description', ''));
        $seoOgImage     = $seoMeta['ogImage']     ?? trim((string) View::yieldContent('og_image', $appUrl . '/images/og-default.png'));
        $seoOgType      = $seoMeta['ogType']      ?? trim((string) View::yieldContent('og_type', 'website'));
        $seoRobots      = $seoMeta['robots']      ?? '';
        $seoCanonical   = $seoMeta['canonical']   ?? '';
        $publishedTime  = trim((string) View::yieldContent('article_published_time', ''));
        $modifiedTime   = trim((string) View::yieldContent('article_modified_time', ''));
    @endphp

    <x-seo-head
        :title="$seoTitle"
        :description="$seoDescription"
        :og-image="$seoOgImage"
        :og-type="$seoOgType"
        :robots="$seoRobots ?: ($isProd ? 'index,follow' : 'noindex,nofollow')"
        :canonical="$seoCanonical"
        :published-time="$publishedTime ?: null"
        :modified-time="$modifiedTime ?: null"
        :locale="$locale"
    />
    <meta name="twitter:site" content="@Globaltrding">
    <meta name="twitter:creator" content="@Globaltrding">

    <link rel="icon" href="{{ rtrim(config('app.url', 'https://globaltrding.com'), '/') }}/images/favicon.ico">
    <link rel="apple-touch-icon" href="{{ rtrim(config('app.url', 'https://globaltrding.com'), '/') }}/images/logo.png">

    @if(app()->getLocale() === 'ar')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @else
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    @endif

    {{-- JSON-LD: Organization + WebSite (site-wide) --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph'   => [
                [
                    '@type'            => 'Organization',
                    '@id'              => rtrim(config('app.url'), '/') . '/#organization',
                    'name'             => 'Globaltrding',
                    'url'              => rtrim(config('app.url'), '/'),
                    'logo'             => [
                        '@type'           => 'ImageObject',
                        'url'             => rtrim(config('app.url'), '/') . '/images/logo.png',
                        'width'           => 200,
                        'height'          => 60,
                    ],
                    'sameAs'           => array_filter([
                        $siteSettings['linkedin_url']  ?? null,
                        $siteSettings['instagram_url'] ?? null,
                        $siteSettings['x_url']         ?? null,
                        $siteSettings['youtube_url']   ?? null,
                    ]),
                    'contactPoint'     => [
                        '@type'           => 'ContactPoint',
                        'contactType'     => 'customer service',
                        'email'           => config('departments.admin.inbox', 'info@globaltrding.com'),
                        'availableLanguage' => ['English', 'Turkish', 'Arabic', 'French'],
                    ],
                ],
                [
                    '@type'            => 'WebSite',
                    '@id'              => rtrim(config('app.url'), '/') . '/#website',
                    'url'              => rtrim(config('app.url'), '/'),
                    'name'             => 'Globaltrding',
                    'publisher'        => ['@id' => rtrim(config('app.url'), '/') . '/#organization'],
                    'inLanguage'       => config('locales.supported', ['en']),
                    'potentialAction'  => [
                        '@type'         => 'SearchAction',
                        'target'        => [
                            '@type'     => 'EntryPoint',
                            'urlTemplate' => rtrim(config('app.url'), '/') . '/' . $locale . '/search?q={search_term_string}',
                        ],
                        'query-input'   => 'required name=search_term_string',
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    @stack('structured_data')

    <script>window.__cookieAlwaysActive = {{ Js::from(__('cookie.always_active')) }};</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased @stack('body_class')">
    {{-- Top bar / header placeholder --}}
    @php $locale = app()->getLocale(); @endphp
    <header id="siteHeader" class="site-header">
        <div class="header-inner">
            @php
                $locale = app()->getLocale();
                $fallback = config('locales.default', 'en');
                $navPayload = app(\App\Services\NavService::class)->payload();

                $t = function ($arr) use ($locale, $fallback) {
                    if (!is_array($arr)) return (string)($arr ?? '');
                    return (string)($arr[$locale] ?? $arr[$fallback] ?? (count($arr) ? reset($arr) : ''));
                };
            @endphp
            {{-- Mobile: hamburger toggle (hidden on desktop via CSS) --}}
            <button
                type="button"
                id="mobileMenuToggle"
                class="mobile-menu-toggle"
                aria-label="{{ __('ui.global') }}"
                aria-controls="mobileNav"
                aria-expanded="false"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            {{-- Left: nav bar --}}
            <nav class="main-nav" aria-label="Main navigation">
                <ul>
                    <li><a href="/{{ $locale }}/" class="is-global">{{ __('Global') }}</a></li>

                    @foreach($navPayload as $group)
                        @php
                        $key = $group['key'] ?? null;
                        $label = $t($group['label'] ?? []);
                        if (!$key || $label === '') continue;
                        @endphp
                        <li>
                        {{-- Keep the SAME overlay trigger mechanism --}}
                        <a href="#" data-overlay-key="{{ $key }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Right: icons + logo box --}}
            <div class="header-right">
                <div class="header-icons" aria-label="Header actions">
                    <button id="searchOpen" type="button" aria-label="Search">⌕</button>
                    @php
                        $supported = config('locales.supported', ['en']);
                        $default = config('locales.default', 'en');

                        // Example current path: /en/products/rotok-valve
                        $path = '/' . ltrim(request()->path(), '/');
                        $parts = explode('/', trim($path, '/'));

                        // If first segment is a supported locale, drop it; else keep full path as rest
                        $first = $parts[0] ?? $default;
                        $restParts = in_array($first, $supported, true) ? array_slice($parts, 1) : $parts;
                        $rest = implode('/', $restParts); // e.g. products/rotok-valve (or empty)
                    @endphp
                    
                    {{-- ── Product user: name + logout ────────────────────────────────── --}}
                    @auth('product')
                    @php $productUser = auth()->guard('product')->user(); @endphp
                    <div class="product-user-bar" style="display:inline-flex;align-items:center;gap:10px;">
                        <span style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.85);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                            title="{{ $productUser->email }}">
                            {{ $productUser->name }}
                        </span>
                        <form method="POST" action="/{{ app()->getLocale() }}/logout" style="display:inline;">
                            @csrf
                            <button
                                type="submit"
                                style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);color:#fff;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;transition:background .2s ease;"
                                onmouseover="this.style.background='rgba(255,255,255,0.22)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.12)'"
                                aria-label="Logout"
                                title="Logout from product area"
                            >
                                Logout
                            </button>
                        </form>
                    </div>
                    @endauth

                    <div class="lang-dropdown" data-lang-dropdown>
                        <button type="button" class="lang-btn" data-lang-toggle aria-label="Language" title="Language">🌐</button>

                        <div class="lang-menu-vertical" data-lang-menu aria-label="Language menu">
                            @foreach ($supported as $loc)
                                <a
                                    href="{{ $rest !== '' ? "/{$loc}/{$rest}" : "/{$loc}" }}"
                                    class="{{ $locale === $loc ? 'active' : '' }}"
                                >
                                    {{ strtoupper($loc) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <button
                        type="button"
                        id="gtPromoTrigger"
                        class="gt-promo-trigger"
                        aria-label="{{ __('ui.promotions') }}"
                        title="{{ __('ui.promotions') }}"
                        aria-haspopup="dialog"
                        aria-expanded="false"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.9"
                            stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">
                            <path d="M11 5.882V19.24a1.76 1.76 0 0 1-3.417.592l-2.147-6.15M18 13a3 3 0 1 0 0-6M5.436 13.683A4.001 4.001 0 0 0 17.032 8.5l-10.064-.634.468 5.817Z"/>
                        </svg>
                        <span class="gt-promo-trigger__badge" hidden aria-hidden="true"></span>
                    </button>
                </div>

                <a href="/{{ $locale }}/" class="header-logo-box" aria-label="Logo link">
                    <div class="logo-title">GLOBAL TRADING</div>
                    <div class="logo-subtitle">We create value in industry</div>
                </a>
            </div>
        </div>
    </header>

    {{-- Mobile navigation drawer --}}
    @php
        $mLocale = app()->getLocale();
        $mFallback = config('locales.default', 'en');
        $mSupported = config('locales.supported', ['en']);
        $mNavPayload = app(\App\Services\NavService::class)->payload();

        $mT = function ($arr) use ($mLocale, $mFallback) {
            if (!is_array($arr)) return (string)($arr ?? '');
            return (string)($arr[$mLocale] ?? $arr[$mFallback] ?? (count($arr) ? reset($arr) : ''));
        };

        $mPath = '/' . ltrim(request()->path(), '/');
        $mParts = explode('/', trim($mPath, '/'));
        $mFirst = $mParts[0] ?? $mFallback;
        $mRestParts = in_array($mFirst, $mSupported, true) ? array_slice($mParts, 1) : $mParts;
        $mRest = implode('/', $mRestParts);
    @endphp
    <div id="mobileNavBackdrop" class="mobile-nav-backdrop" hidden></div>
    <aside id="mobileNav" class="mobile-nav" aria-hidden="true" aria-label="{{ __('ui.global') }}">
        <div class="mobile-nav__header">
            <a href="/{{ $mLocale }}/" class="mobile-nav__brand">GLOBAL TRADING</a>
            <button type="button" id="mobileNavClose" class="mobile-nav__close" aria-label="{{ __('ui.close') }}">&times;</button>
        </div>

        <nav class="mobile-nav__list" aria-label="Mobile navigation">
            <a href="/{{ $mLocale }}/" class="mobile-nav__link">{{ __('Global') }}</a>

            @foreach($mNavPayload as $group)
                @php
                    $mKey = $group['key'] ?? null;
                    $mLabel = $mT($group['label'] ?? []);
                    if (!$mKey || $mLabel === '') continue;
                @endphp
                <a href="#" class="mobile-nav__link" data-overlay-key="{{ $mKey }}">
                    <span>{{ $mLabel }}</span>
                    <span class="mobile-nav__chev" aria-hidden="true">&rsaquo;</span>
                </a>
            @endforeach
        </nav>

        <div class="mobile-nav__section">{{ __('ui.search') ?? 'Search' }}</div>
        <nav class="mobile-nav__list" aria-label="Mobile shortcuts">
            <a href="/{{ $mLocale }}/products" class="mobile-nav__link">{{ __('products.finder_title') }}</a>
        </nav>

        <div class="mobile-nav__section" aria-hidden="true">Language</div>
        <div class="mobile-nav__langs">
            @foreach ($mSupported as $loc)
                <a
                    href="{{ $mRest !== '' ? "/{$loc}/{$mRest}" : "/{$loc}" }}"
                    class="mobile-nav__lang {{ $mLocale === $loc ? 'active' : '' }}"
                >{{ strtoupper($loc) }}</a>
            @endforeach
        </div>
    </aside>

    @php
        $hasHero = $hasHero ?? false;
    @endphp

    <main class="site-main">
        @yield('content')
    </main>

    @include('shared.footer')

    {{-- Search Overlay --}}
    <div id="searchOverlay" class="search-overlay hidden" aria-hidden="true">
        <div class="search-overlay__bar">
            <div class="search-overlay__inner">
                <input id="siteSearchInput"
                        type="search"
                        placeholder="{{ __('ui.search_placeholder') }}"
                        class="site-search-input"
                        autocomplete="off" />

                <div class="search-overlay__actions">
                    <a href="/{{ app()->getLocale() }}/products" class="search-overlay__icon" aria-label="Go to products">⌕</a>
                    <button id="searchClose" class="search-overlay__icon" aria-label="Close">✕</button>
                </div>
            </div>
        </div>
        <div class="search-results-wrap">
            <div id="searchHint" class="search-hint"
                data-hint-default="{{ __('ui.search_type_hint') }}"
                data-hint-searching="{{ __('ui.search_searching') }}"
                data-hint-no-results="{{ __('ui.search_no_results') }}">
                {{ __('ui.search_type_hint') }}
            </div>
            <div id="searchResults" class="search-results"></div>
        </div>

        <div class="search-overlay__content">
            <div class="search-overlay__panel">
                <div class="search-overlay__h">{{ __('ui.search_product_finder') }}</div>
                <div class="search-overlay__p">{{ __('ui.search_products_cta') }}</div>
                <div class="search-overlay__p">{{ __('ui.search_products_body') }}</div>
                <a href="/{{ app()->getLocale() }}/products" class="search-overlay__btn">{{ __('ui.search_find_products') }}</a>
            </div>
        </div>
    </div>
    <div id="navOverlay" class="nav-overlay hidden" aria-hidden="true">
        <button type="button" id="navOverlayClose" class="nav-overlay__close" aria-label="{{ __('ui.close') }}">×</button>

        <div class="nav-overlay__content" 
            role="dialog" 
            aria-modal="true" 
            aria-label="{{ __('ui.global') }}"
            data-i18n-menu="{{ __('ui.global') }}"
            data-i18n-what="{{ __('nav.search_placeholder') }}"
            data-i18n-cancel="{{ __('ui.cancel') }}"
            data-i18n-leave="{{ __('nav.leave_page') }}">
            <aside class="nav-overlay__left">
                <div class="nav-overlay__sectionTitle" id="navOverlayTitle">Menu</div>
                <div class="nav-overlay__list" id="navOverlayList"></div>
            </aside>

            <div class="nav-overlay__mid" aria-hidden="true">
                <div class="nav-overlay__thumbTrack" id="navOverlayThumbTrack">
                    <div class="nav-overlay__thumb" id="navOverlayThumb"></div>
                </div>
            </div>

            <section class="nav-overlay__center">
                <div class="nav-overlay__desc" id="navOverlayDesc"></div>
            </section>

            <section class="nav-overlay__right">
                <div class="nav-overlay__preview" id="navOverlayPreview"></div>
            </section>
        </div>
    </div>
    @include('components.cookie-consent')
    @include('components.promotion-overlay')
</body>
</html>
