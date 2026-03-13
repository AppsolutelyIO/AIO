@extends('layouts.public')

@section('content')
    <div>
        @forelse ($page->blocks as $block)
            <div id="block-{{ $block->reference }}" class="block-wrapper">
                @renderBlock($block, $page)
            </div>
        @empty
        @endforelse
    </div>
@endsection
