import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import utwm from 'unplugin-tailwindcss-mangle/vite';
import { defineConfig } from 'vite';
import { imagetools } from 'vite-imagetools'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.tsx'
            ],
            // ssr: 'resources/js/ssr.tsx',
            // buildDirectory: 'zergly',
            // hotFile: 'storage/zergly.hot',
            refresh: true,
            // transformOnServe: (code, devServerUrl) => code.replaceAll('/@imagetools', devServerUrl+'/@imagetools'),
        }),
        react(),
        tailwindcss(),
        imagetools(),
        utwm({
            classGenerator: {
                // log: true,
                customGenerate: () => {
                    const randomBase36 = (length: number) => [...Array(length)].map(() => Math.floor(Math.random() * 36).toString(36)).join('');

                    return `zergly-${randomBase36(6)}`;
                },
            },
        }),
    ],
    build: {
        // manifest: "zergly.json",
        rollupOptions: {
            output: {
                hashCharacters: 'base36',
                assetFileNames: 'assets/zergly-[hash:6][extname]',
                chunkFileNames: 'assets/zergly-[hash:6].js',
                entryFileNames: 'assets/zergly-[hash:6].js',
            },
        },
    },
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
});
