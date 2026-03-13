<section class="faq faq--card-grid py-5">
    <div class="faq__container container-xl">
        @if (($displayOptions['title'] ?? false) || ($displayOptions['subtitle'] ?? false))
            <div class="faq__header text-center mb-5">
                @if ($displayOptions['title'] ?? false)
                    <h2 class="faq__title h1 mb-3">{{ $displayOptions['title'] }}</h2>
                @endif
                @if ($displayOptions['subtitle'] ?? false)
                    <p class="faq__subtitle lead text-muted">{{ $displayOptions['subtitle'] }}</p>
                @endif
            </div>
        @endif

        @if (!empty($displayOptions['items']) && is_array($displayOptions['items']))
            <div class="faq__grid row row-deck row-cards g-4">
                @foreach ($displayOptions['items'] as $index => $item)
                    @if (!empty($item['question']))
                        <div class="faq__grid-item col-md-6">
                            <div class="faq__card card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <span
                                            class="faq__card-number avatar avatar-sm bg-primary-lt text-primary me-3 flex-shrink-0">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <h3 class="faq__card-question card-title mb-2">
                                                {{ $item['question'] }}
                                            </h3>
                                            <div class="faq__card-answer text-muted">
                                                {!! md2html((string) ($item['answer'] ?? '')) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</section>
