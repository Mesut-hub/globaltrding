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
<body class="@stack('body_class') min-h-screen bg-white text-slate-900 antialiased">
    {{-- Top bar / header placeholder --}}
    @php $locale = app()->getLocale(); @endphp
    <header id="siteHeader" class="site-header">
        <div class="header-inner">
            {{-- Left: nav bar --}}
            <nav class="main-nav" aria-label="Main navigation">
                <ul>
                    <li><a href="/{{ $locale }}/" class="is-global">Global</a></li>
                    <li><a href="/{{ $locale }}/pages/who-we-are"
                           data-overlay-key="who-we-are"
                           data-overlay-title="Who We Are"
                           data-overlay-desc="Globaltrding is a multilingual supplier of industrial equipment and raw materials, serving Oil &amp; Gas, Petrochemical, Refinery, and Chemical industries worldwide."
                           data-overlay-items='[{"label":"About Globaltrding","href":"/{{ $locale }}/pages/who-we-are"},{"label":"Our Mission","href":"/{{ $locale }}/pages/who-we-are#mission"},{"label":"Our Values","href":"/{{ $locale }}/pages/who-we-are#values"}]'
                        >Who we are</a></li>
                    <li><a href="/{{ $locale }}/products"
                           data-overlay-key="products"
                           data-overlay-title="Products"
                           data-overlay-desc="Browse our full range of industrial equipment and raw materials for Oil &amp; Gas, Petrochemical, and Chemical industries."
                           data-overlay-items='[{"label":"Product Finder","href":"/{{ $locale }}/products","isProductFinder":true},{"label":"Industrial Equipment","href":"/{{ $locale }}/products?category=equipment"},{"label":"Raw Materials","href":"/{{ $locale }}/products?category=materials"},{"label":"Valves &amp; Fittings","href":"/{{ $locale }}/products?category=valves"}]'
                        >Products</a></li>
                    <li><a href="/{{ $locale }}/pages/investors"
                           data-overlay-key="investors"
                           data-overlay-title="Investors"
                           data-overlay-desc="Corporate governance, financial information and investor relations for Globaltrding."
                           data-overlay-items='[{"label":"Investor Relations","href":"/{{ $locale }}/pages/investors"},{"label":"Annual Reports","href":"/{{ $locale }}/pages/investors#reports"},{"label":"Corporate Governance","href":"/{{ $locale }}/pages/investors#governance"}]'
                        >Investors</a></li>
                    <li><a href="/{{ $locale }}/pages/careers"
                           data-overlay-key="careers"
                           data-overlay-title="Careers"
                           data-overlay-desc="Join our global team. Build your future with Globaltrding and contribute to the industrial supply chain."
                           data-overlay-items='[{"label":"Open Positions","href":"/{{ $locale }}/pages/careers"},{"label":"Our Culture","href":"/{{ $locale }}/pages/careers#culture"},{"label":"Benefits","href":"/{{ $locale }}/pages/careers#benefits"}]'
                        >Careers</a></li>
                    <li><a href="/{{ $locale }}/pages/media"
                           data-overlay-key="media"
                           data-overlay-title="Media"
                           data-overlay-desc="Latest news, press releases, and media resources from Globaltrding."
                           data-overlay-items='[{"label":"Latest News","href":"/{{ $locale }}/news"},{"label":"Press Releases","href":"/{{ $locale }}/pages/media"},{"label":"Market Data","href":"/{{ $locale }}/market"}]'
                        >Media</a></li>
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

                <a href="/{{ $locale }}/" class="header-logo-box" aria-label="Logo link">
                    <div class="logo-title">GLOBALTRDING</div>
                    <div class="logo-subtitle">We create value in industry</div>
                </a>
            </div>
        </div>
    </header>
    
    @php
        $hasHero = $hasHero ?? false;
    @endphp

    <main class="{{ $hasHero ? '' : 'main-offset' }}">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 mt-16 bg-white">
        @php
            $locale = app()->getLocale();
            $fallback = config('locales.default', 'en');

            $footerPages = \App\Models\Page::query()
                ->where('is_published', true)
                ->where('show_in_footer', true)
                ->orderBy('id')
                ->get();

            $companyLinks = $footerPages->map(function ($p) use ($locale, $fallback) {
                // Adjust field name depending on your Page model:
                // If your pages use "title" (typical) keep as-is.
                // If they use "name", replace $p->title with $p->name.
                $title = $p->title ?? $p->name ?? null;

                $label = is_array($title)
                    ? (data_get($title, $locale) ?: data_get($title, $fallback) ?: $p->slug)
                    : ((string) ($title ?: $p->slug));

                return [
                    'label' => $label,
                    'href' => "/{$locale}/pages/{$p->slug}",
                ];
            })->values();

            $productsLinks = collect([
                ['label' => 'Product Finder', 'href' => "/{$locale}/products"],
                // add later if you create public brands page:
                // ['label' => 'Brands', 'href' => "/{$locale}/brands"],
            ]);

            $newsLinks = collect([
                ['label' => 'Latest News', 'href' => "/{$locale}/news"],
                ['label' => 'Market', 'href' => "/{$locale}/market"],
            ]);

            $contactLinks = collect([
                ['label' => 'Collaboration', 'href' => "/{$locale}/collaboration"],
                // placeholders (create pages later and enable show_in_footer):
                // ['label' => 'Contact', 'href' => "/{$locale}/pages/contact"],
            ]);

            $legalLinks = collect([
                // Create these pages in Pages CMS and toggle show_in_footer=true:
                // ['label' => 'Privacy Policy', 'href' => "/{$locale}/pages/privacy-policy"],
                // ['label' => 'Terms of Use', 'href' => "/{$locale}/pages/terms"],
                // ['label' => 'Cookie Policy', 'href' => "/{$locale}/pages/cookies"],
            ]);
        @endphp

        <div class="mx-auto max-w-7xl px-4 py-12">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-12">
                {{-- Brand / short description --}}
                <div class="lg:col-span-4">
                    <a href="/{{ $locale }}" class="text-base font-semibold tracking-tight text-slate-900">
                        Globaltrding
                    </a>

                    <p class="mt-3 text-sm text-slate-600 leading-relaxed max-w-sm">
                        Industrial equipment & raw materials supplier supporting Oil & Gas, Petrochemical,
                        Refinery, and Chemical industries with trusted sourcing and multilingual experience.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2 text-xs text-slate-500">
                        <span class="rounded-full border border-slate-200 px-2 py-1">EN</span>
                        <span class="rounded-full border border-slate-200 px-2 py-1">TR</span>
                        <span class="rounded-full border border-slate-200 px-2 py-1">AR</span>
                        <span class="rounded-full border border-slate-200 px-2 py-1">FR</span>
                    </div>
                </div>

                {{-- Columns --}}
                <div class="lg:col-span-8 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Company</div>
                        <ul class="mt-4 space-y-2 text-sm">
                            @forelse ($companyLinks as $link)
                                <li>
                                    <a href="{{ $link['href'] }}" class="hover:text-slate-900 hover:underline">
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @empty
                                <li class="text-slate-500">
                                    Add Pages and enable “Show in footer”.
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <div>
                        <div class="text-sm font-semibold text-slate-900">Products</div>
                        <ul class="mt-4 space-y-2 text-sm">
                            @foreach ($productsLinks as $link)
                                <li>
                                    <a href="{{ $link['href'] }}" class="hover:text-slate-900 hover:underline">
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <div class="text-sm font-semibold text-slate-900">News</div>
                        <ul class="mt-4 space-y-2 text-sm">
                            @foreach ($newsLinks as $link)
                                <li>
                                    <a href="{{ $link['href'] }}" class="hover:text-slate-900 hover:underline">
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <div class="text-sm font-semibold text-slate-900">Contact</div>
                        <ul class="mt-4 space-y-2 text-sm">
                            @foreach ($contactLinks as $link)
                                <li>
                                    <a href="{{ $link['href'] }}" class="hover:text-slate-900 hover:underline">
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @endforeach

                            @if ($legalLinks->count())
                                <li class="pt-3 text-xs font-semibold text-slate-500">Legal</li>
                                @foreach ($legalLinks as $link)
                                    <li>
                                        <a href="{{ $link['href'] }}" class="hover:text-slate-900 hover:underline">
                                            {{ $link['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li class="pt-3 text-xs text-slate-500">
                                    (Create Privacy/Terms pages if needed)
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="mt-12 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t border-slate-200 pt-6">
                <div class="text-xs text-slate-500">
                    © {{ date('Y') }} Globaltrding. All rights reserved.
                </div>

                <div class="flex flex-wrap gap-3 text-xs">
                    <a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/products">Products</a>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/news">News</a>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/market">Market</a>
                    <a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/collaboration">Collaboration</a>
                </div>
            </div>
        </div>
    </footer>
    {{-- Nav Overlay (BASF-style mega menu) --}}
    <div id="navOverlay" class="nav-overlay" aria-hidden="true" role="dialog" aria-modal="true">
        {{-- Close button aligned with nav bar --}}
        <button id="navOverlayClose" class="nav-overlay__close" type="button" aria-label="Close menu">&#x2715;</button>

        <div class="nav-overlay__body">
            {{-- Left: scrollable nav list --}}
            <div class="nav-overlay__left-wrap">
                {{-- Products-only search (injected by JS under Product Finder) --}}
                <div id="navOverlaySearch" class="nav-overlay__search" style="display:none;" aria-hidden="true">
                    <input id="navOverlaySearchInput" type="search"
                           class="nav-overlay__search-input"
                           placeholder="Search products…"
                           autocomplete="off">
                </div>

                <nav id="navOverlayList" class="nav-overlay__list" aria-label="Section links"></nav>
            </div>

            {{-- Middle: scroll indicator --}}
            <div class="nav-overlay__scrolltrack" aria-hidden="true">
                <div id="navOverlayScrollThumb" class="nav-overlay__scrollthumb"></div>
            </div>

            {{-- Right: 2-column preview (image | description) --}}
            <div class="nav-overlay__preview">
                <div class="nav-overlay__preview-img-col">
                    <img id="navOverlayPreviewImg" class="nav-overlay__preview-img" src="" alt="">
                </div>
                <div class="nav-overlay__preview-text-col">
                    <div id="navOverlayPreviewTitle" class="nav-overlay__preview-title"></div>
                    <div id="navOverlayPreviewDesc" class="nav-overlay__preview-desc"></div>
                </div>
            </div>
        </div>
    </div>

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
    <div id="cookieBanner" class="cookie-banner hidden" aria-live="polite">
        <div class="cookie-banner__inner">
            <div class="cookie-banner__text">
                We use cookies to improve your experience and to measure site usage (Google Analytics).
                Read our <a href="/{{ app()->getLocale() }}/pages/cookie-policy">Cookie Policy</a>.
            </div>
            <div class="cookie-banner__actions">
                <button type="button" id="cookieReject" class="cookie-btn cookie-btn--secondary">Reject</button>
                <button type="button" id="cookieAccept" class="cookie-btn cookie-btn--primary">Accept</button>
            </div>
        </div>
    </div>
</body>
</html>