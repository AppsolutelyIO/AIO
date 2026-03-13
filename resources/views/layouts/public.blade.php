<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@title($page)</title>
    <meta name="keywords" content="@keywords($page)">
    <meta name="description" content="@description($page)">
    <link rel="canonical" href="{{ url()->current() }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset_url(site_favicon()) }}">
    <link rel="apple-touch-icon" href="{{ asset_url(site_favicon()) }}">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Noto+Sans+Thai:wght@100..900&display=swap"
        rel="stylesheet">

    @vite([themed_path() . '/sass/app.scss', themed_path() . '/js/app.js'], themed_build_path())
</head>

<body>
    <div id="scrollTrigger" class="position-absolute top-0 w-100" style="height: 1px;"></div>
    @yield('content')
</body>

</html>
