<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Appsolutely') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @livewireStyles
    @vite([themed_path() . '/sass/app.scss', themed_path() . '/js/app.ts'], themed_build_path())
</head>

<body class="antialiased">
    <x-banner />

    <div class="page">
        {{-- Navbar --}}
        @livewire('navigation-menu')

        <div class="page-wrapper">
            {{-- Page Header --}}
            @if (isset($header))
                <div class="page-header d-print-none">
                    <div class="container-xl">
                        <div class="page-pretitle">{{ config('app.name', 'Appsolutely') }}</div>
                        {{ $header }}
                    </div>
                </div>
            @endif

            {{-- Page Body --}}
            <div class="page-body">
                <div class="container-xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    @stack('modals')

    @livewireScripts
    @stack('scripts')
</body>

</html>
