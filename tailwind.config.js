import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

// Shared palette: orange, ink, rose, lime and sage.
const mix = (hex, target, weight) => '#' + [0, 2, 4].map(i => Math.round(parseInt(hex.slice(i, i + 2), 16) * (1 - weight) + parseInt(target.slice(i, i + 2), 16) * weight).toString(16).padStart(2, '0')).join('');
const scale = hex => Object.fromEntries([[50,.94],[100,.86],[200,.68],[300,.46],[400,.22],[500,0],[600,-.18],[700,-.36],[800,-.52],[900,-.68],[950,-.8]].map(([level, weight]) => [level, mix(hex, weight >= 0 ? 'ffffff' : '0b2027', Math.abs(weight))]));
const sage = scale('439575');
const orange = scale('a17c2c');
const rose = scale('b56971');
const lime = scale('439575');
const ink = {50:'#f5f7f7',100:'#e9eeee',200:'#d4dede',300:'#b2c2c4',400:'#81999e',500:'#59757c',600:'#425e65',700:'#30494f',800:'#203940',850:'#183037',900:'#102930',925:'#0e252c',950:'#0b2027'};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        // Scan source files, not temporary Blade caches removed by view:clear/cache.
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/View/Components/**/*.php',
    ],

    safelist: ['audit-tone-neutral', 'audit-tone-teal', 'audit-tone-red', 'audit-tone-blue', 'audit-tone-violet', 'audit-tone-amber'],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', '"Avenir Next"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: { ...scale('1e3a5f'), 600: '#2d517b', 700: '#1e3a5f', 800: '#19314f', 900: '#162e4b', 950: '#12263e' },
                slate: colors.slate,
                gold: orange,
                orange, amber: orange, yellow: orange,
                rose, red: rose, pink: rose,
                lime,
                green: sage, emerald: sage, teal: sage,
                blue: scale('587faf'), cyan: scale('587faf'), sky: scale('587faf'),
                indigo: scale('587faf'), purple: scale('8062aa'), violet: scale('8062aa'),
                palette: { orange: '#ffa630', ink: '#0b2027', rose: '#ef476f', lime: '#c7ef00', sage: '#53917e' },
            },
            boxShadow: {
                card: '0 1px 2px 0 rgba(16,26,41,0.04), 0 1px 3px 0 rgba(16,26,41,0.04)',
                'card-md': '0 6px 16px -4px rgba(16,26,41,0.08), 0 2px 6px -2px rgba(16,26,41,0.05)',
                'card-lg': '0 16px 32px -8px rgba(16,26,41,0.12), 0 6px 12px -4px rgba(16,26,41,0.06)',
            },
            borderRadius: {
                xl: '0.625rem',
                '2xl': '0.625rem',
                '3xl': '0.75rem',
            },
            letterSpacing: {
                tightest: '-0.03em',
            },
        },
    },

    plugins: [forms],
};
