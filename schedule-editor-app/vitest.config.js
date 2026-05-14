/// <reference types="vitest" />

import { defineConfig } from 'vitest/config'

export default defineConfig({
    test: {
        environment: "jsdom",
        globals: true,
        css: true,
        setupFiles: [],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'html'],
            reportsDirectory: './coverage',
            exclude: [
                'node_modules/',
                'dist/',
                '**/*.d.ts',
            ],
        },
    },
});