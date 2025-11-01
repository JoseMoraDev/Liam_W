import { checkAuth } from "~/store/auth";

export default defineNuxtPlugin((nuxtApp) => {
  // 🔹 En servidor (SSR) → inicializa si hay cookie (no usa composables todavía)
  if (import.meta.server) {
    return;
  }

  // 🔹 En cliente → espera a que Nuxt esté completamente montado
  nuxtApp.hook("app:mounted", async () => {
    try {
      const ok = await checkAuth();
      console.log("[auth.global] checkAuth ->", ok);
    } catch (error) {
      console.error("[auth.global] Error al inicializar sesión:", error);
    }
  });
});
