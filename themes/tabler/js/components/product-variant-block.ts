/**
 * Product Variant Block - Native JavaScript Fallback
 *
 * This module provides a fallback for tab switching when Alpine.js fails to initialize.
 * The primary UI is handled by inline Alpine.js in the Blade template.
 * This module activates only if Alpine doesn't initialize properly.
 */

interface ProductVariant {
    name: string;
    price?: number | string;
    specs?: string[];
    colors?: ProductColor[];
}

interface ProductColor {
    name: string;
    code: string;
    images?: string[];
}

interface ProductData {
    name: string;
    common?: Record<string, string>;
    variants: ProductVariant[];
}

/**
 * Extracts product data from a component's script[data-product] element
 */
function extractProductData(container: HTMLElement): ProductData | null {
    const dataEl = container.querySelector('script[data-product]');
    if (!dataEl?.textContent) return null;

    try {
        return JSON.parse(dataEl.textContent.trim()) as ProductData;
    } catch (e) {
        console.error('[ProductVariantBlock] Failed to parse product data:', e);
        return null;
    }
}

/**
 * Native JavaScript fallback for tab switching
 */
function initializeNativeFallback(container: HTMLElement): void {
    const productData = extractProductData(container);
    if (!productData?.variants?.length) return;

    let currentVariantIndex = 0;
    let currentColorIndex = 0;

    const tabs = container.querySelectorAll<HTMLButtonElement>('.product-variant-block__tabs .nav-link');

    function updateDisplay(variantIndex: number, colorIndex: number = 0): void {
        const variant = productData?.variants?.[variantIndex];
        if (!variant) return;

        const color = variant.colors?.[colorIndex];

        tabs.forEach((tab, idx) => {
            tab.classList.toggle('active', idx === variantIndex);
            tab.setAttribute('aria-selected', idx === variantIndex ? 'true' : 'false');
        });

        const mainImage = container.querySelector<HTMLImageElement>('.product-variant-block__main-image-img');
        if (mainImage && color?.images?.[0]) {
            mainImage.src = color.images[0];
            mainImage.alt = color.name || 'Product Image';
        }

        const variantName = container.querySelector<HTMLElement>('.product-variant-block__info h2');
        if (variantName) {
            variantName.textContent = variant.name || 'Variant';
        }

        const priceEl = container.querySelector<HTMLElement>('.product-variant-block__price span:last-child');
        if (priceEl && variant.price !== undefined && variant.price !== null) {
            const price = typeof variant.price === 'number' ? variant.price.toLocaleString() : variant.price;
            priceEl.textContent = String(price);
        }

        // Update specs (use textContent to prevent XSS)
        const specsList = container.querySelector<HTMLElement>('.product-variant-block__specs .list-group');
        if (specsList && variant?.specs) {
            specsList.innerHTML = '';
            variant.specs.forEach((spec) => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = spec;
                specsList.appendChild(li);
            });
        }

        const colorOptions = container.querySelectorAll<HTMLButtonElement>(
            '.product-variant-block__color-selection .product-variant-block__color-option',
        );
        colorOptions.forEach((option, idx) => {
            option.classList.toggle('active', idx === colorIndex);
            const checkIcon = option.querySelector('.fa-check');
            if (checkIcon) {
                (checkIcon as HTMLElement).style.display = idx === colorIndex ? 'block' : 'none';
            }
        });

        const colorName = container.querySelector<HTMLElement>('.product-variant-block__color-selection p span');
        if (colorName && color) {
            colorName.textContent = color.name || 'Unnamed Color';
        }

        currentVariantIndex = variantIndex;
        currentColorIndex = colorIndex;
    }

    // Event delegation for clicks
    container.addEventListener('click', (e) => {
        const target = e.target as HTMLElement;

        const tabButton = target.closest<HTMLButtonElement>('.product-variant-block__tabs .nav-link');
        if (tabButton) {
            e.preventDefault();
            e.stopPropagation();
            const tabIndex = Array.from(tabs).indexOf(tabButton);
            if (tabIndex >= 0 && tabIndex !== currentVariantIndex) {
                updateDisplay(tabIndex, 0);
            }
        }

        const colorButton = target.closest<HTMLButtonElement>(
            '.product-variant-block__color-selection .product-variant-block__color-option',
        );
        if (colorButton) {
            e.preventDefault();
            e.stopPropagation();
            const colorOptions = container.querySelectorAll<HTMLButtonElement>(
                '.product-variant-block__color-selection .product-variant-block__color-option',
            );
            const colorIndex = Array.from(colorOptions).indexOf(colorButton);
            if (colorIndex >= 0 && colorIndex !== currentColorIndex) {
                updateDisplay(currentVariantIndex, colorIndex);
            }
        }
    });

    container.setAttribute('data-native-fallback', 'true');
}

/**
 * Check if Alpine.js has initialized a component.
 */
function hasAlpineInitialized(element: HTMLElement): boolean {
    const alpineRoot = element.querySelector('[x-data]');
    if (!alpineRoot) return false;

    try {
        if ((alpineRoot as unknown as { __x?: unknown }).__x !== undefined) return true;
        const stack = (alpineRoot as unknown as { _x_dataStack?: unknown[] })._x_dataStack;
        return !!(stack && stack.length > 0);
    } catch {
        return false;
    }
}

/**
 * Initialize fallback for all product variant blocks if Alpine fails
 */
function initializeAllBlocks(): void {
    document.querySelectorAll<HTMLElement>('.product-variant-block').forEach((block) => {
        if (block.hasAttribute('data-native-fallback')) return;

        setTimeout(() => {
            if (!hasAlpineInitialized(block)) {
                console.warn('[ProductVariantBlock] Alpine not initialized, using native fallback');
                initializeNativeFallback(block);
            }
        }, 500);
    });
}

let livewireListenersSetup = false;

function setupLivewireListeners(): void {
    if (livewireListenersSetup) return;
    livewireListenersSetup = true;

    document.addEventListener('livewire:navigated', () => {
        setTimeout(initializeAllBlocks, 100);
    });

    document.addEventListener('livewire:init', () => {
        setTimeout(initializeAllBlocks, 100);
    });
}

export function init(): void {
    setTimeout(initializeAllBlocks, 100);
    setupLivewireListeners();
}

export { initializeNativeFallback, initializeAllBlocks };
