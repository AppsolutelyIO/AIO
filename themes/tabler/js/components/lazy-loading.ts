/**
 * Lazy Loading Component
 *
 * Provides consistent lazy loading functionality using vanilla-lazyload
 * for images, videos, and background images across the Tabler theme.
 */

import LazyLoad, { type ILazyLoadInstance, type ILazyLoadOptions } from 'vanilla-lazyload';

import { escapeHtmlAttr } from '../utils/escape';

interface LazyLoadOptions {
    rootMargin?: string;
    threshold?: number;
    enableAuto?: boolean;
    loadingClass?: string;
    loadedClass?: string;
    errorClass?: string;
}

class LazyLoadingManager {
    private lazyLoadInstance: ILazyLoadInstance | null = null;
    private defaultOptions: LazyLoadOptions = {
        rootMargin: '50px 0px',
        threshold: 0.1,
        enableAuto: true,
        loadingClass: 'lazy-loading',
        loadedClass: 'lazy-loaded',
        errorClass: 'lazy-error',
    };

    constructor() {
        this.init();
    }

    /**
     * Initialize lazy loading with default options
     */
    init(options: Partial<LazyLoadOptions> = {}): void {
        const config = { ...this.defaultOptions, ...options };

        this.lazyLoadInstance = new LazyLoad({
            elements_selector: '.lazy',
            root_margin: config.rootMargin,
            threshold: config.threshold,
            enableAuto: config.enableAuto,
            class_loading: config.loadingClass,
            class_loaded: config.loadedClass,
            class_error: config.errorClass,
            callback_loaded: (element: HTMLElement) => {
                this.onElementLoaded(element);
            },
            callback_error: (element: HTMLElement) => {
                this.onElementError(element);
            },
            // Handle background images
            callback_enter: (element: HTMLElement) => {
                if (element.classList.contains('lazy-bg')) {
                    const bgSrc = element.getAttribute('data-bg');
                    if (bgSrc) {
                        element.style.backgroundImage = `url(${bgSrc})`;
                    }
                }
            },
        } as ILazyLoadOptions);

        // Make instance available globally for debugging
        if (typeof window !== 'undefined') {
            window.lazyLoadInstance = this.lazyLoadInstance;
        }
    }

    /**
     * Update lazy loading for dynamically added content
     */
    update(): void {
        if (this.lazyLoadInstance) {
            this.lazyLoadInstance.update();
        }
    }

    /**
     * Destroy lazy loading instance
     */
    destroy(): void {
        if (this.lazyLoadInstance) {
            this.lazyLoadInstance.destroy();
            this.lazyLoadInstance = null;
        }
    }

    /**
     * Handle element loaded callback
     */
    private onElementLoaded(element: HTMLElement): void {
        // Add fade-in animation
        element.style.opacity = '0';
        element.style.transition = 'opacity 0.3s ease-in-out';

        // Trigger fade-in after a short delay
        setTimeout(() => {
            element.style.opacity = '1';
        }, 50);

        // Dispatch custom event for additional handling
        element.dispatchEvent(
            new window.CustomEvent('lazyLoaded', {
                detail: { element },
            }),
        );
    }

    /**
     * Handle element error callback
     */
    private onElementError(element: HTMLElement): void {
        console.warn('Lazy loading failed for element:', element);

        // Dispatch custom event for error handling
        element.dispatchEvent(
            new window.CustomEvent('lazyError', {
                detail: { element },
            }),
        );
    }

    /**
     * Create lazy image element with proper attributes.
     * Uses attribute escaping to prevent XSS when src/alt/className contain user input.
     */
    static createLazyImage(src: string, alt: string = '', className: string = ''): string {
        const e = escapeHtmlAttr;
        return `<img class="lazy ${e(className)}" data-src="${e(src)}" alt="${e(alt)}" loading="lazy" />`;
    }

    /**
     * Create lazy video element with proper attributes.
     * Uses attribute escaping to prevent XSS when src/poster/className contain user input.
     */
    static createLazyVideo(src: string, poster?: string, className: string = ''): string {
        const e = escapeHtmlAttr;
        const posterAttr = poster ? ` poster="${e(poster)}"` : '';
        return `<video class="lazy ${e(className)}" data-src="${e(src)}"${posterAttr} controls preload="none">Your browser does not support the video tag.</video>`;
    }

    /**
     * Create lazy background image element.
     * Uses attribute escaping to prevent XSS when src/className contain user input.
     */
    static createLazyBackground(src: string, className: string = ''): string {
        const e = escapeHtmlAttr;
        return `<div class="lazy lazy-bg ${e(className)}" data-bg="${e(src)}"></div>`;
    }
}

// Initialize lazy loading when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const lazyManager = new LazyLoadingManager();

    // Make manager available globally for dynamic content
    if (typeof window !== 'undefined') {
        window.lazyManager = lazyManager;
    }
});

// Export for use in other components
export default LazyLoadingManager;
