<section class="document py-5">
    <div class="document__container container-xl">
        <div class="document__row row justify-content-center">
            <div class="document__content col-lg-8">
                <!-- Title -->
                @if ($displayOptions['title'] ?? false)
                    <h1 class="document__title fw-bold mb-3">
                        {{ $displayOptions['title'] }}
                    </h1>
                @endif

                <!-- Subtitle -->
                @if ($displayOptions['subtitle'] ?? false)
                    <p class="document__subtitle lead text-muted mb-4">
                        {{ $displayOptions['subtitle'] }}
                    </p>
                @endif

                <!-- Meta Information -->
                @if (
                    ($displayOptions['show_meta'] ?? true) &&
                        (($displayOptions['published_date'] ?? false) || ($displayOptions['author'] ?? false)))
                    <div class="document__meta text-muted mb-4 pb-3 border-bottom">
                        <small>
                            @if ($displayOptions['author'] ?? false)
                                <i class="fas fa-user me-2" aria-hidden="true"></i>
                                <span class="me-3">By {{ $displayOptions['author'] }}</span>
                            @endif
                            @if ($displayOptions['published_date'] ?? false)
                                <i class="fas fa-calendar-alt me-2" aria-hidden="true"></i>
                                <time datetime="{{ $displayOptions['published_date'] }}">
                                    Published:
                                    {{ \Carbon\Carbon::parse($displayOptions['published_date'])->format('F j, Y') }}
                                </time>
                            @endif
                        </small>
                    </div>
                @endif

                <!-- Content -->
                @if ($displayOptions['content'] ?? false)
                    <div class="document__body content-body markdown">
                        {!! md2html($displayOptions['content']) !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
