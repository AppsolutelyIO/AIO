@props([
    'align' => 'end',
    'contentClasses' => '',
])

<div class="nav-item dropdown" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
    <div @click="open = ! open" role="button">
        {{ $trigger }}
    </div>

    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="dropdown-menu dropdown-menu-{{ $align }} show {{ $contentClasses }}"
        style="display: none;" @click="open = false">
        {{ $content }}
    </div>
</div>
