import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import copy from 'rollup-plugin-copy';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.ts',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        copy({
            targets: [
                { src: 'resources/image_system', dest: 'public' },
                {
                    src: 'resources/sounds', dest: 'public'
                }
            ],
            verbose: true
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    build: {
        assetsInlineLimit: 0,
        rollupOptions: {
            output: {
                assetFileNames: function (file) {
                    return file.name.endsWith('.ttf') || file.name.endsWith('.woff2')
                        ? `assets/[name].[ext]`
                        : `assets/[name]-[hash].[ext]`;
                }
            }
        }
    }
});
