@php
    $bgColor = $displayOptions['background_color'] ?? 'primary';
    $heroes = $displayOptions['heroes'] ?? [];
    $firstHero = $heroes[0] ?? null;
    $heroImage = $firstHero ? asset_url($firstHero['url'] ?? '') : '';
@endphp

<div class="hero hero--split-layout
    @if ($bgColor === 'primary') bg-primary text-white
    @elseif ($bgColor === 'dark') bg-dark text-white
    @else bg-light text-dark @endif">
    <div class="container-xl py-5">
        <div class="row align-items-center g-4">
            {{-- Text Column --}}
            <div class="col-lg-6">
                @if ($displayOptions['heading'] ?? null)
                    <h1 class="display-4 fw-bold mb-3">{{ $displayOptions['heading'] }}</h1>
                @endif

                @if ($displayOptions['subheading'] ?? null)
                    <p class="lead mb-4 opacity-75">{{ $displayOptions['subheading'] }}</p>
                @endif

                <div class="d-flex gap-2 flex-wrap">
                    @if (($displayOptions['cta_primary_text'] ?? null) && ($displayOptions['cta_primary_url'] ?? null))
                        <a href="{{ $displayOptions['cta_primary_url'] }}"
                            class="btn btn-lg {{ $bgColor !== 'light' ? 'btn-white' : 'btn-primary' }}">
                            {{ $displayOptions['cta_primary_text'] }}
                        </a>
                    @endif

                    @if (($displayOptions['cta_secondary_text'] ?? null) && ($displayOptions['cta_secondary_url'] ?? null))
                        <a href="{{ $displayOptions['cta_secondary_url'] }}"
                            class="btn btn-lg {{ $bgColor !== 'light' ? 'btn-outline-white' : 'btn-outline-primary' }}">
                            {{ $displayOptions['cta_secondary_text'] }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Image Column --}}
            <div class="col-lg-6">
                @if ($heroImage)
                    <div class="hero__image rounded-3 overflow-hidden shadow-lg">
                        <img src="{{ $heroImage }}"
                            alt="{{ $firstHero['title'] ?? ($displayOptions['heading'] ?? 'Hero image') }}"
                            class="w-100 h-auto d-block" loading="eager">
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
