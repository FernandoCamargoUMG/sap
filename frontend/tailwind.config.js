/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#0066cc',  // Azul CFAG
          600: '#0052a3',
          700: '#003d7a',
          800: '#002952',
          900: '#001429',
          950: '#000a14',
        },
        secondary: {
          50: '#fffbeb',
          100: '#fef3c7',
          200: '#fde68a',
          300: '#fcd34d',
          400: '#fbbf24',
          500: '#FFD700',  // Dorado CFAG
          600: '#d4af37',
          700: '#b8941f',
          800: '#997912',
          900: '#7a5f0a',
          950: '#5c4707',
        },
        accent: {
          50: '#fafafa',
          100: '#f5f5f5',
          200: '#e5e5e5',
          300: '#d4d4d4',
          400: '#a3a3a3',
          500: '#737373',
          600: '#525252',
          700: '#404040',
          800: '#262626',
          900: '#171717',
          950: '#0a0a0a',
        },
      },
      backgroundImage: {
        'gradient-cfag': 'linear-gradient(135deg, #0066cc 0%, #003d7a 100%)',
        'gradient-gold': 'linear-gradient(135deg, #FFD700 0%, #b8941f 100%)',
      },
    },
  },
  plugins: [],
}
