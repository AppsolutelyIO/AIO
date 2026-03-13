/**
 * Tabler Theme Component Initialization
 *
 * Centralizes component init: runs all component inits on DOMContentLoaded
 * and livewire:navigated. Components export init functions (ComponentInit);
 * this module orchestrates when they run.
 */

import type { ComponentInit } from './types';
import { init as initAnchor } from './components/anchor';
import { init as initVideoShowcase } from './components/video-showcase';
import { init as initFeatures } from './components/features';
import { init as initMediaSlider } from './components/media-slider';
import { init as initStoreLocations } from './components/store-locations';
import { init as initStoreLocationsDropdown } from './components/store-locations-dropdown';
import { init as initPhotoGallery } from './components/photo-gallery';
import { init as initProductVariantBlock } from './components/product-variant-block';
import { init as initDynamicFormInteractive } from './components/dynamic-form-interactive';
import { init as initTextDocumentCollapsible } from './components/text-document-collapsible';

const componentInits: ComponentInit[] = [
    initAnchor,
    initVideoShowcase,
    initFeatures,
    initMediaSlider,
    initStoreLocations,
    initStoreLocationsDropdown,
    initPhotoGallery,
    initProductVariantBlock,
    initDynamicFormInteractive,
    initTextDocumentCollapsible,
];

/**
 * Run all component initializers.
 */
export function initComponents(): void {
    componentInits.forEach((init) => {
        try {
            init();
        } catch (error) {
            console.error('[Tabler Theme] Component init error:', error);
        }
    });
}

function init(): void {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initComponents);
    } else {
        initComponents();
    }

    document.addEventListener('livewire:navigated', () => {
        initComponents();
    });
}

init();
