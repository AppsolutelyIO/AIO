<section class="features-fullscreen">
    @if (isset($displayOptions['title']))
        <div class="features-fullscreen__header text-center py-4">
            <div class="container-xl">
                <h2 class="features-fullscreen__title h1">{{ $displayOptions['title'] }}</h2>
                @if (isset($displayOptions['subtitle']) && $displayOptions['subtitle'])
                    <p class="features-fullscreen__subtitle text-muted lead">{{ $displayOptions['subtitle'] }}</p>
                @endif
                @if (!empty($displayOptions['descriptions']) && is_array($displayOptions['descriptions']))
                    <div class="features-fullscreen__descriptions">
                        @foreach ($displayOptions['descriptions'] as $description)
                            <p class="features-fullscreen__description text-muted">
                                {{ is_array($description) ? $description['text'] ?? '' : $description }}</p>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if (isset($displayOptions['features']) && count($displayOptions['features']) > 0)
        <div class="features-fullscreen__content">
            <div class="features-fullscreen__grid row g-0">
                @foreach ($displayOptions['features'] as $index => $feature)
                    <div class="features-fullscreen__item col-12 col-md-6 col-lg">
                        @if ($feature['type'] === 'image' && $feature['url'])
                            <div class="features-fullscreen__image-wrapper position-relative overflow-hidden">
                                @if ($feature['link'])
                                    <a href="{{ $feature['link'] }}" class="features-fullscreen__image-link d-block">
                                        <img class="lazy features-fullscreen__image w-100"
                                            data-src="{{ asset_url($feature['url']) }}"
                                            alt="{{ $feature['image_alt'] ?? '' }}" src=""
                                            style="height: 60vh; object-fit: cover;">
                                    </a>
                                @else
                                    <img class="lazy features-fullscreen__image w-100"
                                        data-src="{{ asset_url($feature['url']) }}"
                                        alt="{{ $feature['image_alt'] ?? '' }}" src=""
                                        style="height: 60vh; object-fit: cover;">
                                @endif

                                @if ($feature['title'] || $feature['subtitle'])
                                    <div class="features-fullscreen__overlay position-absolute bottom-0 start-0 w-100 p-4"
                                        style="background: linear-gradient(transparent, rgba(0,0,0,.7));">
                                        @if ($feature['title'])
                                            <h3 class="features-fullscreen__item-title text-white mb-1">
                                                {{ $feature['title'] }}</h3>
                                        @endif
                                        @if ($feature['subtitle'])
                                            <p class="features-fullscreen__item-subtitle text-white opacity-75 mb-0">
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
    @endif
</section>
