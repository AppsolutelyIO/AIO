/**
 * Scroll-triggered animation utilities
 * Uses a shared IntersectionObserver pool to add/remove classes when elements enter/leave viewport.
 * Observers are reused by (threshold, rootMargin) to reduce memory and improve performance.
 */

export interface ObserveInViewOptions {
    threshold?: number;
    rootMargin?: string;
}

interface SimpleConfig {
    type: 'simple';
}

interface StaggerConfig {
    type: 'stagger';
    titleSelector: string;
    itemSelector: string;
    staggerDelay: number;
    initialDelay: number;
}

type ElementConfig = SimpleConfig | StaggerConfig;

const observerCache = new Map<string, IntersectionObserver>();
const elementConfigs = new WeakMap<Element, ElementConfig>();

function getObserverKey(threshold: number, rootMargin: string): string {
    return `${threshold}:${rootMargin}`;
}

function getOrCreateObserver(threshold: number, rootMargin: string): IntersectionObserver {
    const key = getObserverKey(threshold, rootMargin);
    let observer = observerCache.get(key);

    if (!observer) {
        observer = new IntersectionObserver(
            (entries: IntersectionObserverEntry[]) => {
                entries.forEach((entry: IntersectionObserverEntry) => {
                    const config = elementConfigs.get(entry.target);
                    if (!config) return;

                    const target = entry.target as HTMLElement;
                    const isInView = entry.isIntersecting;

                    if (config.type === 'simple') {
                        target.classList.toggle('in-view', isInView);
                        return;
                    }

                    target.classList.toggle('in-view', isInView);

                    if (config.titleSelector) {
                        const title = target.querySelector<HTMLElement>(config.titleSelector);
                        title?.classList.toggle('animate', isInView);
                    }

                    if (config.itemSelector) {
                        const items = target.querySelectorAll<HTMLElement>(config.itemSelector);
                        items.forEach((item, index) => {
                            if (isInView) {
                                setTimeout(
                                    () => item.classList.add('animate'),
                                    config.initialDelay + index * config.staggerDelay
                                );
                            } else {
                                item.classList.remove('animate');
                            }
                        });
                    }
                });
            },
            { root: null, rootMargin, threshold }
        );
        observerCache.set(key, observer);
    }

    return observer;
}

/**
 * Observe elements and toggle an in-view class when they intersect the viewport.
 *
 * @param selector - CSS selector for elements to observe
 * @param options - IntersectionObserver options (threshold, rootMargin)
 */
export function observeInView(selector: string, options: ObserveInViewOptions = {}): void {
    const elements = document.querySelectorAll<HTMLElement>(selector);
    if (!elements.length) return;

    const { threshold = 0.2, rootMargin = '0px' } = options;
    const observer = getOrCreateObserver(threshold, rootMargin);

    elements.forEach((el) => {
        elementConfigs.set(el, { type: 'simple' });
        observer.observe(el);
    });
}

export interface ObserveInViewWithStaggerOptions extends ObserveInViewOptions {
    titleSelector?: string;
    itemSelector?: string;
    staggerDelay?: number;
    initialDelay?: number;
}

/**
 * Observe elements, toggle in-view class, and apply staggered animations to child elements.
 *
 * @param selector - CSS selector for container elements to observe
 * @param options - Options for threshold, rootMargin, and staggered child selectors
 */
export function observeInViewWithStagger(selector: string, options: ObserveInViewWithStaggerOptions = {}): void {
    const elements = document.querySelectorAll<HTMLElement>(selector);
    if (!elements.length) return;

    const {
        threshold = 0.2,
        rootMargin = '0px',
        titleSelector = '',
        itemSelector = '',
        staggerDelay = 150,
        initialDelay = 200,
    } = options;

    const observer = getOrCreateObserver(threshold, rootMargin);
    const config: StaggerConfig = {
        type: 'stagger',
        titleSelector,
        itemSelector,
        staggerDelay,
        initialDelay,
    };

    elements.forEach((el) => {
        elementConfigs.set(el, config);
        observer.observe(el);
    });
}
