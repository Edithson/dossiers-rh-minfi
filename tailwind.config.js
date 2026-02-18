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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                // Réduire toutes les tailles de police de 10-15%
                'xs': '0.65rem',    // au lieu de 0.75rem
                'sm': '0.8rem',     // au lieu de 0.875rem
                'base': '0.9rem',   // au lieu de 1rem
                'lg': '1rem',       // au lieu de 1.125rem
                'xl': '1.1rem',     // au lieu de 1.25rem
                '2xl': '1.4rem',    // au lieu de 1.5rem
                '3xl': '1.7rem',    // au lieu de 1.875rem
                '4xl': '2rem',      // au lieu de 2.25rem
                '5xl': '2.7rem',    // au lieu de 3rem
            },
            spacing: {
                // Réduire les espacements de 10%
                '1': '0.225rem',
                '2': '0.45rem',
                '3': '0.675rem',
                '4': '0.9rem',
                '5': '1.125rem',
                '6': '1.35rem',
                '8': '1.8rem',
                '10': '2.25rem',
                '12': '2.7rem',
                '16': '3.6rem',
            }
        },
    },

    plugins: [forms],
};
