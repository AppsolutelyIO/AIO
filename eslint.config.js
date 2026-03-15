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
            'resources/agent-config/**',
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

    // AIO legacy jQuery code — relaxed rules for migrated JS-to-TS files
    {
        files: ['resources/assets/aio/**/*.{ts,js}'],
        rules: {
            '@typescript-eslint/no-explicit-any': 'off',
            '@typescript-eslint/no-this-alias': 'off',
            '@typescript-eslint/no-unused-vars': 'off',
            '@typescript-eslint/no-unused-expressions': 'off',
            '@typescript-eslint/consistent-type-imports': 'off',
            'prefer-const': 'off',
            'prefer-rest-params': 'off',
            'no-var': 'off',
            'no-console': 'off',
            'no-empty': 'off',
            'no-undef': 'off',
            'no-prototype-builtins': 'off',
            'no-useless-escape': 'off',
            'no-useless-assignment': 'off',
            eqeqeq: 'off',
            'perfectionist/sort-imports': 'off',
            'perfectionist/sort-named-imports': 'off',
        },
    },

    // Prettier — must be last to disable conflicting formatting rules
    prettierConfig,
);
