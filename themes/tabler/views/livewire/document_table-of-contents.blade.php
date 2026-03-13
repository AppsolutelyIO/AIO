@php
    $content = $displayOptions['content'] ?? '';
    $htmlContent = md2html($content);

    // Extract headings for Table of Contents
    $tocItems = [];
    if (preg_match_all('/<h([2-4])[^>]*>(.*?)<\/h[2-4]>/i', $htmlContent, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $index => $match) {
            $level = (int) $match[1];
            $text = strip_tags($match[2]);
            $id = 'toc-' . Str::slug($text) . '-' . $index;
            $tocItems[] = ['level' => $level, 'text' => $text, 'id' => $id];
            // Add id attribute to heading in content
            $htmlContent = str_replace(
                $match[0],
                '<h' . $level . ' id="' . $id . '">' . $match[2] . '</h' . $level . '>',
                $htmlContent,
            );
        }
    }
@endphp

<section class="document document--table-of-contents py-5">
    <div class="document__container container-xl">
        <div class="document__row row">
            {{-- Sidebar Table of Contents --}}
            @if (count($tocItems) > 0)
                <div class="document__sidebar col-lg-3 d-none d-lg-block">
                    <nav class="document__toc card sticky-top" style="top: 1rem" aria-label="Table of contents">
                        <div class="card-header">
                            <h3 class="card-title">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler me-2" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 6h16" />
                                    <path d="M4 12h10" />
                                    <path d="M4 18h14" />
                                </svg>
                                {{ __t('Table of Contents') }}
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="document__toc-list list-group list-group-flush" role="list">
                                @foreach ($tocItems as $item)
                                    <li class="list-group-item border-0 py-1"
                                        style="padding-left: {{ ($item['level'] - 2) * 1 + 0.75 }}rem">
                                        <a href="#{{ $item['id'] }}"
                                            class="document__toc-link text-reset text-decoration-none d-block py-1 {{ $item['level'] === 2 ? 'fw-semibold' : 'text-muted small' }}">
                                            {{ $item['text'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </nav>
                </div>
            @endif

            {{-- Main Content --}}
            <div class="{{ count($tocItems) > 0 ? 'col-lg-9' : 'col-lg-8 mx-auto' }}">
                {{-- Title --}}
                @if ($displayOptions['title'] ?? false)
                    <h1 class="document__title fw-bold mb-3">
                        {{ $displayOptions['title'] }}
                    </h1>
                @endif

                {{-- Subtitle --}}
                @if ($displayOptions['subtitle'] ?? false)
                    <p class="document__subtitle lead text-muted mb-4">
                        {{ $displayOptions['subtitle'] }}
                    </p>
                @endif

                {{-- Meta Information --}}
                @if (
                    ($displayOptions['show_meta'] ?? true) &&
                        (($displayOptions['published_date'] ?? false) || ($displayOptions['author'] ?? false)))
                    <div class="document__meta text-muted mb-4 pb-3 border-bottom">
                        <small class="d-flex align-items-center gap-3">
                            @if ($displayOptions['author'] ?? false)
                                <span class="d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler me-1" width="16"
                                        height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" aria-hidden="true">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                    </svg>
                                    {{ $displayOptions['author'] }}
                                </span>
                            @endif
                            @if ($displayOptions['published_date'] ?? false)
                                <span class="d-inline-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler me-1" width="16"
                                        height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" aria-hidden="true">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                    </svg>
                                    <time datetime="{{ $displayOptions['published_date'] }}">
                                        {{ \Carbon\Carbon::parse($displayOptions['published_date'])->format('F j, Y') }}
                                    </time>
                                </span>
                            @endif
                        </small>
                    </div>
                @endif

                {{-- Mobile TOC (collapsible) --}}
                @if (count($tocItems) > 0)
                    <div class="d-lg-none mb-4">
                        <div class="card">
                            <div class="card-header">
                                <a class="card-title text-reset text-decoration-none" data-bs-toggle="collapse"
                                    href="#mobileToc" role="button" aria-expanded="false"
                                    aria-controls="mobileToc">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler me-2"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" aria-hidden="true">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M4 6h16" />
                                        <path d="M4 12h10" />
                                        <path d="M4 18h14" />
                                    </svg>
                                    {{ __t('Table of Contents') }}
                                </a>
                            </div>
                            <div class="collapse" id="mobileToc">
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush" role="list">
                                        @foreach ($tocItems as $item)
                                            <li class="list-group-item border-0 py-1"
                                                style="padding-left: {{ ($item['level'] - 2) * 1 + 0.75 }}rem">
                                                <a href="#{{ $item['id'] }}"
                                                    class="text-reset text-decoration-none d-block py-1 {{ $item['level'] === 2 ? 'fw-semibold' : 'text-muted small' }}">
                                                    {{ $item['text'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Content --}}
                @if ($content)
                    <div class="document__body content-body markdown">
                        {!! $htmlContent !!}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
