<section class="features-alternating-rows container-xl my-5">
    @if (isset($displayOptions['title']) || isset($displayOptions['subtitle']) || isset($displayOptions['description']))
        <div class="features-alternating-rows__header text-center mb-5">
            @if (!empty($displayOptions['title']))
                <h2 class="features-alternating-rows__title h1 mb-3">{{ $displayOptions['title'] }}</h2>
            @endif
            @if (!empty($displayOptions['subtitle']))
                <p class="features-alternating-rows__subtitle text-muted lead mb-2">{{ $displayOptions['subtitle'] }}</p>
            @endif
            @if (!empty($displayOptions['descriptions']) && is_array($displayOptions['descriptions']))
                @foreach ($displayOptions['descriptions'] as $description)
                    <p class="features-alternating-rows__description text-muted">
                        {{ is_array($description) ? $description['text'] ?? '' : $description }}</p>
                @endforeach
            @endif
        </div>
    @endif

    @php($features = $displayOptions['features'] ?? [])
    @if (is_array($features) && count($features))
        @foreach ($features as $index => $feature)
            @php($isImageLeft = $index % 2 === 0)
            <article class="features-alternating-rows__row row align-items-center g-5 mb-5">
                <div
                    class="features-alternating-rows__image-wrap col-md-6 {{ $isImageLeft ? 'order-1 order-md-1' : 'order-1 order-md-2' }}">
                    @if (($feature['type'] ?? 'image') === 'image' && !empty($feature['url']))
                        <div class="card overflow-hidden shadow-sm">
                            <div class="ratio ratio-16x9">
                                @if (!empty($feature['link']))
                                    <a href="{{ $feature['link'] }}" class="d-block">
                                        <img class="lazy w-100 h-100 object-fit-cover"
                                            data-src="{{ asset_url($feature['url']) }}"
                                            alt="{{ $feature['image_alt'] ?? ($feature['title'] ?? '') }}">
                                    </a>
                                @else
                                    <img class="lazy w-100 h-100 object-fit-cover"
                                        data-src="{{ asset_url($feature['url']) }}"
                                        alt="{{ $feature['image_alt'] ?? ($feature['title'] ?? '') }}">
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div
                    class="features-alternating-rows__content col-md-6 {{ $isImageLeft ? 'order-2 order-md-2' : 'order-2 order-md-1' }}">
                    <div class="px-md-4">
                        @if (!empty($feature['eyebrow']))
                            <div class="features-alternating-rows__eyebrow text-uppercase small text-muted mb-2 fw-semibold">
                                {{ $feature['eyebrow'] }}</div>
                        @endif

                        @if (!empty($feature['title']))
                            <h3 class="features-alternating-rows__item-title h2 mb-3">{{ $feature['title'] }}</h3>
                        @endif

                        @if (!empty($feature['subtitle']))
                            <p class="features-alternating-rows__item-subtitle text-muted lead mb-3">{{ $feature['subtitle'] }}</p>
                        @endif

                        @if (!empty($feature['description']))
                            <div class="features-alternating-rows__item-description text-muted mb-4">{!! $feature['description'] !!}</div>
                        @endif

                        @if (!empty($feature['button_text']) && !empty($feature['link']))
                            <a href="{{ $feature['link'] }}" class="features-alternating-rows__button btn btn-primary">
                                {{ $feature['button_text'] }}
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    @endif
</section>
