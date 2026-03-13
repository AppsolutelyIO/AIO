<section class="video video--{{ $viewStyle ?? 'default' }} position-relative overflow-hidden w-100">
    <!-- Background Video -->
    <div class="video__background position-absolute top-0 start-0 w-100 h-100" style="z-index: 1;">
        <video class="lazy w-100 h-100 object-fit-cover" @if ($displayOptions['autoplay']) autoplay @endif
            @if ($displayOptions['loop']) loop @endif @if ($displayOptions['muted']) muted @endif
            @if (!$displayOptions['controls']) style="pointer-events: none;" @else controls @endif
            @if ($displayOptions['poster_image']) poster="{{ asset_url($displayOptions['poster_image']) }}" @endif
            playsinline preload="none">
            @if ($displayOptions['video_url'])
                <source data-src="{{ asset_url($displayOptions['video_url']) }}"
                    type="video/{{ $displayOptions['video_format'] }}">
            @endif

            @if (!empty($displayOptions['fallback_videos']))
                @foreach ($displayOptions['fallback_videos'] as $fallback)
                    <source data-src="{{ asset_url($fallback['url']) }}" type="video/{{ $fallback['format'] }}">
                @endforeach
            @endif

            <!-- Fallback message for browsers that don't support video -->
            @if (!empty($displayOptions['fallback_text']))
                <div class="d-flex align-items-center justify-content-center h-100 bg-secondary text-white">
                    <p class="mb-0">{{ $displayOptions['fallback_text'] }}</p>
                </div>
            @endif
        </video>
    </div>

    <!-- Mobile Fallback Image -->
    @if ($displayOptions['mobile_fallback_image'])
        <div class="video__mobile-fallback d-block d-md-none position-absolute top-0 start-0 w-100 h-100"
            style="z-index: 1;">
            <img class="lazy w-100 h-100 object-fit-cover"
                data-src="{{ asset_url($displayOptions['mobile_fallback_image']) }}" alt="Video fallback">
        </div>
    @endif

    <!-- Overlay -->
    @if ($displayOptions['overlay_opacity'] > 0)
        <div class="video__overlay position-absolute top-0 start-0 w-100 h-100 bg-dark"
            style="z-index: 2; opacity: {{ $displayOptions['overlay_opacity'] }};"></div>
    @endif

    <!-- Content Overlay -->
    <div class="video__content position-relative d-flex align-items-center justify-content-center h-100">
        <div class="container-xl px-4">
            <div
                class="row justify-content-{{ $displayOptions['text_position'] === 'left' ? 'start' : ($displayOptions['text_position'] === 'right' ? 'end' : 'center') }}">
                <div
                    class="col-12 col-lg-8 col-xl-6 text-{{ $displayOptions['text_position'] === 'center' ? 'center' : $displayOptions['text_position'] }}">
                    @if ($displayOptions['title'])
                        <h1 class="display-3 fw-bold mb-4 lh-1" style="color: {{ $displayOptions['text_color'] }};">
                            {{ $displayOptions['title'] }}
                        </h1>
                    @endif

                    @if ($displayOptions['subtitle'])
                        <h2 class="display-7 mb-4 fw-light"
                            style="color: {{ $displayOptions['text_color'] }}; opacity: 0.9;">
                            {{ $displayOptions['subtitle'] }}
                        </h2>
                    @endif

                    @if ($displayOptions['description'])
                        <p class="lead mb-5 fs-4" style="color: {{ $displayOptions['text_color'] }}; opacity: 0.8;">
                            {{ $displayOptions['description'] }}
                        </p>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- CTA Button - Bottom Center -->
    @if ($displayOptions['cta_text'] && $displayOptions['cta_link'])
        <div class="video__cta position-absolute start-50 translate-middle-x"
            style="z-index: 4; bottom: 3rem;">
            <a href="{{ $displayOptions['cta_link'] }}" class="btn btn-light btn-lg px-3 py-2 fs-6 fw-bold rounded-3">
                {{ $displayOptions['cta_text'] }}
            </a>
        </div>
    @endif
    @include('components.overseas-model-notice', [
        'show' => !empty($displayOptions['flag_overseas']),
    ])
</section>
