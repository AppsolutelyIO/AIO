import { existsSync, readdirSync, readFileSync, realpathSync } from 'fs';
import path from 'path';

/**
 * Read a simple KEY=value from the .env file in the given directory.
 */
function loadEnvValue(dir: string, key: string): string | undefined {
    const envPath = path.join(dir, '.env');
    if (!existsSync(envPath)) {
        return undefined;
    }
    const match = readFileSync(envPath, 'utf-8').match(new RegExp(`^${key}=(.+)$`, 'm'));
    return match?.[1]?.trim();
}

/**
 * Read the package name from composer.json in the given directory.
 */
function getComposerPackageName(dir: string): string | undefined {
    const composerPath = path.join(dir, 'composer.json');
    if (!existsSync(composerPath)) {
        return undefined;
    }
    try {
        const json = JSON.parse(readFileSync(composerPath, 'utf-8'));
        return json.name as string | undefined;
    } catch {
        return undefined;
    }
}

/**
 * Find the host Laravel application's public directory.
 *
 * Resolution order:
 * 1. LARAVEL_PUBLIC_PATH env var or .env file (explicit override)
 * 2. Traverse up from CWD looking for artisan (works when running from
 *    the Laravel app root, or from within vendor/package-name)
 * 3. Scan sibling directories for a Laravel app whose vendor/ contains
 *    this package (local dev with composer path repository). Only used
 *    when exactly one sibling matches.
 */
export function findPublicDirectory(): string {
    const cwd = process.cwd();

    // Strategy 1: explicit override via env var or .env file
    const envValue = process.env.LARAVEL_PUBLIC_PATH ?? loadEnvValue(cwd, 'LARAVEL_PUBLIC_PATH');
    if (envValue) {
        return envValue;
    }

    // Strategy 2: traverse up looking for artisan
    let dir = cwd;
    while (true) {
        if (existsSync(path.join(dir, 'artisan'))) {
            const rel = path.relative(cwd, dir);
            return rel ? path.join(rel, 'public') : 'public';
        }
        const parent = path.dirname(dir);
        if (parent === dir) break;
        dir = parent;
    }

    // Strategy 3: find sibling Laravel apps that require this package
    const packageName = getComposerPackageName(cwd);
    if (packageName) {
        const parentDir = path.dirname(cwd);
        const cwdReal = realpathSync(cwd);
        const matches: string[] = [];

        try {
            for (const entry of readdirSync(parentDir, { withFileTypes: true })) {
                if (!entry.isDirectory() || entry.name === path.basename(cwd)) {
                    continue;
                }

                const candidate = path.join(parentDir, entry.name);
                if (!existsSync(path.join(candidate, 'artisan'))) {
                    continue;
                }

                const vendorLink = path.join(candidate, 'vendor', packageName);
                if (existsSync(vendorLink) && realpathSync(vendorLink) === cwdReal) {
                    matches.push(candidate);
                }
            }
        } catch {
            // parentDir not readable — fall through
        }

        if (matches.length === 1) {
            return path.relative(cwd, path.join(matches[0], 'public'));
        }

        if (matches.length > 1) {
            const names = matches.map((m) => path.basename(m)).join(', ');
            throw new Error(
                `Found multiple Laravel apps referencing this package: ${names}.\n` +
                    'Set LARAVEL_PUBLIC_PATH in .env to specify which one.\n' +
                    'Example: LARAVEL_PUBLIC_PATH=../site/public',
            );
        }
    }

    throw new Error(
        'Cannot find Laravel application.\n' +
            'Run from the Laravel app root, or set LARAVEL_PUBLIC_PATH in .env.\n' +
            'Example: LARAVEL_PUBLIC_PATH=../site/public',
    );
}
