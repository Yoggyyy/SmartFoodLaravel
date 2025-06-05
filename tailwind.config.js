/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class', // Habilita modo oscuro manual con clase 'dark'
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./public/js/**/*.js"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
