{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('meta_title', 'Globaltrding')</title>
    <meta name="description" content="@yield('meta_description', 'Industrial equipment & raw materials supplier.')">

    <meta name="robots" content="index,follow">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-900 antialiased">
    {{-- Top bar / header placeholder --}}
    @php $locale = app()->getLocale(); @endphp
    <header id="siteHeader" class="site-header">
        <div class="header-inner">
            {{-- Left: nav bar --}}
            <nav class="main-nav" aria-label="Main navigation">
                <ul>
                    <li><a href="/{{ $locale }}/" class="is-global">Global</a></li>
                    <li><a href="/{{ $locale }}/pages/who-we-are">Who we are</a></li>
                    <li><a href="/{{ $locale }}/products">Products</a></li>
                    <li><a href="/{{ $locale }}/pages/investors">Investors</a></li>
                    <li><a href="/{{ $locale }}/pages/careers">Careers</a></li>
                    <li><a href="/{{ $locale }}/pages/media">Media</a></li>
                </ul>
            </nav>

            {{-- Right: icons + logo box --}}
            <div class="header-right">
                <div class="header-icons" aria-label="Header actions">
                    <button id="searchOpen" type="button" aria-label="Search">⌕</button>
                    <button id="langBtn" type="button" aria-label="Language" title="Language">🌐</button>
                    <div class="lang-dropdown">
                        <div id="langMenu" class="lang-menu-vertical" aria-label="Language menu">
                            @foreach (config('locales.supported') as $loc)
                                <a href="/{{ $loc }}" class="{{ $locale === $loc ? 'active' : '' }}">
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

    <script>
        (() => {
            const header = document.getElementById('siteHeader');

            const onScroll = () => {
                if (window.scrollY > 80) header.classList.add('scrolled');
                else header.classList.remove('scrolled');
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            const btn = document.getElementById('langBtn');
            const menu = document.getElementById('langMenu');

            btn?.addEventListener('click', () => {
                menu?.classList.toggle('open');
            });

            document.addEventListener('click', (e) => {
                if (!menu || !btn) return;
                if (menu.contains(e.target) || btn.contains(e.target)) return;
                menu.classList.remove('open');
            });
        })();
    </script>

    @php
        $hasHero = $hasHero ?? false;
    @endphp

    <main class="{{ $hasHero ? '' : 'pt-28' }}">
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
    <script>
        (() => {
            const openBtn = document.getElementById('searchOpen');
            const overlay = document.getElementById('searchOverlay');
            const closeBtn = document.getElementById('searchClose');

            const open = () => {
                overlay?.classList.remove('hidden');
                setTimeout(() => document.getElementById('siteSearchInput')?.focus(), 50);
            };
            const close = () => overlay && overlay.classList.add('hidden');
            const input = document.getElementById('siteSearchInput');
            const resultsEl = document.getElementById('searchResults');
            const hintEl = document.getElementById('searchHint');
            let timer = null;

            function esc(s){ return (s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

            async function doSearch(q){
            const locale = document.documentElement.lang || 'en';
            const res = await fetch(`/${locale}/search?q=${encodeURIComponent(q)}`);
            const json = await res.json();
            const items = json.results || [];

            if (!items.length){
                resultsEl.innerHTML = `<div class="search-hint">No results found.</div>`;
                return;
            }

            resultsEl.innerHTML = items.map(item => {
                const img = item.image
                ? `<img class="search-result__img" src="${esc(item.image)}" alt="">`
                : `<div class="search-result__img">${esc(item.type)}</div>`;

                return `
                <a class="search-result" href="${esc(item.url)}">
                    ${img}
                    <div>
                    <div class="search-result__title">${esc(item.title)}</div>
                    <div class="search-result__meta">${esc(item.type)}</div>
                    </div>
                </a>
                `;
            }).join('');
            }

            input?.addEventListener('input', () => {
            const q = input.value.trim();
            clearTimeout(timer);

            if (q.length < 3){
                hintEl.textContent = 'Type at least 3 characters…';
                resultsEl.innerHTML = '';
                return;
            }

            hintEl.textContent = 'Searching…';
            timer = setTimeout(() => doSearch(q), 250);
            });

            openBtn?.addEventListener('click', open);
            closeBtn?.addEventListener('click', close);

            overlay?.addEventListener('click', (e) => {
                if (e.target === overlay) close();
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });
        })();
    </script>
</body>
</html>