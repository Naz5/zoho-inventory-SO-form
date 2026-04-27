/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        zoho: {
          blue: '#1351be',
          dark: '#1a1d21',
          light: '#f9f9f9',
          border: '#e1e1e1',
          text: '#212121',
          muted: '#616161'
        }
      }
    },
  },
  plugins: [],
}
