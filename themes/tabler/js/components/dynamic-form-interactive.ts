/**
 * Dynamic Form Interactive Component
 * Handles background image switching, height synchronization, and Livewire event handling
 */

import { buildUrl } from '../utils/url';

const LIVEWIRE_RENDER_DELAY_MS = 100;
const HIDDEN_FIELD_POLL_DELAY_MS = 200;
const FALLBACK_RESIZE_DELAY_MS = 100;

interface OptionsMapping {
    [key: string]: string;
}

function getUrlParameter(name: string): string | null {
    const params = new URLSearchParams(window.location.search);
    return params.get(name);
}

function normalizeVehicleName(name: string): string {
    return name.trim().toLowerCase();
}

function findMatchingOption(value: string, mapping: OptionsMapping): string | null {
    const normalizedValue = normalizeVehicleName(value);

    for (const [key, url] of Object.entries(mapping)) {
        if (normalizeVehicleName(key) === normalizedValue) {
            return url;
        }
    }

    const valueWithSpaces = normalizedValue.replace(/-/g, ' ');
    const valueWithHyphens = normalizedValue.replace(/\s+/g, '-');

    for (const [key, url] of Object.entries(mapping)) {
        const normalizedKey = normalizeVehicleName(key);
        if (normalizedKey === valueWithSpaces || normalizedKey === valueWithHyphens) {
            return url;
        }
    }

    return null;
}

function updateBackgroundImage(container: HTMLElement, imageUrl: string, baseUrl: string): void {
    const backgroundImageEl = container.querySelector<HTMLElement>('.dynamic-form-interactive__background-image');
    if (!backgroundImageEl) return;

    const normalizedUrl = buildUrl(imageUrl, baseUrl);

    const currentBg = backgroundImageEl.style.backgroundImage;
    if (currentBg && currentBg.includes(normalizedUrl)) {
        return;
    }

    const img = new Image();
    img.onload = () => {
        backgroundImageEl.style.backgroundImage = `url(${normalizedUrl})`;
        backgroundImageEl.classList.add('active');
    };
    img.onerror = () => {
        console.error('[DynamicFormInteractive] Failed to load image:', normalizedUrl);
    };
    img.src = normalizedUrl;
}

const RESIZE_CLEANUP = new WeakMap<HTMLElement, () => void>();

function syncHeight(container: HTMLElement): void {
    const backgroundEl = container.querySelector<HTMLElement>('.dynamic-form-interactive__background');
    const formContainerEl = container.querySelector<HTMLElement>('.dynamic-form-interactive__container');

    if (!backgroundEl || !formContainerEl) return;

    RESIZE_CLEANUP.get(container)?.();

    const updateHeight = (): void => {
        if (!document.contains(formContainerEl)) {
            RESIZE_CLEANUP.get(container)?.();
            return;
        }
        const formHeight = formContainerEl.offsetHeight;
        if (formHeight > 0) {
            backgroundEl.style.height = `${formHeight}px`;
        }
    };

    let cleanup: () => void;

    if (typeof ResizeObserver !== 'undefined') {
        const observer = new ResizeObserver(updateHeight);
        observer.observe(formContainerEl);
        cleanup = () => observer.disconnect();
    } else {
        window.addEventListener('resize', updateHeight);
        setTimeout(updateHeight, FALLBACK_RESIZE_DELAY_MS);
        cleanup = () => window.removeEventListener('resize', updateHeight);
    }

    RESIZE_CLEANUP.set(container, cleanup);
    updateHeight();
}

function getRandomImageUrl(mapping: OptionsMapping): string | null {
    const urls = Object.values(mapping).filter((url) => url && url.trim());
    if (urls.length === 0) return null;
    const randomIndex = Math.floor(Math.random() * urls.length);
    return urls[randomIndex];
}

function applyInitialImage(
    container: HTMLElement,
    baseUrl: string,
    urlValue: string | null,
    mapping: OptionsMapping,
): void {
    const imageUrl = urlValue ? findMatchingOption(urlValue, mapping) : getRandomImageUrl(mapping);
    if (imageUrl) {
        updateBackgroundImage(container, imageUrl, baseUrl);
    }
}

function getOptionsMapping(container: HTMLElement): OptionsMapping | null {
    const containerMapping = container.getAttribute('data-options-mapping');
    if (containerMapping) {
        try {
            return JSON.parse(containerMapping) as OptionsMapping;
        } catch {
            return null;
        }
    }

    const hiddenField = container.querySelector<HTMLInputElement>('input[type="hidden"][data-options-mapping]');
    if (hiddenField) {
        try {
            return JSON.parse(hiddenField.getAttribute('data-options-mapping') || '{}') as OptionsMapping;
        } catch {
            return null;
        }
    }

    return null;
}

function initializeFromUrl(container: HTMLElement, baseUrl: string, triggerFieldName: string): void {
    const urlValue = getUrlParameter(triggerFieldName);

    let mapping = getOptionsMapping(container);
    if (!mapping) {
        setTimeout(() => {
            mapping = getOptionsMapping(container);
            if (mapping) {
                applyInitialImage(container, baseUrl, urlValue, mapping);
            }
        }, HIDDEN_FIELD_POLL_DELAY_MS);
        return;
    }

    applyInitialImage(container, baseUrl, urlValue, mapping);
}

function setupFormListeners(container: HTMLElement, baseUrl: string, triggerFieldName: string): void {
    const triggerField =
        container.querySelector<HTMLSelectElement>(`select[data-field-name="${triggerFieldName}"]`) ||
        container.querySelector<HTMLSelectElement>(`select[name="${triggerFieldName}"]`) ||
        container.querySelector<HTMLSelectElement>(`select[id="${triggerFieldName}"]`);
    if (!triggerField) {
        console.warn(`[DynamicFormInteractive] Trigger field "${triggerFieldName}" not found`);
        return;
    }

    const hiddenField = container.querySelector<HTMLInputElement>('input[type="hidden"][data-options-mapping]');
    if (!hiddenField) return;

    let mapping: OptionsMapping = {};
    try {
        mapping = JSON.parse(hiddenField.getAttribute('data-options-mapping') || '{}');
    } catch (error) {
        console.error('[DynamicFormInteractive] Failed to parse options mapping:', error);
        return;
    }

    triggerField.addEventListener('change', (e) => {
        const target = e.target as HTMLSelectElement;
        const selectedValue = target.value;
        if (!selectedValue) return;

        const imageUrl = findMatchingOption(selectedValue, mapping);
        if (imageUrl) {
            updateBackgroundImage(container, imageUrl, baseUrl);
        }
    });

    container.addEventListener('livewire:update', () => {
        const selectedValue = triggerField.value;
        if (selectedValue) {
            const imageUrl = findMatchingOption(selectedValue, mapping);
            if (imageUrl) {
                updateBackgroundImage(container, imageUrl, baseUrl);
            }
        }
    });
}

function initializeComponent(container: HTMLElement): void {
    const baseUrl = container.getAttribute('data-asset-base-url') || '/assets/';
    const triggerFieldName = container.getAttribute('data-trigger-field') || 'vehicle_interest';

    syncHeight(container);
    initializeFromUrl(container, baseUrl, triggerFieldName);

    setTimeout(() => {
        setupFormListeners(container, baseUrl, triggerFieldName);
    }, LIVEWIRE_RENDER_DELAY_MS);
}

function initializeAllComponents(): void {
    const containers = document.querySelectorAll<HTMLElement>('.dynamic-form-interactive');
    containers.forEach((container) => {
        if (container.hasAttribute('data-initialized')) return;
        container.setAttribute('data-initialized', 'true');
        initializeComponent(container);
    });
}

let livewireListenersSetup = false;

function setupLivewireListeners(): void {
    if (livewireListenersSetup) return;
    livewireListenersSetup = true;

    document.addEventListener('livewire:load', () => {
        setTimeout(initializeAllComponents, LIVEWIRE_RENDER_DELAY_MS);
    });

    document.addEventListener('livewire:update', () => {
        setTimeout(initializeAllComponents, LIVEWIRE_RENDER_DELAY_MS);
    });

    document.addEventListener('livewire:navigated', () => {
        setTimeout(initializeAllComponents, LIVEWIRE_RENDER_DELAY_MS);
    });
}

export function init(): void {
    setTimeout(initializeAllComponents, LIVEWIRE_RENDER_DELAY_MS);
    setupLivewireListeners();
}

export { initializeComponent, initializeAllComponents };
