import { existsSync } from 'fs';
import path from 'path';

/**
 * Find the host Laravel application's public directory.
 *
 * Traverses up from CWD looking for an artisan file (Laravel app root).
 * Works when running from the Laravel app root, or from within
 * vendor/appsolutely/aio (installed via composer).
 *
 * Set LARAVEL_PUBLIC_PATH env var to override.
 */
export function findPublicDirectory(): string {
    if (process.env.LARAVEL_PUBLIC_PATH) {
        return process.env.LARAVEL_PUBLIC_PATH;
    }

    let dir = process.cwd();
    while (true) {
        if (existsSync(path.join(dir, 'artisan'))) {
            const rel = path.relative(process.cwd(), dir);
            return rel ? path.join(rel, 'public') : 'public';
        }
        const parent = path.dirname(dir);
        if (parent === dir) break;
        dir = parent;
    }

    throw new Error(
        'Cannot find Laravel application.\n' +
            'Run from the Laravel app root, or set LARAVEL_PUBLIC_PATH env var.',
    );
}
