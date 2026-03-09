@extends('admin::grid.displayer.editinline.template')

@section('field')
    <textarea class="form-control ie-input" rows="5" style="resize:none;overflow:hidden;"></textarea>
@endsection

<script>
@section('popover-content')
    $template.find('textarea').text($trigger.data('value'));
@endsection

@section('popover-shown')
    @if(! empty($mask))
    $popover.find('.ie-input').inputmask({!! admin_javascript_json($mask) !!});
    @endif
@endsection
</script>
