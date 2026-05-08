@php
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');

    $t = function ($arr) use ($locale, $fallback) {
        if (!is_array($arr)) return (string)($arr ?? '');
        return (string)($arr[$locale] ?? $arr[$fallback] ?? (count($arr) ? reset($arr) : ''));
    };

    $settings = class_exists(\App\Models\SiteSetting::class)
        ? \App\Models\SiteSetting::getCached()
        : [];

    $linkedin = $settings['linkedin_url'] ?? null;
    $instagram = $settings['instagram_url'] ?? null;
    $x = $settings['x_url'] ?? null;
    $youtube = $settings['youtube_url'] ?? null;

    $companyPages = \App\Models\Page::query()->where('is_published', true)->where('show_in_company', true)->orderBy('slug')->get();
    $productPages = \App\Models\Page::query()->where('is_published', true)->where('show_in_products', true)->orderBy('slug')->get();
    $infoPages    = \App\Models\Page::query()->where('is_published', true)->where('show_in_information', true)->orderBy('slug')->get();
    $servicePages = \App\Models\Page::query()->where('is_published', true)->where('show_in_service', true)->orderBy('slug')->get();

    $pageUrl = fn ($slug) => "/{$locale}/pages/{$slug}";
    $year = date('Y');

    $iconLinkClass = 'inline-flex items-center justify-center w-10 h-10 rounded-full border border-slate-200 bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-800 transition';
@endphp

<footer class="mt-16 bg-slate-50 border-t border-slate-200">
    {{-- Row 1: Social --}}
    <div class="mx-auto max-w-7xl px-4 py-6 flex items-center justify-between gap-6">
        <div class="text-sm font-semibold text-slate-900 tracking-tight">
            {{ __('Global Trading') }}
        </div>

        <div class="flex items-center gap-3">
            @if ($linkedin)
                <a class="{{ $iconLinkClass }}" href="{{ $linkedin }}" target="_blank" rel="noopener" aria-label="LinkedIn">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1 4.98 2.12 4.98 3.5zM0.5 8.5H4.5V23.5H0.5V8.5zM8.5 8.5H12.3V10.55H12.35C12.88 9.55 14.18 8.5 16.1 8.5C20.2 8.5 21 11.1 21 14.5V23.5H17V15.6C17 13.7 17 11.3 14.5 11.3C12 11.3 11.6 13.2 11.6 15.5V23.5H7.6V8.5H8.5z"/></svg>
                </a>
            @endif

            @if ($instagram)
                <a class="{{ $iconLinkClass }}" href="{{ $instagram }}" target="_blank" rel="noopener" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 2A3.75 3.75 0 0 0 4 7.75v8.5A3.75 3.75 0 0 0 7.75 20h8.5A3.75 3.75 0 0 0 20 16.25v-8.5A3.75 3.75 0 0 0 16.25 4h-8.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm6.4-2.15a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0z"/></svg>
                </a>
            @endif

            @if ($x)
                <a class="{{ $iconLinkClass }}" href="{{ $x }}" target="_blank" rel="noopener" aria-label="X">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M18.9 2H22l-6.8 7.8L23.5 22h-6.6l-5.2-6.8L5.9 22H2.8l7.3-8.4L0.5 2h6.7l4.7 6.1L18.9 2zm-1.1 18h1.7L6.3 3.9H4.4L17.8 20z"/></svg>
                </a>
            @endif

            @if ($youtube)
                <a class="{{ $iconLinkClass }}" href="{{ $youtube }}" target="_blank" rel="noopener" aria-label="YouTube">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M21.6 7.2s-.2-1.6-.9-2.3c-.9-.9-1.9-.9-2.4-1C14.7 3.5 12 3.5 12 3.5h0s-2.7 0-6.3.4c-.5.1-1.5.1-2.4 1C2.6 5.6 2.4 7.2 2.4 7.2S2 9.1 2 11v2c0 1.9.4 3.8.4 3.8s.2 1.6.9 2.3c.9.9 2.1.9 2.6 1 1.9.2 6.1.4 6.1.4s2.7 0 6.3-.4c.5-.1 1.5-.1 2.4-1 .7-.7.9-2.3.9-2.3s.4-1.9.4-3.8v-2c0-1.9-.4-3.8-.4-3.8zM10 15.5v-7l6 3.5-6 3.5z"/></svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Row 2: Columns --}}
    <div class="border-t border-slate-200">
        <div class="mx-auto max-w-7xl px-4 py-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <div class="text-sm font-semibold text-slate-900">{{ __('Global Trading') }}</div>
                <p class="mt-3 text-sm text-slate-600 leading-relaxed max-w-sm">
                    {{ __('We create value in industry with trusted sourcing, fast response times, and multilingual customer support.') }}
                </p>
            </div>

            <div>
                <div class="text-sm font-semibold text-slate-900">{{ __('Company') }}</div>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach($companyPages as $p)
                        <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl($p->slug) }}">{{ $t($p->title) ?: $p->slug }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <div class="text-sm font-semibold text-slate-900">{{ __('Products') }}</div>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/products">{{ __('Product Finder') }}</a></li>
                    <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/industries">{{ __('Industries') }}</a></li>
                    @foreach($productPages as $p)
                        <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl($p->slug) }}">{{ $t($p->title) ?: $p->slug }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <div class="text-sm font-semibold text-slate-900">{{ __('Information') }}</div>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/news">{{ __('Latest News') }}</a></li>
                    <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/market">{{ __('Market') }}</a></li>
                    @foreach($infoPages as $p)
                        <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl($p->slug) }}">{{ $t($p->title) ?: $p->slug }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <div class="text-sm font-semibold text-slate-900">{{ __('Service') }}</div>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach($servicePages as $p)
                        <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl($p->slug) }}">{{ $t($p->title) ?: $p->slug }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Row 3: Legal --}}
    <div class="border-t border-slate-200">
        <div class="mx-auto max-w-7xl px-4 py-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-xs text-slate-500">© {{ $year }} Global Trading</div>

            <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs">
                <a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl('disclaimer') }}">{{ __('Disclaimer') }}</a>
                <a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl('credits') }}">{{ __('Credits') }}</a>
                <a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl('privacy-policy') }}">{{ __('Privacy Policy') }}</a>
                <a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl('responsible-disclosure-statement') }}">{{ __('Responsible Disclosure') }}</a>
                <a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl('contact') }}">{{ __('Contact') }}</a>
            </div>
        </div>
    </div>
</footer>