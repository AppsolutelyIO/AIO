<section class="specs py-5">
    <div class="specs__container container-xl">
        <!-- Section Header -->
        @if ($displayOptions['title'] || $displayOptions['subtitle'] || $displayOptions['description'])
            <div class="specs__header text-center mb-5">
                @if ($displayOptions['title'])
                    <h2 class="specs__title h1 mb-3">
                        {{ $displayOptions['title'] }}
                    </h2>
                @endif

                @if ($displayOptions['subtitle'])
                    <h3 class="specs__subtitle h4 text-muted mb-4">
                        {{ $displayOptions['subtitle'] }}
                    </h3>
                @endif

                @if ($displayOptions['description'])
                    <p class="specs__description lead text-muted">
                        {{ $displayOptions['description'] }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Specifications Content -->
        @if (!empty($displayOptions['specifications']))
            @if ($displayOptions['layout'] === 'grid')
                <!-- Grid Layout -->
                <div class="specs__grid row row-deck row-cards g-4">
                    @foreach ($displayOptions['specifications'] as $spec)
                        <div class="specs__grid-item col-md-{{ 12 / $displayOptions['columns'] }}">
                            <div class="specs__item card h-100">
                                <div class="card-body">
                                    <h5 class="specs__item-label card-title mb-2">
                                        @if ($spec['icon'] ?? false)
                                            <i class="{{ $spec['icon'] }} me-2" aria-hidden="true"></i>
                                        @endif
                                        {{ $spec['label'] }}
                                    </h5>
                                    <div class="specs__item-value h3 mb-0">
                                        {{ $spec['value'] }}
                                        @if ($spec['unit'] ?? false)
                                            <span
                                                class="specs__item-value-unit text-muted fs-5">{{ $spec['unit'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @elseif($displayOptions['layout'] === 'list')
                <!-- Two-Column Layout -->
                <div class="row justify-content-center">
                    <div class="col">
                        <div class="row g-4">
                            <!-- Dimensions Column -->
                            <div class="col-xl-6">
                                <div class="specs__column card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Dimensions</h4>
                                    </div>
                                    @if (!empty($displayOptions['dimensions']))
                                        <div class="list-group list-group-flush">
                                            @foreach ($displayOptions['dimensions'] as $spec)
                                                <div class="list-group-item d-flex justify-content-between">
                                                    @if ($spec['label'])
                                                        <span class="specs__label">{{ $spec['label'] }}</span>
                                                    @endif
                                                    <span class="specs__value fw-semibold">
                                                        {{ $spec['value'] }}
                                                        @if ($spec['unit'] ?? false)
                                                            <span
                                                                class="specs__value--unit text-muted">{{ $spec['unit'] }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Drive Column -->
                            <div class="col-xl-6">
                                <div class="specs__column card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Drive</h4>
                                    </div>
                                    @if (!empty($displayOptions['drives']))
                                        <div class="list-group list-group-flush">
                                            @foreach ($displayOptions['drives'] as $spec)
                                                <div class="list-group-item d-flex justify-content-between">
                                                    @if ($spec['label'])
                                                        <span class="specs__label">{{ $spec['label'] }}</span>
                                                    @endif
                                                    <span class="specs__value fw-semibold">
                                                        {{ $spec['value'] }}
                                                        @if ($spec['unit'] ?? false)
                                                            <span
                                                                class="specs__value--unit text-muted">{{ $spec['unit'] }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Table Layout -->
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="table-responsive">
                                <table class="specs__table table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Specification</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($displayOptions['specifications'] as $spec)
                                            <tr>
                                                <td>
                                                    @if ($spec['icon'] ?? false)
                                                        <i class="{{ $spec['icon'] }} me-2" aria-hidden="true"></i>
                                                    @endif
                                                    {{ $spec['label'] }}
                                                </td>
                                                <td>
                                                    {{ $spec['value'] }}
                                                    @if ($spec['unit'] ?? false)
                                                        <span class="text-muted">{{ $spec['unit'] }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Download Button -->
        @if ($displayOptions['download_url'] ?? false)
            <div class="specs__download text-center mt-5">
                <a href="{{ asset_url($displayOptions['download_url']) }}"
                    class="specs__download-btn btn btn-primary btn-lg"
                    download="{{ $displayOptions['download_filename'] ?? 'specs' }}" target="_blank"
                    rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                        <path d="M7 11l5 5l5 -5" />
                        <path d="M12 4l0 12" />
                    </svg>
                    {{ !empty($displayOptions['download_label']) ? $displayOptions['download_label'] : 'Download Brochure' }}
                </a>
            </div>
        @endif
    </div>
</section>
