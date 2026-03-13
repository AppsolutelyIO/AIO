/**
 * Escape utilities for safe HTML output (prevents XSS).
 */

/**
 * Escape string for safe use in HTML text content.
 * Use when inserting user-controlled data into element text or HTML body.
 */
export function escapeHtml(text: string): string {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Escape string for safe use in HTML attributes.
 * Use when inserting user-controlled data into attribute values (src, alt, class, etc.).
 */
export function escapeHtmlAttr(text: string): string {
    return text
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
