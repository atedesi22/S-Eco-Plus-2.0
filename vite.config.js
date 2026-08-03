import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],

    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
        cors: true,
        allowedHosts: ['.ngrok-free.app'], // Autorise tous les sous-domaines ngrok
    },
});
