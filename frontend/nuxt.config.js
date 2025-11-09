export default defineNuxtConfig({
  compatibilityDate: '2025-09-04',
  css: ['@/assets/css/tailwind.css', '@/assets/css/theme.css'],
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },
  modules: [
    '@nuxtjs/i18n',
  ],
  vite: {
    resolve: {
      alias: {
        'form-data': false, // no usar form-data
      },
    },
  },
  i18n: {
    strategy: 'no_prefix',
    defaultLocale: 'es',
    lazy: true,
    langDir: 'locales',
    detectBrowserLanguage: false,
    locales: [
      { code: 'es', name: 'Español', file: 'es.json', dir: 'ltr' },
      { code: 'ca', name: 'Català', file: 'ca.json', dir: 'ltr' },
      { code: 'val', name: 'Valencià', file: 'val.json', dir: 'ltr' },
      { code: 'gl', name: 'Galego', file: 'gl.json', dir: 'ltr' },
      { code: 'eu', name: 'Euskara', file: 'eu.json', dir: 'ltr' },
      { code: 'ary', name: 'العربية المغربية', file: 'ary.json', dir: 'rtl' },
    ],
    vueI18n: './i18n.config.ts',
  },
  nitro: {
    devProxy: {
      '/api': {
        target: 'http://localhost:8000/api',
        changeOrigin: true,
      },
    },
  },
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || '/api',
    },
  },
})
