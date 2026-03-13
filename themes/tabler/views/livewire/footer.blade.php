<footer class="site-footer footer footer-transparent d-print-none">
    <div class="container-xl">

        {{-- Main footer row: logo + menus + contact --}}
        @if ($footerMenuItems->isNotEmpty() || ($displayOptions['contact']['enabled'] ?? false))
            <div class="row py-4 border-bottom">

                {{-- Logo --}}
                @if ($displayOptions['logo'] ?? true)
                    <div class="col-12 col-md-3 mb-4 mb-md-0">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset_url('assets/images/logo.webp') }}" alt="{{ site_title() }}"
                                class="footer-logo-img">
                        </a>
                    </div>
                @endif

                {{-- Footer menu --}}
                @if ($footerMenuItems->isNotEmpty())
                    <div class="col-12 col-md mb-4 mb-md-0">
                        <ul class="list-unstyled mb-0">
                            @foreach ($footerMenuItems as $item)
                                <li class="mb-1">
                                    <a href="{{ app_uri($item->url) }}" target="{{ $item->target->value }}"
                                        class="link-secondary">
                                        @if ($item->icon)
                                            <i class="{{ $item->icon }} me-1"></i>
                                        @endif
                                        {{ $item->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Contact info --}}
                @if ($displayOptions['contact']['enabled'] ?? false)
                    <div class="col-12 col-md-auto">
                        <ul class="list-unstyled mb-0 text-muted small">
                            @if ($displayOptions['contact']['address'] ?? null)
                                <li class="mb-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7z" />
                                        <circle cx="12" cy="9" r="2.5" />
                                    </svg>
                                    {{ $displayOptions['contact']['address'] }}
                                </li>
                            @endif
                            @if ($displayOptions['contact']['phone'] ?? null)
                                <li class="mb-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5-2.5l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2" />
                                    </svg>
                                    <a href="tel:{{ $displayOptions['contact']['phone'] }}"
                                        class="link-secondary">{{ $displayOptions['contact']['phone'] }}</a>
                                </li>
                            @endif
                            @if ($displayOptions['contact']['email'] ?? null)
                                <li class="mb-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <polyline points="3 7 12 13 21 7" />
                                    </svg>
                                    <a href="mailto:{{ $displayOptions['contact']['email'] }}"
                                        class="link-secondary">{{ $displayOptions['contact']['email'] }}</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Bottom bar: copyright + policy links + social --}}
        <div class="row align-items-center py-3">
            {{-- Copyright --}}
            <div class="col-12 col-lg-auto">
                <span class="text-muted small">
                    {{ $displayOptions['copyright']['text'] ?? '© ' . date('Y') . ' ' . site_title() . '. All rights reserved.' }}
                </span>
            </div>

            {{-- Policy links --}}
            @if ($policyMenuItems->isNotEmpty())
                <div class="col-12 col-lg-auto mt-2 mt-lg-0">
                    <ul class="list-inline list-inline-dots mb-0">
                        @foreach ($policyMenuItems as $item)
                            <li class="list-inline-item">
                                <a href="{{ app_uri($item->url) }}" target="{{ $item->target->value }}"
                                    class="link-secondary small">{{ $item->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Social links --}}
            @if ($socialMediaItems->isNotEmpty())
                <div class="col-12 col-lg-auto ms-lg-auto mt-2 mt-lg-0 social-links">
                    @foreach ($socialMediaItems as $item)
                        <a href="{{ app_uri($item->url) }}" target="{{ $item->target->value }}"
                            class="btn btn-icon btn-ghost-secondary btn-sm ms-1"
                            aria-label="{{ $item->title }}" title="{{ $item->title }}">
                            @if ($item->icon)
                                <i class="{{ $item->icon }}"></i>
                            @else
                                {{ $item->title }}
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</footer>
