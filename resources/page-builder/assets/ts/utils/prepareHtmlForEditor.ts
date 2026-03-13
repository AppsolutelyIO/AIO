/**
 * Copy data-src to src for img, video, and source elements.
 * Does not modify visibility (opacity/class).
 *
 * @param html - HTML string (e.g. block output)
 * @returns HTML with src populated from data-src where applicable
 */
export function attachDataSrcToSrc(html: string): string {
    const trimmed = html.trim();
    if (trimmed === '') return html;
    if (typeof document === 'undefined') return html;

    const container = document.createElement('div');
    container.innerHTML = trimmed;

    container
        .querySelectorAll<
            HTMLImageElement | HTMLVideoElement | HTMLSourceElement
        >('img[data-src], video[data-src], source[data-src]')
        .forEach((el) => {
            const dataSrc = el.getAttribute('data-src');
            if (!dataSrc) return;
            const currentSrc = el.getAttribute('src');
            if (currentSrc !== null && currentSrc !== '') return;
            el.setAttribute('src', dataSrc);
        });

    return container.innerHTML;
}

/**
 * Add opacity: 1 and lazy-loaded class to img elements.
 * Use when displaying lazy images in contexts without lazy-loading JS.
 *
 * @param html - HTML string
 * @returns HTML with img elements made visible
 */
export function makeLazyImagesVisible(html: string): string {
    const trimmed = html.trim();
    if (trimmed === '') return html;
    if (typeof document === 'undefined') return html;

    const container = document.createElement('div');
    container.innerHTML = trimmed;

    container.querySelectorAll<HTMLImageElement>('img').forEach((el) => {
        el.classList.add('lazy-loaded');
        el.style.opacity = '1';
    });

    return container.innerHTML;
}

/**
 * Prepare lazy content for Page Builder editor/preview.
 * Combines attachDataSrcToSrc and makeLazyImagesVisible in one pass.
 *
 * @param html - HTML string (e.g. block output)
 * @returns HTML with src attached and images made visible
 */
export function prepareLazyContentForEditor(html: string): string {
    const trimmed = html.trim();
    if (trimmed === '') return html;
    if (typeof document === 'undefined') return html;

    const container = document.createElement('div');
    container.innerHTML = trimmed;

    container
        .querySelectorAll<
            HTMLImageElement | HTMLVideoElement | HTMLSourceElement
        >('img[data-src], video[data-src], source[data-src]')
        .forEach((el) => {
            const dataSrc = el.getAttribute('data-src');
            if (!dataSrc) return;
            const currentSrc = el.getAttribute('src');
            if (currentSrc !== null && currentSrc !== '') return;
            el.setAttribute('src', dataSrc);

            if (el.tagName.toLowerCase() === 'img') {
                el.classList.add('lazy-loaded');
                (el as HTMLImageElement).style.opacity = '1';
            }
        });

    return container.innerHTML;
}

/**
 * Attach data-src to src in a document (DOM, in-place).
 */
export function attachDataSrcToSrcInDocument(doc: Document): void {
    doc.querySelectorAll<HTMLImageElement | HTMLVideoElement | HTMLSourceElement>(
        'img[data-src], video[data-src], source[data-src]'
    ).forEach((el) => {
        const dataSrc = el.getAttribute('data-src');
        if (!dataSrc) return;
        const currentSrc = el.getAttribute('src');
        if (currentSrc !== null && currentSrc !== '') return;
        el.setAttribute('src', dataSrc);
    });
}

/**
 * Make lazy img elements visible in a document (DOM, in-place).
 */
export function makeLazyImagesVisibleInDocument(doc: Document): void {
    doc.querySelectorAll<HTMLImageElement>('img').forEach((el) => {
        el.classList.add('lazy-loaded');
        el.style.opacity = '1';
    });
}
