<template>
  <div
    class="w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg')"
  >
    <!-- Capa translúcida -->
    <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>

    <!-- Contenido principal -->
    <div
      :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 overflow-y-auto transition-opacity duration-300 md:p-8"
    >
      <!-- Título -->
      <div
        class="w-full max-w-md p-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h1 class="text-2xl font-bold text-center text-gray-900/90">
          Módulo de Administración
        </h1>
      </div>

      <!-- GESTIÓN DE USUARIOS -->
      <div
        class="w-full max-w-md p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">
          Gestión de Usuarios
        </h2>

        <div class="grid grid-cols-1 gap-4">
          <NuxtLink to="/admin/usuarios" class="sub-card">
            Administrar usuarios
          </NuxtLink>
        </div>
      </div>

      <!-- APARIENCIA GENERAL -->
      <div
        class="w-full max-w-md p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">
          Apariencia General
        </h2>

        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/admin/fuentes" class="sub-card">
            Fuente por defecto
          </NuxtLink>
          <NuxtLink to="/admin/temas" class="sub-card">
            Temas de colores
          </NuxtLink>
        </div>
      </div>

      <!-- LOGS Y AUDITORÍA -->
      <div
        class="w-full max-w-md p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">
          Logs y Auditoría
        </h2>

        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/admin/logins" class="sub-card">
            Historial de logins
          </NuxtLink>
          <NuxtLink to="/admin/estadisticas" class="sub-card">
            Estadísticas
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const mounted = ref(false);

onMounted(() => {
  mounted.value = true;
});
</script>

<style scoped>
.sub-card {
  @apply flex items-center justify-center p-4 text-sm font-semibold text-center text-gray-900/90 transition-colors rounded-xl bg-gray-100/20 backdrop-blur-md hover:bg-gray-200/30 shadow-inner;
}
</style>
