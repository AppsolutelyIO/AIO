@props([
    'show' => false,
    'text' => 'Overseas model shown',
    'position' => 'bottom-0 end-0',
    'padding' => 'p-4',
    'zIndex' => 'z-2',
])

@if ($show)
    <!-- Overseas model shown text -->
    <div class="position-absolute {{ $position }} {{ $padding }} {{ $zIndex }}">
        <span class="text-white small opacity-75">{{ $text }}</span>
    </div>
@endif
