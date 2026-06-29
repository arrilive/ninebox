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
            colors: {
                canvas:   '#ffffff',
                surface:  '#f7f6f3',
                border:   '#e9e8e4',
                ink:      '#191918',
                'ink-2':  '#6b6b6b',
                'ink-3':  '#a3a3a3',
                primary:       '#2563eb',
                'primary-hover':'#1d4ed8',
                success:  '#16a34a',
                warning:  '#d97706',
                danger:   '#dc2626',
                'nb-high':    '#16a34a',
                'nb-mid-high':'#22c55e',
                'nb-mid':     '#ca8a04',
                'nb-low-mid': '#dc2626',
                'nb-low':     '#991b1b',
            },
        },
    },

    plugins: [forms],
};
