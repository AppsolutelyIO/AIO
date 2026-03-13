<section class="features container-xl py-5">
    @if (isset($displayOptions['title']))
        <div class="features__header text-center mb-5">
            <h2 class="features__title h1">{{ $displayOptions['title'] }}</h2>
            @if (isset($displayOptions['subtitle']) && $displayOptions['subtitle'])
                <p class="features__subtitle text-muted lead">{{ $displayOptions['subtitle'] }}</p>
            @endif
            @if (!empty($displayOptions['descriptions']) && is_array($displayOptions['descriptions']))
                <div class="features__descriptions">
                    @foreach ($displayOptions['descriptions'] as $description)
                        <p class="features__description text-muted">
                            {{ is_array($description) ? $description['text'] ?? '' : $description }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if (isset($displayOptions['features']) && count($displayOptions['features']) >= 3)
        <div class="features__content">
            <div class="row g-4">
                <!-- Main Feature (Left Side) -->
                <div class="col-lg-8">
                    @php $mainFeature = $displayOptions['features'][0]; @endphp
                    <div class="features__main-item card card-link card-link-pop overflow-hidden h-100">
                        @if ($mainFeature['type'] === 'image' && $mainFeature['url'])
                            <div class="features__image-wrapper position-relative">
                                @if ($mainFeature['link'])
                                    <a href="{{ $mainFeature['link'] }}" class="features__image-link d-block">
                                        <img class="lazy features__image features__image--main w-100"
                                            data-src="{{ asset_url($mainFeature['url']) }}"
                                            alt="{{ $mainFeature['image_alt'] ?? '' }}" src=""
                                            style="height: 400px; object-fit: cover;">
                                    </a>
                                @else
                                    <img class="lazy features__image features__image--main w-100"
                                        data-src="{{ asset_url($mainFeature['url']) }}"
                                        alt="{{ $mainFeature['image_alt'] ?? '' }}" src=""
                                        style="height: 400px; object-fit: cover;">
                                @endif

                                @if ($mainFeature['title'])
                                    <div class="features__overlay card-img-overlay d-flex flex-column justify-content-end"
                                        style="background: linear-gradient(transparent 40%, rgba(0,0,0,.7));">
                                        <h3 class="features__item-title text-white mb-1">{{ $mainFeature['title'] }}</h3>
                                        @if ($mainFeature['subtitle'])
                                            <p class="features__item-subtitle text-white opacity-75 mb-0">
                                                {{ $mainFeature['subtitle'] }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Secondary Features (Right Side) -->
                <div class="col-lg-4">
                    <div class="features__secondary-items d-flex flex-column gap-4 h-100">
                        @for ($i = 1; $i < 3; $i++)
                            @if (isset($displayOptions['features'][$i]))
                                @php $feature = $displayOptions['features'][$i]; @endphp
                                <div class="features__secondary-item card card-link card-link-pop overflow-hidden flex-fill">
                                    @if ($feature['type'] === 'image' && $feature['url'])
                                        <div class="features__image-wrapper position-relative h-100">
                                            @if ($feature['link'])
                                                <a href="{{ $feature['link'] }}" class="features__image-link d-block h-100">
                                                    <img class="lazy features__image features__image--secondary w-100 h-100"
                                                        data-src="{{ asset_url($feature['url']) }}"
                                                        alt="{{ $feature['image_alt'] ?? '' }}" src=""
                                                        style="object-fit: cover; min-height: 180px;">
                                                </a>
                                            @else
                                                <img class="lazy features__image features__image--secondary w-100 h-100"
                                                    data-src="{{ asset_url($feature['url']) }}"
                                                    alt="{{ $feature['image_alt'] ?? '' }}" src=""
                                                    style="object-fit: cover; min-height: 180px;">
                                            @endif

                                            @if ($feature['title'] || $feature['subtitle'])
                                                <div class="features__overlay card-img-overlay d-flex flex-column justify-content-end"
                                                    style="background: linear-gradient(transparent 30%, rgba(0,0,0,.7));">
                                                    @if ($feature['title'])
                                                        <h4 class="features__item-title text-white mb-1">
                                                            {{ $feature['title'] }}</h4>
                                                    @endif
                                                    @if ($feature['subtitle'])
                                                        <p class="features__item-subtitle text-white opacity-75 mb-0 small">
                                                            {{ $feature['subtitle'] }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
