<div {!! $attributes !!}><textarea style="display:none;">{!! $content !!}</textarea></div>

<script first>
    var ele = window.Element;
    AIO.eMatches = ele.prototype.matches ||
        ele.prototype.msMatchesSelector ||
        ele.prototype.webkitMatchesSelector;
</script>

<script require="@editor-md">
    editormd.markdownToHTML('{{ $id }}', {!! admin_javascript_json($options) !!});

    Element.prototype.matches = AIO.eMatches;
</script>