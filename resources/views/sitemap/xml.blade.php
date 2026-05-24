@<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
>
@foreach ($entries as $e)
<url>
    <loc>{{ $e['url'] }}</loc>
    @if (!empty($e['lastmod']))<lastmod>{{ $e['lastmod'] }}</lastmod>@endif
    <changefreq>{{ $e['changefreq'] ?? 'monthly' }}</changefreq>
    <priority>{{ number_format($e['priority'] ?? 0.5, 1) }}</priority>
    @if (!empty($e['imageUrl']))
    <image:image>
        <image:loc>{{ $e['imageUrl'] }}</image:loc>
        @if (!empty($e['imageTitle']))<image:title>{{ htmlspecialchars($e['imageTitle']) }}</image:title>@endif
    </image:image>
    @endif
</url>
@endforeach
</urlset>