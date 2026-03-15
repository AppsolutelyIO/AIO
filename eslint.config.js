import js from '@eslint/js';
import tseslint from 'typescript-eslint';
import globals from 'globals';
import perfectionist from 'eslint-plugin-perfectionist';
import prettierConfig from 'eslint-config-prettier';

export default tseslint.config(
    // Ignore patterns
    {
        ignores: [
            'node_modules/**',
            'vendor/**',
            'storage/**',
            'bootstrap/cache/**',
            'resources/dist/**',
            'resources/pre-dist/**',
            'resources/assets/aio/plugins/**',
            '.agents/**',
            '_ide_helper.php',
            '_ide_helper_models.php',
            '*.config.js',
            'build.ts',
            'themes.ts',
        ],
    },

    // Base JavaScript configuration
    js.configs.recommended,

    // TypeScript recommended rules
    ...tseslint.configs.recommended,

    // TypeScript files configuration
    {
        files: ['**/*.ts', '**/*.tsx'],
        languageOptions: {
            parserOptions: {
                ecmaVersion: 'latest',
                sourceType: 'module',
            },
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
        rules: {
            // TypeScript specific rules
            '@typescript-eslint/no-explicit-any': 'warn',
            '@typescript-eslint/consistent-type-imports': [
                'error',
                { prefer: 'type-imports', fixStyle: 'inline-type-imports', disallowTypeAnnotations: false },
            ],
            '@typescript-eslint/no-unused-expressions': [
                'error',
                { allowShortCircuit: true, allowTaggedTemplates: true },
            ],
            '@typescript-eslint/no-this-alias': ['error', { allowedNames: ['self'] }],
            'no-unused-vars': 'off',
            '@typescript-eslint/no-unused-vars': [
                'error',
                {
                    argsIgnorePattern: '^_',
                    varsIgnorePattern: '^_',
                    caughtErrorsIgnorePattern: '^_',
                },
            ],

            // General code quality
            'no-console': ['warn', { allow: ['warn', 'error', 'info'] }],
            'prefer-const': 'error',
            'no-var': 'error',
            eqeqeq: ['error', 'always', { null: 'ignore' }],
        },
    },

    // Import sorting (all file types)
    {
        plugins: {
            perfectionist,
        },
        rules: {
            'perfectionist/sort-imports': [
                'error',
                {
                    type: 'alphabetical',
                    order: 'asc',
                    ignoreCase: true,
                    newlinesBetween: 1,
                    internalPattern: ['^@/.*', '^@themes/.*', '^@aio/.*'],
                    groups: [
                        'type-import',
                        ['value-builtin', 'value-external'],
                        'type-internal',
                        'value-internal',
                        ['type-parent', 'type-sibling', 'type-index'],
                        ['value-parent', 'value-sibling', 'value-index'],
                        'unknown',
                    ],
                },
            ],
            'perfectionist/sort-named-imports': [
                'error',
                {
                    type: 'alphabetical',
                    order: 'asc',
                    ignoreCase: true,
                },
            ],
        },
    },

    // Prettier — must be last to disable conflicting formatting rules
    prettierConfig,
);
