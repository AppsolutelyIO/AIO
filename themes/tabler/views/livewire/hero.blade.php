<div class="hero page-header
    @if (($displayOptions['background_color'] ?? 'primary') === 'primary') bg-primary text-white
    @elseif (($displayOptions['background_color'] ?? '') === 'dark') bg-dark text-white
    @else bg-light text-dark
    @endif
    d-flex align-items-center py-6">
    <div class="container-xl text-center">
        @if ($displayOptions['heading'] ?? null)
            <h2 class="display-4 fw-bold mb-3">{{ $displayOptions['heading'] }}</h2>
        @endif

        @if ($displayOptions['subheading'] ?? null)
            <p class="lead mb-4 opacity-75">{{ $displayOptions['subheading'] }}</p>
        @endif

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            @if (($displayOptions['cta_primary_text'] ?? null) && ($displayOptions['cta_primary_url'] ?? null))
                <a href="{{ $displayOptions['cta_primary_url'] }}"
                    class="btn btn-lg {{ ($displayOptions['background_color'] ?? 'primary') !== 'light' ? 'btn-white' : 'btn-primary' }}">
                    {{ $displayOptions['cta_primary_text'] }}
                </a>
            @endif

            @if (($displayOptions['cta_secondary_text'] ?? null) && ($displayOptions['cta_secondary_url'] ?? null))
                <a href="{{ $displayOptions['cta_secondary_url'] }}"
                    class="btn btn-lg {{ ($displayOptions['background_color'] ?? 'primary') !== 'light' ? 'btn-outline-white' : 'btn-outline-primary' }}">
                    {{ $displayOptions['cta_secondary_text'] }}
                </a>
            @endif
        </div>
    </div>
</div>
