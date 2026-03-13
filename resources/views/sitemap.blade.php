@php echo '<?xml version="1.0" encoding="UTF-8"@endphp'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @foreach ($urls as $url)
        <url>
            <loc>{{ $url['loc'] }}</loc>
            <lastmod>{{ $url['lastmod'] }}</lastmod>
            <changefreq>{{ $url['changefreq'] }}</changefreq>
            <priority>{{ $url['priority'] }}</priority>
            @if (!empty($url['hreflang']))
                @foreach ($url['hreflang'] as $hreflang)
                    <xhtml:link rel="alternate" hreflang="{{ $hreflang['lang'] }}" href="{{ $hreflang['href'] }}" />
                @endforeach
            @endif
        </url>
    @endforeach
</urlset>
