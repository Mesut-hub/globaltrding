{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    data-ga-id="{{ config('services.google_analytics.id', '') }}"
>
<head>
    <meta charset="utf-8">
    {{-- Responsive viewport: prevents iOS auto-zoom, enables proper scaling --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">
    @if(app()->getLocale() === 'ar')
        <meta name="text-direction" content="rtl">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

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
    <link rel="apple-touch-icon" sizes="180x180" href="{{ rtrim(config('app.url', 'https://globaltrding.com'), '/') }}/images/logo.png">
    <link rel="manifest" href="/site.webmanifest" crossorigin="use-credentials">

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

    {{-- Inline critical CSS for above-fold performance --}}
    <style>
        /* Critical: prevent flash of unstyled header */
        .site-header { position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; }
        @media (min-width: 1101px) { .site-header { top: 42px; } }
        /* Prevent layout shift while fonts load */
        body { font-display: swap; }
        /* Skip link */
        .skip-to-main {
            position: absolute; top: -100%; left: 16px; z-index: 9999;
            background: #0f172a; color: #fff; padding: 10px 20px;
            border-radius: 0 0 8px 8px; font-weight: 700; font-size: 14px;
            text-decoration: none; transition: top 0.2s;
        }
        .skip-to-main:focus { top: 0; }
    </style>
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased @stack('body_class')">

    {{-- Skip to main content (accessibility) --}}
    <a class="skip-to-main" href="#main-content">Skip to main content</a>

    {{-- Top bar / header placeholder --}}
    @php $locale = app()->getLocale(); @endphp
    <header id="siteHeader" class="site-header" role="banner">
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
            
            {{-- Left: nav bar --}}
            <nav class="main-nav" aria-label="{{ __('ui.global') }} main navigation" role="navigation">
                <ul role="list">
                    <li>
                        <a href="/{{ $locale }}/" class="is-global" aria-label="{{ __('ui.home') }}">
                            {{ __('ui.global') }}
                        </a>
                    </li>

                    @foreach($navPayload as $group)
                        @php
                        $key = $group['key'] ?? null;
                        $label = $t($group['label'] ?? []);
                        if (!$key || $label === '') continue;
                        @endphp
                        <li>
                            {{-- Keep the SAME overlay trigger mechanism --}}
                            <a href="#"
                                data-overlay-key="{{ $key }}"
                                aria-haspopup="dialog"
                                aria-expanded="false">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Right: icons + logo box --}}
            <div class="header-right">
                <div class="header-icons" role="toolbar" aria-label="Header actions">
                    <button id="searchOpen"
                            type="button"
                            aria-label="{{ __('ui.search') }}"
                            aria-expanded="false"
                            aria-controls="searchOverlay">⌕</button>
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
                    <div class="product-user-bar" role="status" aria-label="Logged in as {{ $productUser->name }}" style="display:inline-flex;align-items:center;gap:10px;">
                        <span style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.85);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                            title="{{ $productUser->email }}"
                            aria-hidden="true">
                            {{ $productUser->name }}
                        </span>
                        <form method="POST" action="/{{ app()->getLocale() }}/logout" style="display:inline;">
                            @csrf
                            <button
                                type="submit"
                                style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);color:#fff;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;transition:background .2s ease;"
                                onmouseover="this.style.background='rgba(255,255,255,0.22)'"
                                onmouseout="this.style.background='rgba(255,255,255,0.12)'"
                                aria-label="{{ __('auth.logged_out') }}"
                                title="Logout from product area"
                            >
                                Logout
                            </button>
                        </form>
                    </div>
                    @endauth

                    <div class="lang-dropdown" data-lang-dropdown role="navigation" aria-label="{{ __('ui.language') }}">
                        <button type="button"
                                class="lang-btn"
                                data-lang-toggle
                                aria-label="{{ __('ui.language') }}"
                                aria-haspopup="listbox"
                                aria-expanded="false"
                                title="{{ __('ui.language') }}">🌐</button>

                        <div class="lang-menu-vertical"
                             data-lang-menu
                             role="listbox"
                             aria-label="{{ __('ui.language') }}">
                            @foreach ($supported as $loc)
                                <a
                                    href="{{ $rest !== '' ? "/{$loc}/{$rest}" : "/{$loc}" }}"
                                   role="option"
                                   aria-selected="{{ $locale === $loc ? 'true' : 'false' }}"
                                   class="{{ $locale === $loc ? 'active' : '' }}"
                                   hreflang="{{ $loc }}"
                                   lang="{{ $loc }}">
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
                            aria-hidden="true"
                            focusable="false">
                            <path d="M11 5.882V19.24a1.76 1.76 0 0 1-3.417.592l-2.147-6.15M18 13a3 3 0 1 0 0-6M5.436 13.683A4.001 4.001 0 0 0 17.032 8.5l-10.064-.634.468 5.817Z"/>
                        </svg>
                        <span class="gt-promo-trigger__badge" hidden aria-hidden="true"></span>
                    </button>
                </div>

                <a href="/{{ $locale }}/"
                   class="header-logo-box"
                   aria-label="Globaltrding — {{ __('ui.home') }}">
                    <div class="logo-title">GLOBAL TRADING</div>
                    <div class="logo-subtitle" aria-hidden="true">We create value in industry</div>
                </a>
            </div>
        </div>
    </header>
    
    @php
        $hasHero = $hasHero ?? false;
    @endphp

    <main class="site-main" id="main-content" role="main" tabindex="-1">
        @yield('content')
    </main>

    @include('shared.footer')

    {{-- Search Overlay --}}
    <div id="searchOverlay"
         class="search-overlay hidden"
         role="dialog"
         aria-modal="true"
         aria-label="{{ __('ui.search') }}"
         aria-hidden="true">
        <div class="search-overlay__bar">
            <div class="search-overlay__inner">
                <input id="siteSearchInput"
                       type="search"
                       placeholder="{{ __('ui.search_placeholder') }}"
                       class="site-search-input"
                       autocomplete="off"
                       aria-label="{{ __('ui.search_placeholder') }}"
                       aria-autocomplete="list"
                       aria-controls="searchResults"
                       role="combobox"
                       aria-expanded="false" />

                <div class="search-overlay__actions">
                    <a href="/{{ app()->getLocale() }}/products"
                       class="search-overlay__icon"
                       aria-label="{{ __('products.finder_title') }}">⌕</a>
                    <button id="searchClose"
                            class="search-overlay__icon"
                            type="button"
                            aria-label="{{ __('ui.close') }}">✕</button>
                </div>
            </div>
        </div>
        <div class="search-results-wrap" role="region" aria-live="polite" aria-atomic="true">
            <div id="searchHint" class="search-hint" role="status"
                data-hint-default="{{ __('ui.search_type_hint') }}"
                data-hint-searching="{{ __('ui.search_searching') }}"
                data-hint-no-results="{{ __('ui.search_no_results') }}">
                {{ __('ui.search_type_hint') }}
            </div>
            <div id="searchResults" class="search-results" role="listbox" aria-label="{{ __('ui.search') }} results"></div>
        </div>

        <div class="search-overlay__content" aria-hidden="true">
            <div class="search-overlay__panel">
                <div class="search-overlay__h">{{ __('ui.search_product_finder') }}</div>
                <div class="search-overlay__p">{{ __('ui.search_products_cta') }}</div>
                <div class="search-overlay__p">{{ __('ui.search_products_body') }}</div>
                <a href="/{{ app()->getLocale() }}/products" class="search-overlay__btn">
                    {{ __('ui.search_find_products') }}
                </a>
            </div>
        </div>
    </div>
    <div id="navOverlay" class="nav-overlay hidden" role="dialog" aria-modal="true" aria-label="{{ __('ui.global') }}" aria-hidden="true">
        <button type="button" id="navOverlayClose" class="nav-overlay__close" aria-label="{{ __('ui.close') }}">×</button>
        <div class="nav-overlay__content"
            data-i18n-menu="{{ __('ui.global') }}"
            data-i18n-what="{{ __('nav.search_placeholder') }}"
            data-i18n-cancel="{{ __('ui.cancel') }}"
            data-i18n-leave="{{ __('nav.leave_page') }}">

            <div class="nav-overlay__grouptabs-row">
                <nav class="nav-overlay__grouptabs" id="navOverlayGroupTabs" aria-label="{{ __('ui.global') }} navigation"></nav>
            </div>

            <aside class="nav-overlay__left" aria-label="Navigation menu">
                <div class="nav-overlay__sectionTitle" id="navOverlayTitle">Menu</div>
                <div class="nav-overlay__listWrap" id="navOverlayListWrap">
                    <div class="nav-overlay__list" id="navOverlayList" role="list" aria-label="Navigation items"></div>
                    <div class="nav-overlay__sublevel" id="navOverlaySublevel" aria-hidden="true">
                        <button type="button" class="nav-overlay__subBack" id="navOverlaySubBack">
                            <span aria-hidden="true">‹</span>
                            <span>{{ __('ui.go_back') }}</span>
                        </button>
                        <div class="nav-overlay__subTitle" id="navOverlaySubTitle"></div>
                        <div class="nav-overlay__subList" id="navOverlaySubList" role="list"></div>
                    </div>
                </div>
            </aside>

            <div class="nav-overlay__mid" aria-hidden="true">
                <div class="nav-overlay__thumbTrack" id="navOverlayThumbTrack">
                    <div class="nav-overlay__thumb" id="navOverlayThumb"></div>
                </div>
            </div>

            <section class="nav-overlay__center" aria-label="Description">
                <div class="nav-overlay__desc" id="navOverlayDesc"></div>
            </section>

            <section class="nav-overlay__right" aria-label="Preview">
                <div class="nav-overlay__preview" id="navOverlayPreview"></div>
            </section>
        </div>
    </div>
    @include('components.cookie-consent')
    @include('components.promotion-overlay')

    {{-- Scroll to top button --}}
    <button type="button"
            data-scroll-top
            aria-label="{{ __('ui.scroll_top') }}"
            title="{{ __('ui.scroll_top') }}"
            style="
                position:fixed;
                bottom:max(24px, env(safe-area-inset-bottom, 24px));
                left:20px;
                width:44px;
                height:44px;
                border-radius:50%;
                background:#0f172a;
                color:#fff;
                border:0;
                cursor:pointer;
                z-index:500;
                display:grid;
                place-items:center;
                font-size:20px;
                opacity:0;
                pointer-events:none;
                transition:opacity 0.25s ease;
                box-shadow:0 4px 12px rgba(0,0,0,0.25);
            ">&#8679;</button>
 
    @stack('scripts')
        <script>
            function gtDocDownload(el) {
                var url  = el.getAttribute('data-doc-dl');
                var name = el.getAttribute('data-doc-name') || 'document';
                var icon = el.querySelector('.gt-docdd__dlIcon');

                // Show loading state
                if (icon) icon.textContent = '…';
                el.style.opacity = '0.6';
                el.style.pointerEvents = 'none';

                fetch(url, { credentials: 'same-origin' })
                    .then(function(res) {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.blob();
                    })
                    .then(function(blob) {
                        var blobUrl = URL.createObjectURL(blob);
                        var a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = name;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        setTimeout(function() { URL.revokeObjectURL(blobUrl); }, 10000);
                    })
                    .catch(function() {
                        // Fallback: open directly (IDM may intercept but file still downloads)
                        window.open(url, '_blank');
                    })
                    .finally(function() {
                        if (icon) icon.textContent = '↓';
                        el.style.opacity = '';
                        el.style.pointerEvents = '';
                    });
            }
        </script>
    @stack('scripts')
</body>
</html>