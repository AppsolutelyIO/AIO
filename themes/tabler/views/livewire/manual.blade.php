<section class="manual py-5">
    <div class="manual__container container-xl">
        @if (!empty($displayOptions['title']) || !empty($displayOptions['subtitle']))
            <div class="manual__header text-center mb-5">
                @if (!empty($displayOptions['title']))
                    <h2 class="manual__title h1 mb-2">{{ $displayOptions['title'] }}</h2>
                @endif
                @if (!empty($displayOptions['subtitle']))
                    <p class="manual__subtitle text-muted lead">{{ $displayOptions['subtitle'] }}</p>
                @endif
            </div>
        @endif

        @php
            $items = array_values(
                array_filter(
                    $displayOptions['items'] ?? [],
                    fn($item) => !empty($item['title']) || !empty($item['image_src']) || !empty($item['links']),
                ),
            );
        @endphp

        @if (!empty($items))
            <div class="manual__grid row row-deck row-cards g-4">
                @foreach ($items as $item)
                    <div class="manual__item col-md-6 col-lg-4">
                        <div class="manual__card card h-100">
                            @if (!empty($item['image_src']))
                                <div class="manual__card-image-wrap ratio ratio-4x3">
                                    <img class="manual__card-img lazy card-img-top object-fit-cover"
                                        data-src="{{ asset_url($item['image_src']) }}" alt="{{ $item['title'] ?? '' }}"
                                        src="">
                                </div>
                            @endif
                            <div class="manual__card-body card-body d-flex flex-column">
                                @if (!empty($item['title']))
                                    <h3 class="manual__card-title card-title h5">{{ $item['title'] }}</h3>
                                @endif
                                @if (!empty($item['links']) && is_array($item['links']))
                                    <div class="manual__card-links pt-3 d-flex flex-column gap-2 mt-auto">
                                        @foreach ($item['links'] as $link)
                                            @if (!empty($link['url']))
                                                <a href="{{ asset_url($link['url']) }}"
                                                    class="manual__link btn btn-outline-primary btn-sm"
                                                    target="_blank" rel="noopener noreferrer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1"
                                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                        <path
                                                            d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                        <path d="M9 9l1 0" />
                                                        <path d="M9 13l6 0" />
                                                        <path d="M9 17l6 0" />
                                                    </svg>
                                                    {{ $link['label'] ?? 'Download PDF' }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
