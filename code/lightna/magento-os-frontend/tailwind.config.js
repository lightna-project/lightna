/** @type {import('tailwindcss').Config} */
import { animations, keyframes } from './css/animations';

module.exports = {
    content: [
        '../../code/lightna/*/template/**/*.phtml',
        '../../code/lightna/*/layout/*.yaml',
        '../../code/lightna/*/js/*.js',
    ],
    theme: {
        fontFamily: {
            sans: ['Assistant', 'sans-serif'],
        },
        extend: {
            colors: {
                primary: {
                    main: '#222222',
                    alt: '#997E49',
                    text: '#272525',
                    bg: '#FAF8F8',
                },
                hover: {
                    main: '#3B3B3B',
                },
                message: {
                    error: {
                        text: '#C02D33',
                        bg: '#F9D6D7'
                    },
                    success: {
                        text: '#339F3F',
                        bg: '#DFF0D8'
                    },
                    warning: {
                        text: '#FDB91A',
                        bg: '#FFF3D9'
                    },
                    notice: {
                        text: '#5375AA',
                        bg: '#D6E2FF'
                    }
                },
            },
            animation: animations,
            keyframes: keyframes,
        },
    },
    plugins: [],
}
