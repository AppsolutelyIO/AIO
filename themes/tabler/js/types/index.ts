/**
 * Tabler Theme Type Definitions
 */

/**
 * Component init function signature.
 * All theme components must export an init function matching this type.
 * Called by init.ts on DOMContentLoaded and livewire:navigated.
 */
export type ComponentInit = () => void;

// Photo gallery (from data-photos JSON)
export interface Photo {
    image_src: string;
    title?: string;
    subtitle?: string;
    description?: string;
    alt?: string;
    caption?: string;
    link?: string;
    category?: string;
    tags?: string[];
    price?: string;
}

export {};
