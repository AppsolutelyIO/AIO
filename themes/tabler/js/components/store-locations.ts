/**
 * Store Locations Component
 * Provides smart map opening functionality based on device platform
 */

/**
 * Opens a map application intelligently based on the user's device and platform.
 *
 * @param lat - Latitude coordinate of the location
 * @param lng - Longitude coordinate of the location
 * @param name - Optional name/label for the location
 */
export function openSmartMap(lat: number, lng: number, name: string = ''): void {
    const ua = navigator.userAgent.toLowerCase();
    const encodedName = encodeURIComponent(name || `${lat},${lng}`);
    const appleURL = `http://maps.apple.com/?ll=${lat},${lng}&q=${encodedName}`;
    const googleAppURL = `comgooglemaps://?q=${encodedName}&center=${lat},${lng}&zoom=14`;
    const googleWebURL = `https://www.google.com/maps?q=${encodedName}&ll=${lat},${lng}`;

    if (/iphone|ipad|mac/.test(ua)) {
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = googleAppURL;
        document.body.appendChild(iframe);

        setTimeout(() => {
            if (document.body.contains(iframe)) {
                document.body.removeChild(iframe);
                window.location.href = appleURL;
            }
        }, 1200);
    } else if (/android/.test(ua)) {
        window.location.href = `geo:${lat},${lng}?q=${encodedName}`;
    } else {
        window.location.href = googleWebURL;
    }
}

// Make function globally accessible
window.openSmartMap = openSmartMap;

export function init(): void {
    const mapLinks = document.querySelectorAll<HTMLAnchorElement>('[data-map-lat][data-map-lng]');
    mapLinks.forEach((link) => {
        const lat = parseFloat(link.getAttribute('data-map-lat') || '0');
        const lng = parseFloat(link.getAttribute('data-map-lng') || '0');
        const name = link.getAttribute('data-map-name') || '';

        if (Number.isFinite(lat) && Number.isFinite(lng)) {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                openSmartMap(lat, lng, name);
            });
        }
    });
}

export default openSmartMap;
