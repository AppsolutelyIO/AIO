<section id="mediaSliderCarousel-{{ $this->getId() }}"
    class="slider slider--fullscreen carousel slide section-full" data-bs-ride="carousel">
    <!-- Carousel Section (80vh) -->
    <div class="slider__carousel">
        <div class="carousel-inner">
            @if (!empty($displayOptions['slides']))
                @foreach ($displayOptions['slides'] as $index => $slide)
                    <div class="carousel-item @if ($index === 0) active @endif">
                        @if (($slide['type'] ?? 'image') === 'video')
                            <video class="lazy d-block w-100" data-src="{{ asset_url($slide['url']) }}" controls
                                preload="none">
                                Your browser does not support the video tag.
                            </video>
                        @else
                            <img class="lazy d-block w-100" data-src="{{ asset_url($slide['url']) }}"
                                alt="{{ $slide['image_alt'] ?? '' }}">
                        @endif
                        <div class="slider__caption carousel-caption d-none d-md-block">
                            @if (!empty($slide['model']) && !empty($slide['title']))
                                <img class="lazy mb-4" data-src="{{ asset_url($slide['model']) }}"
                                    alt="{{ $slide['title'] }}">
                            @elseif(empty($slide['model']) && !empty($slide['title']))
                                <h2 class="display-6 fw-bold mb-3">{{ $slide['title'] }}</h2>
                            @endif
                            @if (!empty($slide['subtitle']))
                                <p class="lead">{{ $slide['subtitle'] }}</p>
                            @endif
                            @if (!empty($slide['link']))
                                <a href="{{ $slide['link'] }}" class="slider__btn btn btn-primary">
                                    Learn More
                                </a>
                            @endif
                        </div>

                        <!-- Overseas model notice -->
                        @include('components.overseas-model-notice', [
                            'show' => !empty($slide['flag_overseas']),
                        ])
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Previous/Next Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#mediaSliderCarousel-{{ $this->getId() }}"
            data-bs-slide="prev" aria-label="Previous">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mediaSliderCarousel-{{ $this->getId() }}"
            data-bs-slide="next" aria-label="Next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>

        <div class="carousel-indicators">
            @if (!empty($displayOptions['slides']))
                @foreach ($displayOptions['slides'] as $index => $slide)
                    <button type="button" data-bs-target="#mediaSliderCarousel-{{ $this->getId() }}"
                        data-bs-slide-to="{{ $index }}"
                        @if ($index === 0) class="active" aria-current="true" @endif
                        aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Items Section (20vh) -->
    @if (!empty($displayOptions['items']) || !empty($displayOptions['vehicles']))
        <div class="slider__items">
            <div class="container-xl">
                <div class="row g-3 g-md-4 justify-content-center">
                    @foreach ($displayOptions['items'] ?? ($displayOptions['vehicles'] ?? []) as $item)
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="slider__item card card-link card-link-pop">
                                <div class="slider__item-image-wrap">
                                    <img class="slider__item-image lazy card-img-top"
                                        data-src="{{ asset_url($item['image'] ?? '') }}"
                                        alt="{{ $item['name'] ?? '' }}">
                                </div>
                                <div class="slider__item-content card-body text-center">
                                    <h3 class="slider__item-name card-title h5">{{ $item['name'] ?? '' }}</h3>
                                    <div class="slider__item-actions d-flex gap-2 justify-content-center">
                                        @if (!empty($item['learn_more_link']))
                                            <a href="{{ $item['learn_more_link'] }}"
                                                class="slider__item-btn btn btn-outline-primary btn-sm">
                                                Learn More
                                            </a>
                                        @endif
                                        @if (!empty($item['test_drive_link']))
                                            <a href="{{ $item['test_drive_link'] }}"
                                                class="slider__item-btn btn btn-outline-primary btn-sm">
                                                Test Drive
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
