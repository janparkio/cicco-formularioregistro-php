/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./index.php", "./pages/**/*.php", "./components/**/*.php", "./lib/**/*.js"],
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
        // primary: {
        //   50: "#EBE5F0",
        //   100: "#DDD3E5",
        //   200: "#C2B0D0",
        //   300: "#A78DBC",
        //   400: "#8C69A7",
        //   500: "#705088",
        //   600: "#583F6B",
        //   700: "#402E4E",
        //   800: "#291D31",
        //   900: "#110C14",
        //   950: "#050306",
        // },
      },
    },
  },
  plugins: [require("@tailwindcss/forms")],
};
