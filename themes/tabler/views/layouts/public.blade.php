<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Asset Base URL -->
    <meta name="asset-base-url"
        content="{{ config('appsolutely.asset_url') ?: config('appsolutely.storage.assets') ?? 'assets/' }}">
    <meta name="asset-build-hash" content="{{ build_hash() }}">

    <title>@title($page ?? null)</title>
    <meta name="keywords" content="@keywords($page ?? null)">
    <meta name="description" content="@description($page ?? null)">
    {!! structured_data() !!}
    {!! site_meta() !!}
    <link rel="canonical" href="{{ url()->current() }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset_url(site_favicon()) }}">
    <link rel="apple-touch-icon" href="{{ asset_url(site_favicon()) }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    @livewireStyles
    @vite([themed_path() . '/sass/app.scss', themed_path() . '/js/app.ts'], themed_build_path())
</head>

<body class="antialiased">
    {!! noscript() !!}
    @isset($page)
        <h1 class="d-none">{{ $page->h1_text ?? $page->title }}</h1>
    @endisset
    @yield('content')
    @livewireScripts
</body>
{!! tracking_code() !!}

</html>
