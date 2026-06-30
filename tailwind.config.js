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
                sans: ['Inter', 'ui-sans-serif', 'system-ui'],
            },
            colors: {
                // Superficies (60% — fondos, cards)
                canvas:   '#ffffff',
                surface:  '#f4f5f7',
                border:   '#e2e4e9',

                // Tipografía
                ink:      '#1a1d23',
                'ink-2':  '#5e6470',
                'ink-3':  '#9ba3af',

                // Acción principal (30%)
                primary:        '#1e40af',
                'primary-light':'#dbeafe',
                'primary-hover':'#1e3a8a',

                // Estados semánticos (10%)
                success: '#16a34a',
                warning: '#d97706',
                danger:  '#dc2626',
                'success-bg': '#dcfce7',
                'warning-bg': '#fef3c7',
                'danger-bg':  '#fee2e2',

                // Nine-Box
                'nb-1': '#7f1d1d',
                'nb-2': '#b91c1c',
                'nb-3': '#c2410c',
                'nb-4': '#b45309',
                'nb-5': '#854d0e',
                'nb-6': '#3f6212',
                'nb-7': '#166534',
                'nb-8': '#15803d',
                'nb-9': '#14532d',
            },
            borderRadius: {
                DEFAULT: '4px',
                md: '8px',
                lg: '12px',
            },
            boxShadow: {
                card: '0 1px 3px 0 rgba(0,0,0,0.07), 0 1px 2px -1px rgba(0,0,0,0.04)',
            },
        },
    },

    plugins: [forms],
};
