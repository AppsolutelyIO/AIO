<section class="faq py-5">
    <div class="faq__container container-xl">
        <div class="faq__row row justify-content-center">
            <div class="faq__content col-lg-8">
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
                    <div class="faq__accordion accordion" id="faqAccordion">
                        @foreach ($displayOptions['items'] as $index => $item)
                            @if (!empty($item['question']))
                                <div class="faq__accordion-item accordion-item">
                                    <h3 class="faq__accordion-header accordion-header">
                                        <button
                                            class="faq__accordion-button accordion-button {{ $index === 0 ? '' : 'collapsed' }}"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#faq{{ $index }}"
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                            aria-controls="faq{{ $index }}">
                                            {{ $item['question'] }}
                                        </button>
                                    </h3>
                                    <div id="faq{{ $index }}"
                                        class="faq__accordion-collapse accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                        data-bs-parent="#faqAccordion">
                                        <div class="faq__accordion-body accordion-body">
                                            {!! md2html((string) ($item['answer'] ?? '')) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
