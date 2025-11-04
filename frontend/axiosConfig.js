import axios from 'axios';

export const axiosClient = axios.create({
  baseURL: (typeof window === 'undefined')
    ? (process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api')
    : (
      window?.__NUXT__?.config?.public?.apiBase ||
      process.env.NUXT_PUBLIC_API_BASE ||
      `http://${window.location.hostname}:8000/api`
    ),
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  },
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
});