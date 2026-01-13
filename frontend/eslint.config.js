import js from '@eslint/js';
import tsPlugin from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';
import vuePlugin from 'eslint-plugin-vue';
import prettier from 'eslint-config-prettier';

export default [
    js.configs.recommended,
    {
        files: ['**/*.{js,mjs,cjs,ts,vue}'],
        languageOptions: {
            parser: tsParser,
            parserOptions: {
                ecmaVersion: 'latest',
                sourceType: 'module',
            },
            globals: {
                console: 'readonly',
                process: 'readonly',
                Buffer: 'readonly',
                __dirname: 'readonly',
                __filename: 'readonly',
                module: 'readonly',
                require: 'readonly',
                exports: 'readonly',
                window: 'readonly',
                document: 'readonly',
                localStorage: 'readonly',
            },
        },
        plugins: {
            '@typescript-eslint': tsPlugin,
            vue: vuePlugin,
        },
        rules: {
            // TypeScript
            '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
            '@typescript-eslint/no-explicit-any': 'warn',
            '@typescript-eslint/explicit-function-return-type': 'off',
            
            // Vue
            'vue/multi-word-component-names': 'off',
            'vue/no-v-html': 'warn',
            'vue/require-default-prop': 'off',
            'vue/require-explicit-emits': 'warn',
            
            // General
            'no-console': ['warn', { allow: ['warn', 'error'] }],
            'no-debugger': 'warn',
            'prefer-const': 'error',
            'no-var': 'error',
        },
    },
    {
        files: ['**/*.vue'],
        languageOptions: {
            parser: vuePlugin.parsers['vue-eslint-parser'],
            parserOptions: {
                parser: tsParser,
            },
        },
    },
    prettier,
    {
        ignores: ['dist', 'node_modules', '*.config.js', '*.config.ts', '*.config.mjs'],
    },
];















