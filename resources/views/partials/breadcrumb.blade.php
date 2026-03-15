@if ($breadcrumb)
    <div class="breadcrumb-wrapper col-12">
    <ol class="breadcrumb float-right text-capitalize">
        <li class="breadcrumb-item"><a href="{{ admin_url('/') }}"><i class="fa fa-dashboard"></i> {{admin_trans('admin.home')}}</a></li>
        @foreach($breadcrumb as $item)
            @if($loop->last)
                <li class="active breadcrumb-item">
                    @if (\Illuminate\Support\Arr::has($item, 'icon'))
                        <i class="fa {{ $item['icon'] }}"></i>
                    @endif
                    {{ $item['text'] }}
                </li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{ admin_url(\Illuminate\Support\Arr::get($item, 'url')) }}">
                        @if (\Illuminate\Support\Arr::has($item, 'icon'))
                            <i class="fa {{ $item['icon'] }}"></i>
                        @endif
                        {{ $item['text'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
    </div>
@elseif(config('admin.enable_default_breadcrumb'))
    <div class="breadcrumb-wrapper col-12">
    <ol class="breadcrumb float-right text-capitalize">
        <li class="breadcrumb-item"><a href="{{ admin_url('/') }}"><i class="fa fa-dashboard"></i> {{admin_trans('admin.home')}}</a></li>
        @for($i = 2; $i <= ($len = count(Request::segments())); $i++)
            @php
                $segmentUrl = implode('/', array_slice(Request::segments(), 0, $i));
                $hasRoute = false;
                if ($i < $len) {
                    try {
                        $matched = app('router')->getRoutes()->match(app('request')->create($segmentUrl, 'GET'));
                        $hasRoute = ! $matched->isFallback;
                    } catch (\Throwable $e) {}
                }
            @endphp
            @if($i < $len && $hasRoute)
                <li class="breadcrumb-item">
                    <a href="{{ url($segmentUrl) }}">
                        {{admin_trans_label(Request::segment($i))}}
                    </a>
                </li>
            @elseif($i < $len)
                <li class="breadcrumb-item">
                    {{admin_trans_label(Request::segment($i))}}
                </li>
            @else
                <li class="active breadcrumb-item">
                    {{admin_trans_label(Request::segment($i))}}
                </li>
            @endif
        @endfor
    </ol>
    </div>
@endif

<div class="clearfix"></div>
