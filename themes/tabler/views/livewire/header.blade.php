<header class="site-header navbar navbar-expand-md navbar-light d-print-none">
    <div class="container-xl">
        {{-- Mobile toggle --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu"
            aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
            @if ($displayOptions['logo'] ?? true)
                <img src="{{ asset_url('assets/images/logo.webp') }}" alt="{{ site_title() }}"
                    class="navbar-brand-image">
            @else
                <span class="fw-bold fs-4">{{ site_title() }}</span>
            @endif
        </a>

        {{-- Collapsible nav --}}
        <div class="collapse navbar-collapse" id="navbar-menu">
            <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                @if ($mainNavigation->isNotEmpty())
                    <ul class="navbar-nav">
                        @foreach ($mainNavigation as $item)
                            @if ($item->children->isNotEmpty())
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                        role="button" aria-expanded="false">
                                        @if ($item->icon)
                                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                                <i class="{{ $item->icon }}"></i>
                                            </span>
                                        @endif
                                        <span class="nav-link-title">{{ $item->title }}</span>
                                    </a>
                                    <div class="dropdown-menu submenu-container">
                                        @foreach ($item->children as $child)
                                            <a class="dropdown-item d-flex align-items-center"
                                                href="{{ app_uri($child->url) }}"
                                                target="{{ $child->target->value }}">
                                                @if ($child->thumbnail)
                                                    <img class="nav-thumbnail me-2"
                                                        src="{{ asset_url($child->thumbnail) }}"
                                                        alt="{{ $child->title }}">
                                                @elseif ($child->icon)
                                                    <i class="{{ $child->icon }} me-2"></i>
                                                @endif
                                                {{ $child->title }}
                                            </a>
                                        @endforeach
                                    </div>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->url() === app_uri($item->url) ? 'active' : '' }}"
                                        href="{{ app_uri($item->url) }}"
                                        target="{{ $item->target->value }}">
                                        @if ($item->icon)
                                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                                <i class="{{ $item->icon }}"></i>
                                            </span>
                                        @endif
                                        <span class="nav-link-title">{{ $item->title }}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- CTA button --}}
            @if (($displayOptions['cta']['text'] ?? null) && ($displayOptions['cta']['url'] ?? null))
                <div class="ms-md-auto d-flex">
                    <a href="{{ $displayOptions['cta']['url'] }}" class="btn btn-primary">
                        {{ $displayOptions['cta']['text'] }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</header>
