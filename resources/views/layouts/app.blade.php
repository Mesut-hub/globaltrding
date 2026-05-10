{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $locale = app()->getLocale();
        $navPayload = app(\App\Services\NavService::class)->payload();
        $siteSettings = class_exists(\App\Models\SiteSetting::class) ? \App\Models\SiteSetting::getCached() : [];
        $headerLogoPath = $siteSettings['header_logo_path'] ?? '/images/logo.png';
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

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    @php
        $isProd = app()->environment('production');
    @endphp

    <meta name="robots" content="{{ $isProd ? 'index,follow' : 'noindex,nofollow' }}">

    {{-- Canonical --}}
    <link rel="canonical" href="{{ $currentUrl }}">

    {{-- hreflang alternates (all locales) --}}
    @php
        $supportedLocales = config('locales.supported', ['en']);
        $defaultLocale = config('locales.default', 'en');

        // current path without query string, e.g. /en/products/rotok-valve
        $path = '/' . ltrim(request()->path(), '/');

        // Replace first path segment locale with "{loc}"
        // If path is just "/en" then remainder becomes empty.
        $parts = explode('/', trim($path, '/'));
        $currentLocale = $parts[0] ?? $defaultLocale;
        $rest = implode('/', array_slice($parts, 1));
    @endphp

    @foreach ($supportedLocales as $loc)
        @php
            $altPath = $rest !== '' ? "/{$loc}/{$rest}" : "/{$loc}";
            $altUrl = $appUrl . $altPath;
        @endphp
        <link rel="alternate" hreflang="{{ $loc }}" href="{{ $altUrl }}">
    @endforeach

    <link rel="alternate" hreflang="x-default" href="{{ $appUrl }}/{{ $defaultLocale }}{{ $rest !== '' ? '/' . $rest : '' }}">

    {{-- OpenGraph --}}
    <meta property="og:site_name" content="Globaltrding">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ $currentUrl }}">

    @if ($ogImage !== '')
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:width" content="@yield('og_image_width', '1200')">
        <meta property="og:image:height" content="@yield('og_image_height', '630')">
        <meta property="og:image:alt" content="@yield('og_image_alt', $ogTitle)">
    @endif
    
    {{-- OpenGraph locale / alternates --}}
    @php
        $supportedLocales = config('locales.supported', ['en']);
        $defaultLocale = config('locales.default', 'en');

        // Minimal mapping. Adjust if you target specific regions.
        $ogLocaleMap = [
            'en' => 'en_US',
            'tr' => 'tr_TR',
            'ar' => 'ar_AR',
            'fr' => 'fr_FR',
        ];

        $currentLocale = app()->getLocale();
        $ogLocale = $ogLocaleMap[$currentLocale] ?? ($ogLocaleMap[$defaultLocale] ?? 'en_US');
    @endphp

    <meta property="og:locale" content="{{ $ogLocale }}">
    @foreach ($supportedLocales as $loc)
        @continue($loc === $currentLocale)
        @php $alt = $ogLocaleMap[$loc] ?? null; @endphp
        @if ($alt)
            <meta property="og:locale:alternate" content="{{ $alt }}">
        @endif
    @endforeach

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    @if ($ogImage !== '')
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
    @if ($ogImage !== '')
        <meta name="twitter:image" content="{{ $ogImage }}">
        <meta name="twitter:image:alt" content="@yield('og_image_alt', $ogTitle)">
    @endif
    <meta name="twitter:site" content="@Globaltrding">
    <meta name="twitter:creator" content="@Globaltrding">

    <link rel="icon" href="{{ rtrim(config('app.url', 'https://globaltrding.com'), '/') }}/images/favicon.ico">
    <link rel="apple-touch-icon" href="{{ rtrim(config('app.url', 'https://globaltrding.com'), '/') }}/images/logo.png">

    {{-- JSON-LD: Organization (site-wide) --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Globaltrding',
        'url' => rtrim(config('app.url', 'https://globaltrding.com'), '/'),
        'logo' => rtrim(config('app.url', 'https://globaltrding.com'), '/') . '/images/logo.png',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    @stack('structured_data')

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
                    <button type="button" aria-label="Accessibility" title="Accessibility"
                            onclick="document.documentElement.classList.toggle('text-lg')">†</button>
                </div>

                <a href="/{{ $locale }}/" class="header-logo-box" aria-label="Globaltrding home">
                    <img
                        src="{{ str_starts_with($headerLogoPath, 'http://') || str_starts_with($headerLogoPath, 'https://') ? $headerLogoPath : asset(ltrim($headerLogoPath, '/')) }}"
                        alt="Globaltrding logo"
                        class="header-logo-image"
                    />
                    <div class="logo-subtitle">We create value in industry</div>
                </a>
            </div>
        </div>
    </header>
    
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
                        placeholder="Search..."
                        class="site-search-input"
                        autocomplete="off" />

                <div class="search-overlay__actions">
                    <a href="/{{ app()->getLocale() }}/products" class="search-overlay__icon" aria-label="Go to products">⌕</a>
                    <button id="searchClose" class="search-overlay__icon" aria-label="Close">✕</button>
                </div>
            </div>
        </div>
        <div class="search-results-wrap">
            <div id="searchHint" class="search-hint">Type at least 3 characters…</div>
            <div id="searchResults" class="search-results"></div>
        </div>

        <div class="search-overlay__content">
            <div class="search-overlay__panel">
                <div class="search-overlay__h">Product Finder</div>
                <div class="search-overlay__p">Looking for products?</div>
                <div class="search-overlay__p">
                    Use our Product Finder to browse and discover the right product for your needs.
                </div>
                <a href="/{{ app()->getLocale() }}/products" class="search-overlay__btn">Find products</a>
            </div>
        </div>
    </div>
    <div id="navOverlay" class="nav-overlay hidden" aria-hidden="true">
        <button type="button" id="navOverlayClose" class="nav-overlay__close" aria-label="Close menu">×</button>

        <div class="nav-overlay__content" role="dialog" aria-modal="true" aria-label="Navigation">
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
    <div id="cookieBanner" class="cookie-banner hidden" aria-live="polite">
        <div class="cookie-banner__inner">
            <div class="cookie-banner__text">
                We use cookies to improve your experience and to measure site usage (Google Analytics).
                Read our <a href="/{{ app()->getLocale() }}/pages/cookie-policy">Cookie Policy</a>.
            </div>
            <div class="cookie-banner__actions">
                <button type="button" id="cookieReject" class="cookie-btn cookie-btn--secondary">Reject all</button>
                <button type="button" id="cookieAcceptSocial" class="cookie-btn cookie-btn--secondary">Accept social only</button>
                <button type="button" id="cookieAccept" class="cookie-btn cookie-btn--primary">Accept all</button>
            </div>
        </div>
    </div>
</body>
</html>
