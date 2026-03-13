import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default defineConfig({
    base: '/build/themes/tabler',
    plugins: [
        laravel({
            input: ['themes/tabler/sass/app.scss', 'themes/tabler/js/app.ts'],
            buildDirectory: 'build/themes/tabler',
        }),
        {
            name: 'blade',
            hotUpdate({ file, server }) {
                if (file.endsWith('.blade.php')) {
                    server.ws.send({
                        type: 'full-reload',
                        path: '*',
                    });
                }
            },
        },
    ],
    resolve: {
        alias: {
            '@tabler-theme': path.resolve(__dirname),
            '~@tabler/core': path.resolve('node_modules/@tabler/core'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5179,
        strictPort: true,
        hmr: {
            host: 'localhost',
            protocol: 'ws',
        },
        cors: {
            origin: true,
            methods: ['GET', 'HEAD', 'PUT', 'PATCH', 'POST', 'DELETE'],
            credentials: true,
        },
    },
    build: {
        assetsInlineLimit: 0,
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    if (!assetInfo.name) return 'assets/[name].[hash][extname]';
                    const ext = assetInfo.name.split('.').pop();
                    if (ext && ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'].includes(ext)) {
                        return 'images/[name].[hash][extname]';
                    }
                    if (ext && ['woff2', 'woff', 'ttf'].includes(ext)) {
                        return 'fonts/[name].[hash][extname]';
                    }
                    return 'assets/[name].[hash][extname]';
                },
            },
        },
    },
});
