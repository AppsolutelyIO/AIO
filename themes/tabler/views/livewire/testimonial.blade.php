<section class="block-testimonials py-5 bg-light">
    <div class="container-xl">

        @if ($displayOptions['title'] ?? null)
            <div class="text-center mb-5">
                <h2 class="h1">{{ $displayOptions['title'] }}</h2>
            </div>
        @endif

        <div class="row row-cards g-4">
            @forelse ($items ?? [] as $item)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                @if ($item['avatar'] ?? null)
                                    <span class="avatar avatar-md rounded me-3"
                                        style="background-image: url('{{ asset_url($item['avatar']) }}')"></span>
                                @else
                                    <span class="avatar avatar-md rounded me-3 bg-primary-lt text-primary">
                                        {{ strtoupper(substr($item['name'] ?? 'U', 0, 1)) }}
                                    </span>
                                @endif
                                <div>
                                    <div class="fw-semibold">{{ $item['name'] ?? '' }}</div>
                                    @if ($item['role'] ?? null)
                                        <div class="text-muted small">{{ $item['role'] }}</div>
                                    @endif
                                </div>
                            </div>
                            @if ($item['quote'] ?? null)
                                <p class="text-muted mb-0">&ldquo;{{ $item['quote'] }}&rdquo;</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                {{-- Placeholder testimonials --}}
                @foreach ([
                    ['name' => 'Alex Johnson', 'role' => 'CEO, Acme Corp', 'quote' => 'Absolutely transformed how we run our business. Highly recommended!'],
                    ['name' => 'Maria Garcia', 'role' => 'Product Manager', 'quote' => 'The interface is clean and the team was incredibly helpful throughout.'],
                    ['name' => 'Sam Lee', 'role' => 'Lead Developer', 'quote' => 'Best platform we have used. The developer experience is second to none.'],
                ] as $placeholder)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="avatar avatar-md rounded me-3 bg-primary-lt text-primary">
                                        {{ strtoupper(substr($placeholder['name'], 0, 1)) }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $placeholder['name'] }}</div>
                                        <div class="text-muted small">{{ $placeholder['role'] }}</div>
                                    </div>
                                </div>
                                <p class="text-muted mb-0">&ldquo;{{ $placeholder['quote'] }}&rdquo;</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforelse
        </div>

    </div>
</section>
