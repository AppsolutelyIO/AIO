/**
 * Anchor Navigation Component
 * Updates active state based on scroll position (scroll-spy)
 */

const ACTIVE_CLASS = 'anchor-nav__link--active';
/** Offset for scroll-spy to account for sticky header */
const SCROLL_SPY_OFFSET = 120;

function updateActiveLink(links: NodeListOf<HTMLAnchorElement>): void {
    const viewportTop = window.scrollY + SCROLL_SPY_OFFSET;
    let activeHref: string | null = null;

    links.forEach((link) => {
        const href = link.getAttribute('href');
        if (!href || !href.startsWith('#block-')) return;

        const target = document.getElementById(href.slice(1));
        if (!target) return;

        const rect = target.getBoundingClientRect();
        const top = rect.top + window.scrollY;

        if (top <= viewportTop) {
            activeHref = href;
        }
    });

    // If none passed, use first
    if (!activeHref && links.length > 0) {
        const firstHref = links[0].getAttribute('href');
        if (firstHref) activeHref = firstHref;
    }

    links.forEach((link) => {
        if (link.getAttribute('href') === activeHref) {
            link.classList.add(ACTIVE_CLASS);
        } else {
            link.classList.remove(ACTIVE_CLASS);
        }
    });
}

const TO_TOP_VISIBLE_CLASS = 'anchor-nav__to-top--visible';
const SCROLL_THRESHOLD = 200;
/** Offset above anchor to account for floating mega menu / header */
const SCROLL_OFFSET = 180;

function initToTopButton(): void {
    const toTop = document.querySelector<HTMLButtonElement>('.anchor-nav__to-top');
    const nav = document.querySelector<HTMLElement>('.anchor-nav');
    if (!toTop || !nav) return;

    function updateVisibility(): void {
        if (!toTop) return;
        if (window.scrollY > SCROLL_THRESHOLD) {
            toTop.classList.add(TO_TOP_VISIBLE_CLASS);
        } else {
            toTop.classList.remove(TO_TOP_VISIBLE_CLASS);
        }
    }

    toTop.addEventListener('click', () => {
        const navTop = nav.getBoundingClientRect().top + window.scrollY;
        const targetScroll = Math.max(0, navTop - SCROLL_OFFSET);
        window.scrollTo({ top: targetScroll, behavior: 'smooth' });
    });

    updateVisibility();
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(() => {
            updateVisibility();
            ticking = false;
        });
    });
}

function initAnchorNav(): void {
    const nav = document.querySelector<HTMLElement>('.anchor-nav');
    if (!nav) return;

    const links = nav.querySelectorAll<HTMLAnchorElement>('.anchor-nav__link[href^="#block-"]');
    if (links.length === 0) return;

    updateActiveLink(links);

    let ticking = false;
    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(() => {
            updateActiveLink(links);
            ticking = false;
        });
    });

    links.forEach((link) => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (!href || !href.startsWith('#')) return;

            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    initToTopButton();
}

export function init(): void {
    initAnchorNav();
}
