/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
  ],
  theme: {
    extend: {
      colors: {
        'amtfar-primary': '#0f766e', 
        'amtfar-secondary': '#047857', 
        'amtfar-background': '#f8fafc',
        'amtfar-accent': '#f59e0b',
      }
    },
  },
  plugins: [],
}
