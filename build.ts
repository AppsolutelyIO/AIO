import { build, type InlineConfig } from 'vite';
import { resolve, basename } from 'path';
import { cpSync, copyFileSync, mkdirSync } from 'fs';
import { globSync } from 'glob';
import commonjs from '@rollup/plugin-commonjs';

const isProduction = process.env.NODE_ENV === 'production';
const outDir = isProduction ? 'resources/dist' : 'resources/pre-dist';

const dir = import.meta.dirname;

interface CssEntry {
    input: string;
    output: string;
}

// Shared SCSS options
const cssConfig: InlineConfig['css'] = {
    preprocessorOptions: {
        scss: {
            silenceDeprecations: ['import', 'global-builtin', 'if-function'],
        },
    },
};

// === JS/TS entries (build as IIFE) ===
const jsEntries: Record<string, string> = {
    'adminlte/adminlte': resolve(dir, 'resources/assets/adminlte/js/AdminLTE.ts'),
    'aio/js/aio-app': resolve(dir, 'resources/assets/aio/js/aio-app.ts'),
};

for (const f of globSync('resources/assets/aio/extra/*.ts')) {
    const name = basename(f, '.ts');
    if (name === 'markdown') continue; // CSS-only entry, handled below
    jsEntries[`aio/extra/${name}`] = resolve(dir, f);
}

// === CSS entries (SCSS → CSS) ===
const cssEntries: CssEntry[] = [
    { input: 'resources/assets/adminlte/scss/AdminLTE.scss', output: 'adminlte/adminlte.css' },
    { input: 'resources/assets/aio/sass/aio-app.scss', output: 'aio/css/aio-app.css' },
    { input: 'resources/assets/aio/extra/upload.scss', output: 'aio/extra/upload.css' },
    { input: 'resources/assets/aio/extra/markdown.scss', output: 'aio/extra/markdown.css' },
];

async function buildAll(): Promise<void> {
    // 1. Build all JS/TS entries as IIFE (no CSS extraction)
    for (const [name, entry] of Object.entries(jsEntries)) {
        console.log(`[JS]  ${name}`);
        await build({
            configFile: false,
            css: cssConfig,
            plugins: [commonjs()],
            build: {
                outDir,
                emptyOutDir: false,
                sourcemap: true,
                cssCodeSplit: false,
                rollupOptions: {
                    input: { [name]: entry },
                    output: {
                        format: 'iife',
                        inlineDynamicImports: true,
                        entryFileNames: '[name].js',
                        assetFileNames: '[name][extname]',
                    },
                },
            },
        });
    }

    // 2. Build CSS entries
    for (const { input, output } of cssEntries) {
        console.log(`[CSS] ${output}`);
        await build({
            configFile: false,
            css: cssConfig,
            plugins: [
                {
                    name: 'css-rename',
                    generateBundle(_options, bundle) {
                        for (const [key, asset] of Object.entries(bundle)) {
                            if (asset.type === 'asset' && key.endsWith('.css')) {
                                asset.fileName = output;
                            }
                            if (asset.type === 'chunk') {
                                delete bundle[key];
                            }
                        }
                    },
                },
            ],
            build: {
                outDir,
                emptyOutDir: false,
                sourcemap: true,
                rollupOptions: {
                    input: { [output.replace('.css', '')]: resolve(dir, input) },
                    output: {
                        format: 'es',
                        entryFileNames: '[name].js',
                        assetFileNames: '[name][extname]',
                    },
                },
            },
        });
    }

    // 3. Copy static assets
    console.log('[COPY] static assets');
    cpSync('resources/assets/images', `${outDir}/images`, { recursive: true });
    cpSync('resources/assets/fonts', `${outDir}/fonts`, { recursive: true });
    cpSync('resources/assets/aio/plugins', `${outDir}/aio/plugins`, { recursive: true });
    mkdirSync(`${outDir}/aio/css`, { recursive: true });
    copyFileSync('resources/assets/aio/sass/nunito.css', `${outDir}/aio/css/nunito.css`);

    console.log('Done.');
}

buildAll().catch((err: unknown) => {
    console.error(err);
    process.exit(1);
});
