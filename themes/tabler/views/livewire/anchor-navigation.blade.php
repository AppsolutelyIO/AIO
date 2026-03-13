@php
    $anchorItems = $anchorItems ?? [];
    $ctaText = $displayOptions['cta_text'] ?? '';
    $ctaUrl = $displayOptions['cta_url'] ?? '#';
    $ctaIcon = $displayOptions['cta_icon'] ?? 'fa-steering-wheel';
@endphp
<nav class="anchor-navigation sticky-top bg-dark py-3 {{ count($anchorItems) === 0 ? 'anchor-navigation--empty' : '' }}"
    role="navigation" aria-label="{{ __t('Section navigation') }}">
    @if (count($anchorItems) > 0)
        <div class="container-xl">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <ul class="anchor-navigation__list nav mb-0 flex-grow-1 flex-wrap" role="list">
                    @foreach ($anchorItems as $index => $item)
                        <li class="anchor-navigation__item nav-item">
                            <a class="anchor-navigation__link nav-link text-white text-uppercase {{ $index === 0 ? 'anchor-navigation__link--active' : '' }}"
                                href="#block-{{ $item['reference'] }}" data-anchor-index="{{ $index }}">
                                {{ $item['title'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if ($ctaText !== '')
                    <a href="{{ $ctaUrl }}"
                        class="anchor-navigation__cta btn btn-outline-light btn-sm text-nowrap ms-auto flex-shrink-0">
                        @if ($ctaIcon !== '')
                            <i class="fa {{ $ctaIcon }} me-1" aria-hidden="true"></i>
                        @endif
                        {{ $ctaText }}
                    </a>
                @endif
            </div>
        </div>
        <button type="button" class="anchor-navigation__to-top" aria-label="{{ __t('Go to top') }}"
            title="{{ __t('Go to top') }}">
            <i class="bi bi-chevron-double-up" aria-hidden="true"></i>
        </button>
    @else
        <div class="container-xl">
            <span class="text-white-50 small">{{ __t('global.anchor.empty_placeholder') }}</span>
        </div>
    @endif
</nav>
