<section class="features-equal-columns">
    @if (isset($displayOptions['title']))
        <div class="features-equal-columns__header text-center mb-5">
            <div class="container-xl">
                <h2 class="features-equal-columns__title h1">{{ $displayOptions['title'] }}</h2>
                @if (isset($displayOptions['subtitle']) && $displayOptions['subtitle'])
                    <p class="features-equal-columns__subtitle text-muted lead">{{ $displayOptions['subtitle'] }}</p>
                @endif
                @if (!empty($displayOptions['descriptions']) && is_array($displayOptions['descriptions']))
                    <div class="features-equal-columns__descriptions">
                        @foreach ($displayOptions['descriptions'] as $description)
                            <p class="features-equal-columns__description text-muted">
                                {{ is_array($description) ? $description['text'] ?? '' : $description }}</p>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if (isset($displayOptions['features']) && count($displayOptions['features']) >= 3)
        <div class="features-equal-columns__content">
            <div class="container-xl px-0">
                <div class="features-equal-columns__grid row g-0">
                    @foreach ($displayOptions['features'] as $index => $feature)
                        <div class="features-equal-columns__item col">
                            @if ($feature['type'] === 'image' && $feature['url'])
                                <div class="features-equal-columns__image-wrapper position-relative overflow-hidden">
                                    @if ($feature['link'])
                                        <a href="{{ $feature['link'] }}" class="features-equal-columns__image-link d-block">
                                            <img class="lazy features-equal-columns__image w-100"
                                                data-src="{{ asset_url($feature['url']) }}"
                                                alt="{{ $feature['image_alt'] ?? '' }}" src=""
                                                style="height: 350px; object-fit: cover;">
                                        </a>
                                    @else
                                        <img class="lazy features-equal-columns__image w-100"
                                            data-src="{{ asset_url($feature['url']) }}"
                                            alt="{{ $feature['image_alt'] ?? '' }}" src=""
                                            style="height: 350px; object-fit: cover;">
                                    @endif

                                    @if ($feature['title'] || $feature['subtitle'])
                                        <div class="features-equal-columns__overlay position-absolute bottom-0 start-0 w-100 p-3"
                                            style="background: linear-gradient(transparent, rgba(0,0,0,.7));">
                                            @if ($feature['title'])
                                                <h3 class="features-equal-columns__item-title text-white mb-1 h5">
                                                    {{ $feature['title'] }}</h3>
                                            @endif
                                            @if ($feature['subtitle'])
                                                <p class="features-equal-columns__item-subtitle text-white opacity-75 mb-0 small">
                                                    {{ $feature['subtitle'] }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>
