/**
 * Features Component JavaScript
 * Handles scroll-triggered animations for features titles and descriptions
 */

import { observeInViewWithStagger } from '../utils/scroll-animation';

export function init(): void {
    observeInViewWithStagger('.features, .features-fullscreen, .features-fashionable', {
        threshold: 0.2,
        titleSelector: '.features__title, .features-fullscreen__title, .features-fashionable__title',
        itemSelector: '.features__description, .features-fullscreen__description, .features-fashionable__description',
        staggerDelay: 150,
        initialDelay: 200,
    });
}
