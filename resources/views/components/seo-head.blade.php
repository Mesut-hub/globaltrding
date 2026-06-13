@php
use Illuminate\Support\Facades\Storage;

$locale      = $locale   ?? app()->getLocale();
$fallback    = $fallback ?? config('locales.default', 'en');
$appUrl      = rtrim((string) config('app.url'), '/');

// Normalize appUrl for production
if ($appUrl === '' || str_contains($appUrl, '127.0.0.1') || str_contains($appUrl, 'localhost')) {
    $appUrl = rtrim(request()->getSchemeAndHttpHost(), '/');
}

$currentUrl  = $appUrl . request()->getRequestUri();
$isProd      = app()->environment('production');
$supported   = config('locales.supported', ['en']);
$defaultLocale = config('locales.default', 'en');

// Path computation for hreflang
$path     = '/' . ltrim(request()->path(), '/');
$parts    = explode('/', trim($path, '/'));
$curLoc   = in_array($parts[0] ?? '', $supported, true) ? $parts[0] : $defaultLocale;
$rest     = implode('/', array_slice($parts, 1));

// Resolved values (passed as props or defaults)
$metaTitle       = $title       ?? config('app.name');
$metaDescription = $description ?? '';
$ogImage         = $ogImage     ?? ($appUrl . '/images/og-default.png');
$ogType          = $ogType      ?? 'website';
$canonical       = $canonical   ?? $currentUrl;
$robots          = $robots      ?? ($isProd ? 'index,follow' : 'noindex,nofollow');

// Trim to safe lengths
$metaTitle       = mb_substr(trim($metaTitle), 0, 70);
$metaDescription = mb_substr(trim($metaDescription), 0, 160);

// OG locale map
$ogLocaleMap = ['en' => 'en_US', 'tr' => 'tr_TR', 'ar' => 'ar_AR', 'fr' => 'fr_FR'];
$ogLocale    = $ogLocaleMap[$locale] ?? $ogLocaleMap[$fallback] ?? 'en_US';

// Published/modified times (optional)
$publishedTime = $publishedTime ?? null;
$modifiedTime  = $modifiedTime  ?? null;
@endphp

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- hreflang alternates --}}
@foreach ($supported as $loc)
    @php $altPath = $rest !== '' ? "/{$loc}/{$rest}" : "/{$loc}"; @endphp
    <link rel="alternate" hreflang="{{ $loc }}" href="{{ $appUrl . $altPath }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ $appUrl }}/{{ $defaultLocale }}{{ $rest !== '' ? '/' . $rest : '' }}">

{{-- OpenGraph --}}
<meta property="og:site_name" content="Globaltrding">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $currentUrl }}">
<meta property="og:locale" content="{{ $ogLocale }}">
@foreach ($supported as $loc)
    @continue($loc === $locale)
    @if(isset($ogLocaleMap[$loc]))
        <meta property="og:locale:alternate" content="{{ $ogLocaleMap[$loc] }}">
    @endif
@endforeach
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="{{ $imageWidth ?? 1200 }}">
    <meta property="og:image:height" content="{{ $imageHeight ?? 630 }}">
    <meta property="og:image:alt" content="{{ $metaTitle }}">
@endif
@if($publishedTime)
    <meta property="article:published_time" content="{{ $publishedTime }}">
@endif
@if($modifiedTime)
    <meta property="article:modified_time" content="{{ $modifiedTime }}">
@endif

{{-- Twitter / X --}}
<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:site" content="@Globaltrding">
<meta name="twitter:creator" content="@Globaltrding">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
@if ($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="{{ $metaTitle }}">
@endif

{{-- Favicons --}}
<link rel="icon" href="{{ $appUrl }}/images/favicon.ico">
<link rel="apple-touch-icon" href="{{ $appUrl }}/images/logo.png">