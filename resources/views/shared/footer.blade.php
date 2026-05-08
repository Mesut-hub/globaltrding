@php
  $locale = app()->getLocale();
  $fallback = config('locales.default', 'en');

  $t = function ($arr) use ($locale, $fallback) {
    if (!is_array($arr)) return (string)($arr ?? '');
    return (string)($arr[$locale] ?? $arr[$fallback] ?? (count($arr) ? reset($arr) : ''));
  };

  $settings = \App\Models\SiteSetting::getCached();

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
@endphp

<footer class="border-t border-slate-200 bg-white">
  {{-- Row 1: Social --}}
  <div class="mx-auto max-w-7xl px-4 py-6 flex items-center gap-4">
    <div class="text-sm text-slate-600">{{ __('Follow us') }}</div>

    <div class="flex items-center gap-3">
      @if($linkedin)<a href="{{ $linkedin }}" target="_blank" rel="noopener" class="text-slate-700 hover:text-slate-900">LinkedIn</a>@endif
      @if($instagram)<a href="{{ $instagram }}" target="_blank" rel="noopener" class="text-slate-700 hover:text-slate-900">Instagram</a>@endif
      @if($x)<a href="{{ $x }}" target="_blank" rel="noopener" class="text-slate-700 hover:text-slate-900">X</a>@endif
      @if($youtube)<a href="{{ $youtube }}" target="_blank" rel="noopener" class="text-slate-700 hover:text-slate-900">YouTube</a>@endif
    </div>
  </div>

  {{-- Row 2: Columns --}}
  <div class="border-t border-slate-200">
    <div class="mx-auto max-w-7xl px-4 py-10 grid gap-8 md:grid-cols-5">
      <div>
        <div class="font-semibold text-slate-900">{{ __('Global Trading') }}</div>
        <p class="mt-3 text-sm text-slate-600 text-justify">
          {{ __('Industrial equipment & raw materials supplier supporting Oil & Gas, Petrochemical,
                        Refinery, and Chemical industries with trusted sourcing and multilingual experience.') }}
        </p>
      </div>

      <div>
        <div class="font-semibold text-slate-900">{{ __('Company') }}</div>
        <ul class="mt-3 space-y-2 text-sm">
          @foreach($companyPages as $p)
            <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl($p->slug) }}">{{ $t($p->title) ?: $p->slug }}</a></li>
          @endforeach
        </ul>
      </div>

      <div>
        <div class="font-semibold text-slate-900">{{ __('Products') }}</div>
        <ul class="mt-3 space-y-2 text-sm">
          <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/products">{{ __('Product Finder') }}</a></li>
          <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/industries">{{ __('Industries') }}</a></li>
          @foreach($productPages as $p)
            <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl($p->slug) }}">{{ $t($p->title) ?: $p->slug }}</a></li>
          @endforeach
        </ul>
      </div>

      <div>
        <div class="font-semibold text-slate-900">{{ __('Information') }}</div>
        <ul class="mt-3 space-y-2 text-sm">
          <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/news">{{ __('Latest News') }}</a></li>
          <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="/{{ $locale }}/market">{{ __('Market') }}</a></li>
          @foreach($infoPages as $p)
            <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl($p->slug) }}">{{ $t($p->title) ?: $p->slug }}</a></li>
          @endforeach
        </ul>
      </div>

      <div>
        <div class="font-semibold text-slate-900">{{ __('Service') }}</div>
        <ul class="mt-3 space-y-2 text-sm">
          @foreach($servicePages as $p)
            <li><a class="text-slate-600 hover:text-slate-900 hover:underline" href="{{ $pageUrl($p->slug) }}">{{ $t($p->title) ?: $p->slug }}</a></li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  {{-- Row 3: Legal --}}
  <div class="border-t border-slate-200">
    <div class="mx-auto max-w-7xl px-4 py-5 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
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