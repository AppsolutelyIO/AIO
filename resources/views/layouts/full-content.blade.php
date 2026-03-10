@section('content')
    <section class="content">
        @include('admin::partials.alerts')
        @include('admin::partials.exception')

        {!! $content !!}

        @include('admin::partials.toastr')
    </section>
@endsection

@section('app')
    {!! Appsolutely\AIO\Admin::asset()->styleToHtml() !!}

    <div class="content-body" id="app">
        {{-- 页面埋点--}}
        {!! admin_section(Appsolutely\AIO\Admin::SECTION['APP_INNER_BEFORE']) !!}

        @yield('content')

        {{-- 页面埋点--}}
        {!! admin_section(Appsolutely\AIO\Admin::SECTION['APP_INNER_AFTER']) !!}
    </div>

    {!! Appsolutely\AIO\Admin::asset()->scriptToHtml() !!}
    <div class="extra-html">{!! Appsolutely\AIO\Admin::html() !!}</div>
@endsection


@if(!request()->pjax())
    @include('admin::layouts.full-page', ['header' => $header])
@else
    <title>{{ Appsolutely\AIO\Admin::title() }} @if($header) | {{ $header }}@endif</title>

    <script>Dcat.wait();</script>

    {!! Appsolutely\AIO\Admin::asset()->cssToHtml() !!}
    {!! Appsolutely\AIO\Admin::asset()->jsToHtml() !!}

    @yield('app')
@endif
