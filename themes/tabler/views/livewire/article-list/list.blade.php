<div class="container-xl">
    @if ($displayOptions['title'] ?? false)
        <div class="text-center mb-5">
            <h2 class="h1 mb-2">{{ $displayOptions['title'] }}</h2>
            @if ($displayOptions['subtitle'] ?? false)
                <p class="text-muted lead">{{ $displayOptions['subtitle'] }}</p>
            @endif
        </div>
    @endif

    @if ($articles->count() > 0)
        <div class="row row-cards g-4">
            @foreach ($articles as $article)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        @if (($displayOptions['show_featured_image'] ?? true) && $article->cover)
                            <a href="{{ nested_url($article->slug) }}">
                                <img src="{{ $article->cover }}" class="card-img-top"
                                    alt="{{ $article->title }}" style="height: 200px; object-fit: cover;">
                            </a>
                        @endif
                        <div class="card-body d-flex flex-column">
                            @if (($displayOptions['show_date'] ?? true) && $article->published_at)
                                <div class="text-muted small mb-2">
                                    {{ $article->published_at->format('M j, Y') }}
                                </div>
                            @endif

                            <h3 class="card-title">
                                <a href="{{ nested_url($article->slug) }}" class="text-reset">
                                    {{ $article->title }}
                                </a>
                            </h3>

                            @if (($displayOptions['show_excerpt'] ?? true) && $article->description)
                                <p class="text-muted flex-grow-1">
                                    {{ Str::limit($article->description, 120) }}
                                </p>
                            @endif

                            <div class="mt-auto">
                                <a href="{{ nested_url($article->slug) }}" class="btn btn-primary btn-sm">
                                    {{ $displayOptions['read_more_text'] ?? 'Read More' }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm ms-1" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M5 12l14 0" />
                                        <path d="M13 18l6 -6" />
                                        <path d="M13 6l6 6" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($articles->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $articles->links() }}
            </div>
        @endif
    @else
        <div class="empty">
            <div class="empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="24" height="24"
                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M16 6h3a1 1 0 0 1 1 1v11a2 2 0 0 1 -4 0v-13a1 1 0 0 0 -1 -1h-10a1 1 0 0 0 -1 1v12a3 3 0 0 0 3 3h11" />
                    <path d="M8 8l4 0" />
                    <path d="M8 12l4 0" />
                    <path d="M8 16l4 0" />
                </svg>
            </div>
            <p class="empty-title">{{ __('No articles found') }}</p>
            <p class="empty-subtitle text-muted">{{ __('Check back later for new content.') }}</p>
        </div>
    @endif
</div>
