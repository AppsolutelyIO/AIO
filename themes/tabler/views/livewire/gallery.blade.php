<section class="gallery container-xl my-5" data-asset-base-url="{{ asset_url(null, false) }}">
    @php
        $photos = array_values(
            array_filter($displayOptions['photos'] ?? [], fn($p) => !empty($p['image_src'] ?? null)),
        );
    @endphp

    @if (!empty($displayOptions['title']) || !empty($displayOptions['subtitle']) || !empty($displayOptions['descriptions']))
        <div class="gallery__header text-center mb-4">
            @if (!empty($displayOptions['title']))
                <h2 class="gallery__title h1 mb-2">{{ $displayOptions['title'] }}</h2>
            @endif
            @if (!empty($displayOptions['subtitle']))
                <p class="gallery__subtitle lead text-muted mb-2">{{ $displayOptions['subtitle'] }}</p>
            @endif
            @if (!empty($displayOptions['descriptions']) && is_array($displayOptions['descriptions']))
                @foreach ($displayOptions['descriptions'] as $description)
                    <p class="gallery__description text-muted mb-0">
                        {{ is_array($description) ? $description['text'] ?? '' : $description }}</p>
                @endforeach
            @endif
        </div>
    @endif

    <div class="gallery__filters mb-4 d-flex flex-wrap gap-2 justify-content-center" id="gallery-filters"
        aria-label="Photo filters"></div>

    <div class="gallery__grid row g-3" id="gallery-grid" data-photos='@json($photos)'></div>

    <template id="gallery-card-template">
        <div class="gallery__card col-6 col-lg-4">
            <div class="gallery__card-inner card card-link card-link-pop h-100">
                <div class="gallery__card-image-wrap ratio ratio-4x3">
                    <img class="gallery__card-img lazy card-img-top object-fit-cover" alt="">
                </div>
                <div class="gallery__card-body card-body">
                    <h3 class="gallery__card-title card-title h5 mb-1"></h3>
                    <p class="gallery__card-subtitle text-muted small mb-2"></p>
                    <div class="gallery__card-text text-muted small mb-2"></div>
                    <div class="gallery__card-price fw-semibold text-primary"></div>
                </div>
            </div>
        </div>
    </template>
</section>
