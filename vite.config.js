import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/undc-market-home.css',
                'resources/css/undc-orders.css',
                'resources/css/undc-tienda.css',
                'resources/js/app.js',
                'resources/js/undc-hero.js',
                'resources/js/undc-market-home.js',
                'resources/js/undc-orders.js',
                'resources/js/undc-store-motion.js',
                'resources/js/undc-tienda.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
