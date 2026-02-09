import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Instrument Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'market-green': {
                    DEFAULT: '#16a34a',
                    hover: '#15803d',
                },
            },
        },
    },

    plugins: [forms],
};
