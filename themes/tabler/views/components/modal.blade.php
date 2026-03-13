@props(['id', 'maxWidth'])

@php
    $id = $id ?? md5($attributes->wire('model'));

    $maxWidth = [
        'sm' => 'modal-sm',
        'md' => '',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
        '2xl' => 'modal-xl',
    ][$maxWidth ?? 'md'];
@endphp

<div x-data="{ show: @entangle($attributes->wire('model')) }" x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false" x-show="show" id="{{ $id }}"
    class="modal-backdrop-custom" style="display: none;">
    {{-- Backdrop --}}
    <div x-show="show" class="modal-overlay" x-on:click="show = false"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    {{-- Modal content --}}
    <div x-show="show"
        class="modal d-block" tabindex="-1"
        x-trap.inert.noscroll="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">
        <div class="modal-dialog modal-dialog-centered {{ $maxWidth }}">
            <div class="modal-content">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
