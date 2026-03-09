<div class="{{$viewClass['form-group']}}">

    <label class="{{$viewClass['label']}} control-label">{!! $label !!}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <div id="{{ $id }}" class="{{ $class }}" {!! $attributes !!}></div>
        <input type="hidden" name="{{ $name }}" id="{{ $id }}_val">

        @include('admin::form.help-block')

    </div>
</div>

<script require="@vditor" init="{!! $selector !!}">
    var _vditorValue = @json($value ?? '');
    var _vditorOptions = {!! json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};

    _vditorOptions.value = _vditorValue;
    _vditorOptions.input = function (val) {
        document.getElementById('{{ $id }}_val').value = val;
    };
    _vditorOptions.after = function () {
        document.getElementById('{{ $id }}_val').value = _vditorValue;
    };

    new Vditor('{{ $id }}', _vditorOptions);
</script>
