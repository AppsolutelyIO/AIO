<section class="divider position-relative w-100" style="{{ $this->getBackgroundStyle() }}"
    @if (isset($displayOptions['responsive']['hide_on_mobile']) && $displayOptions['responsive']['hide_on_mobile']) data-hide-mobile="true" @endif>
    <!-- Background Overlay -->
    @if (isset($displayOptions['overlay']['enabled']) && $displayOptions['overlay']['enabled'])
        <div class="divider__overlay position-absolute top-0 start-0 w-100 h-100"
            style="background-color: {{ $displayOptions['overlay']['color'] ?? '#000000' }}; opacity: {{ $displayOptions['overlay']['opacity'] ?? 0.3 }};">
        </div>
    @endif
</section>
