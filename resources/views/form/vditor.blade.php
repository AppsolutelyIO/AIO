<div class="{{$viewClass['form-group']}}">

    <label class="{{$viewClass['label']}} control-label">{!! $label !!}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div id="{{ $id }}" class="{{ $class }}" {!! $attributes !!}></div>
        <input type="hidden" name="{{ $name }}" id="{{ $id }}_val" value="{{ $value ?? '' }}">

        @include('admin::form.help-block')

    </div>
</div>

<script require="@vditor" init="{!! $selector !!}">
    var _vditorValue = @json($value ?? '');
    var _vditorOptions = {!! json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

    _vditorOptions.input = function (val) {
        document.getElementById('{{ $id }}_val').value = val;
    };
    _vditorOptions.after = function () {
        if (_vditorValue) {
            _vditor_{{ str_replace('-', '_', $id) }}.setValue(_vditorValue);
        }
        document.getElementById('{{ $id }}_val').value = _vditorValue;
    };

    var _vditor_{{ str_replace('-', '_', $id) }} = new Vditor('{{ $id }}', _vditorOptions);
</script>
