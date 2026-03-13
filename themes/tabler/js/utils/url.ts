/**
 * URL building utilities
 */

/**
 * Build full URL from base URL and relative path.
 * Returns the path unchanged if it is already absolute (http://, https://, //).
 *
 * @param path - Relative path or URI to resolve
 * @param baseUrl - Base URL (e.g. from data-asset-base-url)
 * @returns Full URL or empty string if path is empty
 */
export function buildUrl(path: string, baseUrl: string): string {
    if (!path || String(path).trim() === '') return '';
    if (/^(https?:)?\/\//.test(path)) return path;
    const base = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
    const normalizedPath = path.startsWith('/') ? path.slice(1) : path;
    return `${base}/${normalizedPath}`;
}
