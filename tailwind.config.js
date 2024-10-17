/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.{html,js,php}", // This will match all .html, .js, and .php files in the main directory
    "./components/**/*.{html,js,php}", // This will continue to match files in the src directory
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: "#faf7fc",
          100: "#f4edfa",
          200: "#e8dbf3",
          300: "#d8bee9",
          400: "#c297db",
          500: "#a66ec7",
          600: "#8b4faa",
          700: "#733f8c",
          800: "#603573",
          900: "#482a54",
          950: "#31163c",
        },
      },
    },
  },
  plugins: [require("@tailwindcss/forms")],
};
