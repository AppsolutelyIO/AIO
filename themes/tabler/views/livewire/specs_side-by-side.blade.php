<section class="specs specs--side-by-side py-5">
    <div class="specs__container container-xl">
        {{-- Section Header --}}
        @if ($displayOptions['title'] || $displayOptions['subtitle'] || $displayOptions['description'])
            <div class="specs__header text-center mb-5">
                @if ($displayOptions['title'])
                    <h2 class="specs__title h1 mb-3">{{ $displayOptions['title'] }}</h2>
                @endif
                @if ($displayOptions['subtitle'])
                    <h3 class="specs__subtitle h4 text-muted mb-4">{{ $displayOptions['subtitle'] }}</h3>
                @endif
                @if ($displayOptions['description'])
                    <p class="specs__description lead text-muted">{{ $displayOptions['description'] }}</p>
                @endif
            </div>
        @endif

        <div class="row g-4">
            {{-- Dimensions Card --}}
            @if (!empty($displayOptions['dimensions']))
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary-lt">
                            <h4 class="card-title mb-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler me-2" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" aria-hidden="true">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M17 3l4 4l-14 14l-4 -4z" />
                                    <path d="M16 7l-1.5 -1.5" />
                                    <path d="M13 10l-1.5 -1.5" />
                                    <path d="M10 13l-1.5 -1.5" />
                                    <path d="M7 16l-1.5 -1.5" />
                                </svg>
                                Dimensions
                            </h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped">
                                <tbody>
                                    @foreach ($displayOptions['dimensions'] as $spec)
                                        <tr>
                                            <td class="w-50 text-muted">{{ $spec['label'] }}</td>
                                            <td class="w-50 fw-semibold">
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
            @endif

            {{-- Drive / Performance Card --}}
            @if (!empty($displayOptions['drives']))
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-green-lt">
                            <h4 class="card-title mb-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler me-2" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" aria-hidden="true">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11" />
                                </svg>
                                Performance
                            </h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped">
                                <tbody>
                                    @foreach ($displayOptions['drives'] as $spec)
                                        <tr>
                                            <td class="w-50 text-muted">{{ $spec['label'] }}</td>
                                            <td class="w-50 fw-semibold">
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
            @endif
        </div>

        {{-- Additional Specifications --}}
        @if (!empty($displayOptions['specifications']))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-azure-lt">
                            <h4 class="card-title mb-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler me-2" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" aria-hidden="true">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                    <path
                                        d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                    <path d="M9 12l.01 0" />
                                    <path d="M13 12l2 0" />
                                    <path d="M9 16l.01 0" />
                                    <path d="M13 16l2 0" />
                                </svg>
                                Specifications
                            </h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-striped">
                                <thead>
                                    <tr>
                                        <th class="w-50">Feature</th>
                                        <th class="w-50">Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($displayOptions['specifications'] as $spec)
                                        <tr>
                                            <td class="text-muted">
                                                @if ($spec['icon'] ?? false)
                                                    <i class="{{ $spec['icon'] }} me-2" aria-hidden="true"></i>
                                                @endif
                                                {{ $spec['label'] }}
                                            </td>
                                            <td class="fw-semibold">
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

        {{-- Download Button --}}
        @if ($displayOptions['download_url'] ?? false)
            <div class="specs__download text-center mt-5">
                <a href="{{ asset_url($displayOptions['download_url']) }}"
                    class="btn btn-primary btn-lg"
                    download="{{ $displayOptions['download_filename'] ?? 'specs' }}" target="_blank"
                    rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        aria-hidden="true">
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
