<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($entries as $e)
    <url>
        <loc>{{ $e['loc'] }}</loc>
        @if (!empty($e['lastmod']))
            <lastmod>{{ $e['lastmod'] }}</lastmod>
        @endif
    </url>
@endforeach
</urlset>