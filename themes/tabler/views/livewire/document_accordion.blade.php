<section class="document-accordion py-5">
    <div class="document-accordion__wrapper container-xl">
        <div class="document-accordion__row row justify-content-center">
            <div class="document-accordion__col col-lg-8">
                <div class="document-accordion__container card">
                    <!-- Collapsible Header -->
                    <button type="button"
                        class="document-accordion__header card-header w-100 text-start border-0 bg-transparent"
                        data-bs-toggle="collapse" data-bs-target="#textDocumentCollapsible{{ $blockId ?? 'default' }}"
                        aria-expanded="false" aria-controls="textDocumentCollapsible{{ $blockId ?? 'default' }}">

                        <div class="document-accordion__header-content d-flex justify-content-between align-items-center">
                            <div>
                                <!-- Title -->
                                @if ($displayOptions['title'] ?? false)
                                    <h2 class="document-accordion__title h4 fw-bold mb-0">
                                        {{ $displayOptions['title'] }}
                                    </h2>
                                @endif

                                <!-- Subtitle -->
                                @if ($displayOptions['subtitle'] ?? false)
                                    <p class="document-accordion__subtitle text-muted mb-0 small mt-1">
                                        {{ $displayOptions['subtitle'] }}
                                    </p>
                                @endif
                            </div>

                            <!-- Arrow Icon -->
                            <div class="document-accordion__arrow ms-3">
                                <i class="bi bi-chevron-down text-primary" aria-hidden="true"></i>
                            </div>
                        </div>
                    </button>

                    <!-- Collapsible Content -->
                    <div class="collapse document-accordion__content"
                        id="textDocumentCollapsible{{ $blockId ?? 'default' }}">

                        <div class="document-accordion__body card-body">
                            <!-- Meta Information -->
                            @if (
                                ($displayOptions['show_meta'] ?? true) &&
                                    (($displayOptions['published_date'] ?? false) || ($displayOptions['author'] ?? false)))
                                <div class="document-accordion__meta text-muted mb-3 pb-3 border-bottom">
                                    <small>
                                        @if ($displayOptions['author'] ?? false)
                                            <i class="bi bi-person me-2" aria-hidden="true"></i>
                                            <span class="me-3">By {{ $displayOptions['author'] }}</span>
                                        @endif
                                        @if ($displayOptions['published_date'] ?? false)
                                            <i class="bi bi-calendar3 me-2" aria-hidden="true"></i>
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
                                <div class="document-accordion__content-body markdown">
                                    {!! md2html($displayOptions['content']) !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
