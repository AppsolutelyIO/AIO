@php
    $sliderId = 'mediaSlider-' . $this->getId();
@endphp

<section class="slider slider--default">
    {{-- Swiper Carousel --}}
    @if (!empty($displayOptions['slides']))
        <div class="swiper {{ $sliderId }}" data-slider-id="{{ $sliderId }}">
            <div class="swiper-wrapper">
                @foreach ($displayOptions['slides'] as $index => $slide)
                    <div class="swiper-slide">
                        @if (($slide['type'] ?? 'image') === 'video')
                            <div class="slider__slide-video">
                                <video class="lazy" data-src="{{ asset_url($slide['url']) }}" controls preload="none">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @else
                            <div class="slider__slide-image">
                                <img class="lazy" data-src="{{ asset_url($slide['url']) }}"
                                    alt="{{ $slide['image_alt'] ?? '' }}" src="">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Pagination (if enabled) --}}
            @if ($displayOptions['show_indicators'] && count($displayOptions['slides']) > 1)
                <div class="swiper-pagination"></div>
            @endif
        </div>

        {{-- Navigation Controls at bottom center (if enabled) --}}
        @if ($displayOptions['show_controls'] && count($displayOptions['slides']) > 1)
            <div class="slider__controls">
                <button type="button" class="swiper-button-prev border-0 bg-transparent p-0"
                    data-slider-id="{{ $sliderId }}" aria-label="Previous slide">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="swiper-button-next border-0 bg-transparent p-0"
                    data-slider-id="{{ $sliderId }}" aria-label="Next slide">
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        @endif
    @else
        <div class="container-xl py-3">
            <div class="alert alert-info mb-0">No slides available</div>
        </div>
    @endif
</section>
