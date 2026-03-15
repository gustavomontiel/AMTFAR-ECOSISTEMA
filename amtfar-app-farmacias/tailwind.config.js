/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
  ],
  theme: {
    extend: {
      colors: {
        amtfar: {
          primary: '#008f39', /* Verde farmacia original */
          primary_dark: '#006126', /* Hover state */
          secondary: '#e8f5e9', /* Verde claro */
          accent: '#008f39', 
          background: '#f4f7f6', /* Gris suave */
          surface: '#ffffff',
          dark: '#1d1d1b' /* Texto oscuro institucional */
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
