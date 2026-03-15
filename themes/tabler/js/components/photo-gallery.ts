// Photo Gallery client-side filtering

import type { Photo } from '../types';

import { buildUrl } from '../utils/url';

export function init(): void {
    const grid = document.getElementById('gallery-grid');
    if (!grid) return;

    const section = grid.closest<HTMLElement>('.photo-gallery');
    const baseUrl = section?.getAttribute('data-asset-base-url') || '/assets/';

    const PLACEHOLDER_URL = 'https://placehold.co/500x500?text=Coming+Soon';

    const photos: Photo[] = (() => {
        try {
            const photosData = grid.getAttribute('data-photos');
            return photosData ? JSON.parse(photosData) : [];
        } catch {
            return [];
        }
    })();

    const toTags = (photo: Photo): string[] => {
        if (Array.isArray(photo.tags)) {
            return photo.tags.filter(Boolean).map(String);
        }
        if (typeof photo.category === 'string' && photo.category.trim() !== '') {
            return [photo.category.trim()];
        }
        return [];
    };

    // Build category order based on first occurrence in photos array
    const categoryOrder: string[] = [];
    const seenCategories = new Set<string>();

    photos.forEach((photo: Photo) => {
        const categories = toTags(photo);
        categories.forEach((category: string) => {
            if (!seenCategories.has(category)) {
                categoryOrder.push(category);
                seenCategories.add(category);
            }
        });
    });

    const tags = categoryOrder;

    const filtersEl = document.getElementById('gallery-filters');
    const cardTpl = document.getElementById('gallery-card-template') as HTMLTemplateElement | null;

    const buildCard = (p: Photo): DocumentFragment => {
        if (!cardTpl) {
            return document.createDocumentFragment();
        }

        const node = cardTpl.content.firstElementChild?.cloneNode(true) as HTMLElement;
        if (!node) {
            return document.createDocumentFragment();
        }

        const img = node.querySelector<HTMLImageElement>('img');
        const title = node.querySelector<HTMLElement>('.card-title');
        const subtitle = node.querySelector<HTMLElement>('.card-subtitle');
        const text = node.querySelector<HTMLElement>('.card-text');
        const price = node.querySelector<HTMLElement>('.card-price');

        // Use placeholder when image_src is empty or missing; also handle load errors
        if (img) {
            const imageUrl =
                p.image_src && String(p.image_src).trim() !== '' ? buildUrl(p.image_src, baseUrl) : PLACEHOLDER_URL;
            img.setAttribute('data-src', imageUrl);
            img.alt = p.alt || p.title || '';
            img.addEventListener(
                'error',
                () => {
                    if (img.getAttribute('data-src') !== PLACEHOLDER_URL) {
                        img.setAttribute('data-src', PLACEHOLDER_URL);
                        // Trigger lazy loading update if available
                        if (window.lazyManager) {
                            window.lazyManager.update();
                        }
                    }
                },
                { once: true },
            );
        }

        if (title) title.textContent = p.title || '';
        if (subtitle) subtitle.textContent = p.subtitle || '';

        // prefer description from JSON, fallback to caption
        if (text) text.innerHTML = p.description || p.caption || '';

        // Price label with graceful hide when absent
        if (price) {
            if (p.price && String(p.price).trim() !== '') {
                price.innerHTML = `<span class="text-muted me-1">Price:</span> ${String(p.price).trim()}`;
                price.classList.remove('d-none');
            } else {
                price.textContent = '';
                price.classList.add('d-none');
            }
        }

        if (p.link) {
            const linkWrap = document.createElement('a');
            linkWrap.href = p.link;
            linkWrap.className = 'stretched-link';
            const card = node.querySelector('.card');
            if (card) {
                card.appendChild(linkWrap);
            }
        }

        // for client-side filtering
        const tagsList = toTags(p);
        if (tagsList.length) {
            node.firstElementChild?.setAttribute('data-tags', tagsList.join(','));
        }

        const fragment = document.createDocumentFragment();
        fragment.appendChild(node);
        return fragment;
    };

    const renderGrid = (filter: string = 'all'): void => {
        grid.innerHTML = '';
        let list =
            filter === 'all'
                ? photos
                : photos.filter((p: Photo) => {
                      const t = toTags(p);
                      return t.includes(filter);
                  });

        // Sort photos by category order when displaying
        list = list.sort((a: Photo, b: Photo) => {
            const aCategory = a.category || '';
            const bCategory = b.category || '';
            const aIndex = categoryOrder.indexOf(aCategory);
            const bIndex = categoryOrder.indexOf(bCategory);

            // If both are in the defined order, sort by their position
            if (aIndex !== -1 && bIndex !== -1) {
                return aIndex - bIndex;
            }
            // If only one is in the defined order, prioritize it
            if (aIndex !== -1) return -1;
            if (bIndex !== -1) return 1;
            // If neither is in the defined order, sort alphabetically
            return aCategory.localeCompare(bCategory);
        });

        list.forEach((p: Photo) => grid.appendChild(buildCard(p)));

        // Update lazy loading for newly added images
        if (window.lazyManager) {
            window.lazyManager.update();
        }
    };

    const renderFilters = (): void => {
        if (!filtersEl) return;

        const makeBtn = (label: string, value: string, active: boolean): HTMLButtonElement => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `btn btn-lg px-4 py-2 ${active ? 'btn-dark' : 'btn-outline-dark'}`;
            btn.textContent = label;
            btn.setAttribute('data-filter', value);
            btn.setAttribute('aria-pressed', String(active));
            btn.addEventListener('click', () => {
                // toggle active
                filtersEl.querySelectorAll<HTMLButtonElement>('button').forEach((b: HTMLButtonElement) => {
                    const isActive = b === btn;
                    b.classList.toggle('btn-dark', isActive);
                    b.classList.toggle('btn-outline-dark', !isActive);
                    b.setAttribute('aria-pressed', String(isActive));
                });
                renderGrid(value);
            });
            return btn;
        };

        // Clear & add buttons
        filtersEl.innerHTML = '';
        filtersEl.appendChild(makeBtn('All', 'all', true));
        tags.forEach((t: string) => filtersEl.appendChild(makeBtn(t, t, false)));
    };

    renderFilters();
    renderGrid('all');
}
