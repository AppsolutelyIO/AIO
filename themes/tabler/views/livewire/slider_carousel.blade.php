@php
    $sliderId = 'mediaSlider-' . $this->getId();
@endphp

<section class="slider slider--carousel">
    {{-- Title, Subtitle, Description Section --}}
    @if (!empty($displayOptions['title']) || !empty($displayOptions['subtitle']) || !empty($displayOptions['description']))
        <div class="slider__header container-xl">
            @if (!empty($displayOptions['title']))
                <h2 class="slider__title h1">{{ $displayOptions['title'] }}</h2>
            @endif

            @if (!empty($displayOptions['subtitle']))
                <h3 class="slider__subtitle text-muted lead">{{ $displayOptions['subtitle'] }}</h3>
            @endif

            @if (!empty($displayOptions['description']))
                <div class="slider__description text-muted">
                    @if (!empty($displayOptions['description']) && is_array($displayOptions['description']))
                        @foreach ($displayOptions['description'] as $description)
                            <p>{{ is_array($description) ? $description['text'] ?? '' : $description }}</p>
                        @endforeach
                    @else
                        <p>{{ $displayOptions['description'] }}</p>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- Navigation Controls --}}
    @if (!empty($displayOptions['slides']) && count($displayOptions['slides']) > 1)
        <div class="slider__controls container-xl">
            <div class="slider__caption-wrap">
                <div class="slider__title-slider">
                    <div class="slider__title-slider-inner">
                        @foreach ($displayOptions['slides'] as $index => $slide)
                            <div class="slider__title-slide @if ($index === 0) active @endif"
                                data-slide-index="{{ $index }}">
                                @if (!empty($slide['title']))
                                    <h4 class="slider__slide-title">{{ $slide['title'] }}</h4>
                                @endif
                                @if (!empty($slide['subtitle']))
                                    <p class="slider__slide-subtitle text-muted">{{ $slide['subtitle'] }}</p>
                                @endif
                                @if (!empty($slide['link']))
                                    <a href="{{ $slide['link'] }}" class="slider__slide-btn btn btn-primary">
                                        Learn More
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="slider__nav-buttons">
                <button type="button" class="swiper-button-prev border-0 bg-transparent p-0"
                    data-slider-id="{{ $sliderId }}" aria-label="Previous slide">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="swiper-button-next border-0 bg-transparent p-0"
                    data-slider-id="{{ $sliderId }}" aria-label="Next slide">
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- Swiper Carousel --}}
    @if (!empty($displayOptions['slides']))
        <div class="container-xl swiper {{ $sliderId }}" data-slider-id="{{ $sliderId }}">
            <div class="swiper-wrapper">
                @foreach ($displayOptions['slides'] as $index => $slide)
                    <div class="swiper-slide">
                        <div class="slider__slide-content">
                            @if (($slide['type'] ?? 'image') === 'video')
                                <div class="slider__slide-video">
                                    <video class="lazy" data-src="{{ asset_url($slide['url']) }}" controls
                                        preload="none">
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
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="container-xl py-3">
            <div class="alert alert-secondary mb-0">No slides available</div>
        </div>
    @endif
</section>
