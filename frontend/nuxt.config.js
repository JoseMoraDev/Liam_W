export default defineNuxtConfig({
  compatibilityDate: '2025-09-04',
  css: ['@/assets/css/tailwind.css'],
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },
  vite: {
    resolve: {
      alias: {
        'form-data': false, // no usar form-data
      },
    },
  },
})
