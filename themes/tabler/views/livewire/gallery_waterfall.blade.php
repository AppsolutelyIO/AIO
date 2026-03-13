@php
    $photos = array_values(array_filter($displayOptions['photos'] ?? [], fn($p) => !empty($p['image_src'] ?? null)));
@endphp

<section class="gallery gallery--waterfall container-xl my-5"
    data-asset-base-url="{{ asset_url(null, false) }}">
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
                        {{ is_array($description) ? ($description['text'] ?? '') : $description }}
                    </p>
                @endforeach
            @endif
        </div>
    @endif

    @if (count($photos) > 0)
        <div class="gallery__waterfall" style="columns: 3; column-gap: 1rem;">
            @foreach ($photos as $index => $photo)
                <div class="gallery__waterfall-item card mb-3" style="break-inside: avoid;">
                    @if (!empty($photo['link']))
                        <a href="{{ $photo['link'] }}" class="d-block">
                    @endif
                    <img src="{{ asset_url($photo['image_src']) }}"
                        class="gallery__waterfall-img card-img-top"
                        alt="{{ $photo['alt'] ?? $photo['title'] ?? 'Gallery photo ' . ($index + 1) }}"
                        loading="lazy">
                    @if (!empty($photo['link']))
                        </a>
                    @endif

                    @if (!empty($photo['title']) || !empty($photo['caption']))
                        <div class="card-body p-2">
                            @if (!empty($photo['title']))
                                <h3 class="card-title h5 mb-1">{{ $photo['title'] }}</h3>
                            @endif
                            @if (!empty($photo['subtitle']))
                                <p class="text-muted small mb-1">{{ $photo['subtitle'] }}</p>
                            @endif
                            @if (!empty($photo['caption']))
                                <p class="text-muted small mb-0">{{ $photo['caption'] }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>
