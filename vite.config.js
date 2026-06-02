import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/filament/admin-utilities.css',
                'themes/gazu/resources/css/gazu.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: [
                '**/backups/**',
                '**/storage/debugbar/**',
                '**/test-results/**',
                '**/playwright-report/**',
                '**/superdesign/**'
            ]
        }
    }
});
