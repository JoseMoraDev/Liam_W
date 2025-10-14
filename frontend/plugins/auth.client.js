import { checkAuth } from '~/store/auth';

export default defineNuxtPlugin(async (nuxtApp) => {
    await checkAuth();
});