/** @type {import('tailwindcss').Config} */
const colors = require('tailwindcss/colors');
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue'
    ],

    theme: {
        colors: {
            ...colors,
            primary: '#191E2C',
            secondary: '#2C3345',
            gray: '#E2E8F0'
        },
        screens: {
            sm: '480px',
            md: '768px',
            lg: '1024px',
            xl: '1440px'
        },
        extend: {
            fontFamily: {
                sans: ['Poppins', 'ui-sans-serif', 'system-ui']
            }
        }
    },

    plugins: []
};
