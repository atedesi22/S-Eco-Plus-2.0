import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/View/PagesBackground.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'localhost',
        },
        cors: true,
        allowedHosts: ['.ngrok-free.app'], // Autorise tous les sous-domaines ngrok
    },

    plugins: [forms],
};
