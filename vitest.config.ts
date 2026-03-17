import { resolve } from 'path';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        globals: true,
        environment: 'jsdom',
        setupFiles: ['./tests/frontend/setup.ts'],
        include: ['tests/frontend/**/*.{test,spec}.{ts,tsx}', 'resources/**/*.{test,spec}.{ts,tsx}'],
        exclude: ['node_modules', 'vendor', 'storage', 'public'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'json', 'html'],
            exclude: ['node_modules/', 'vendor/', 'tests/', '**/*.d.ts', '**/*.config.*', '**/types/**'],
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, './resources/page-builder/assets'),
            '@resources': resolve(__dirname, './resources'),
        },
    },
});
