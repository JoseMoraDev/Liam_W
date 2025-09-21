import { userLoggedIn, checkAuth } from '~/store/auth';

export default defineNuxtRouteMiddleware(async (to, from) => {
  if (import.meta.client) {
    const valid = await checkAuth();

    if (to.path === '/main' && !valid) {
      return navigateTo('/login');
    }

    if ((to.path === '/login' || to.path === '/register') && userLoggedIn.value) {
      return navigateTo('/main');
    }
  }
});
