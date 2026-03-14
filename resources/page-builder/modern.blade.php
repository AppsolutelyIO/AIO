<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design your "{{ $page->name }}" Page - Page Builder</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Page Builder Assets (includes GrapesJS, FontAwesome, TailwindCSS) --}}
    @vite(['resources/page-builder/assets/css/app.css', 'resources/page-builder/assets/scss/app.scss', 'resources/page-builder/assets/ts/app.ts'], 'build/page-builder')
</head>

<body class="bg-slate-50 text-slate-800">
    {{-- Drag and Drop Overlay --}}
    <div class="drag-overlay" id="drag-overlay">
        <div class="drag-placeholder">Drag and drop blocks here</div>
    </div>

    @include('page-builder::partials._header')

    <main class="pt-20 mx-auto flex grapes-editor">
        <section class="flex-1 shadow-lg rounded-lg mr-4 px-6 editor-canvas-wrapper">
            <div id="editor-canvas" class="!bg-none !bg-white mb-4 px-8 pt-8 border-slate-300 rounded-lg">
            </div>
        </section>
        @include('page-builder::partials._sidebar')
    </main>

    @include('page-builder::partials._preview-modal')
    @include('page-builder::partials._block-option-modal')

    {{-- Page Builder Initialization --}}
    <script>
        // Initialize Page Builder with data (content = enriched with server-rendered block HTML)
        window.pageBuilderData = @json($page['content'] ?? ($page['setting'] ?? null));
        window.pageBuilderConfig = {
            blockRegistryUrl: '{{ admin_route('api.pages.block-registry') }}',
            blockOptionUrl: '{{ admin_route('api.pages.block-option') }}',
            blockHtmlUrl: '{{ admin_route('api.pages.block-html') }}',
            renderBlockUrl: '{{ admin_route('api.pages.render-block') }}',
            fileUploadUrl: '{{ admin_route('api.files.upload') }}',
            saveUrl: '{{ admin_route('api.pages.save', [$reference]) }}',
            resetUrl: '{{ admin_route('api.pages.reset', [$reference]) }}',
            dataUrl: '{{ admin_route('api.pages.data', [$reference]) }}',
            themedStyles: @json(themed_styles())
        };
    </script>
</body>

</html>
