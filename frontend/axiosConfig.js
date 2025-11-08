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
    'Accept': 'application/json',
  },
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
});

function getCookie(name) {
  if (typeof document === 'undefined') return null;
  const match = document.cookie.match(new RegExp('(^|; )' + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
  return match ? decodeURIComponent(match[2]) : null;
}

axiosClient.interceptors.request.use((config) => {
  // Adjuntar token Bearer desde cookie 'token'
  const token = getCookie('token');
  if (token) {
    config.headers = config.headers || {};
    if (!config.headers.Authorization) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  }
  return config;
});