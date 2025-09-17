<template>
  <nav
    class="fixed inset-x-0 top-0 z-[9999] h-16 flex items-center bg-gray-100/40 backdrop-blur-md border-b border-gray-300/40 shadow-sm"
  >
    <div class="w-full px-3 mx-auto max-w-7xl sm:px-6">
      <div class="flex items-center gap-3 px-3 py-2 rounded-2xl">
        <!-- Botón menú -->
        <button
          @click="open = !open"
          class="inline-flex items-center justify-center w-10 h-10 text-gray-700 transition border rounded-xl border-gray-300/50 hover:bg-gray-200/20"
          aria-label="Abrir menú"
        >
          <client-only
            ><font-awesome-icon icon="fa-solid fa-bars"
          /></client-only>
        </button>

        <!-- Marca -->
        <NuxtLink
          to="/"
          class="hidden font-semibold tracking-wide text-gray-800 sm:block"
        >
          Live Ambience
        </NuxtLink>

        <!-- Buscador -->
        <form
          @submit.prevent="onSearch"
          class="flex items-center justify-center flex-1"
        >
          <div class="relative w-full max-w-xl">
            <input
              v-model="q"
              type="search"
              placeholder="Buscar..."
              class="w-full pl-4 text-gray-800 border rounded-full h-11 pr-11 border-gray-300/50 bg-gray-50/50 placeholder:italic placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300/50 focus:border-gray-300/50"
            />
            <button
              type="submit"
              class="absolute inset-y-0 right-0 flex items-center justify-center text-gray-700 border-l rounded-r-full w-11 border-gray-300/50 hover:bg-gray-200/20"
              aria-label="Buscar"
            >
              <client-only
                ><font-awesome-icon icon="fa-solid fa-magnifying-glass"
              /></client-only>
            </button>
          </div>
        </form>

        <!-- Acciones -->
        <div class="items-center hidden gap-2 sm:flex">
          <NuxtLink
            to="/login"
            class="inline-flex items-center h-10 px-3 text-gray-700 border rounded-xl border-gray-300/50 hover:bg-gray-200/20"
          >
            Acceder
          </NuxtLink>
          <NuxtLink
            to="/register"
            class="inline-flex items-center h-10 px-3 text-gray-700 border rounded-xl border-gray-300/50 hover:bg-gray-200/20"
          >
            Crear cuenta
          </NuxtLink>
        </div>

        <!-- Móvil -->
        <NuxtLink
          to="/login"
          class="inline-flex items-center justify-center w-10 h-10 text-gray-700 border sm:hidden rounded-xl border-gray-300/50 hover:bg-gray-200/20"
          aria-label="Acceder"
        >
          <client-only
            ><font-awesome-icon icon="fa-regular fa-user"
          /></client-only>
        </NuxtLink>
      </div>
    </div>

    <!-- Panel móvil -->
    <transition name="fade">
      <div
        v-if="open"
        class="absolute p-4 space-y-2 text-gray-800 rounded-lg shadow-lg top-16 left-4 bg-gray-50/90 backdrop-blur-md w-max"
      >
        <!-- Menú de un solo nivel -->
        <NuxtLink
          to="/previsiones"
          class="flex items-center px-4 py-2 font-semibold uppercase rounded-lg hover:bg-gray-200/30"
          @click="open = false"
        >
          <client-only
            ><font-awesome-icon
              icon="fa-solid fa-cloud-sun"
              class="w-4 h-4 mr-2"
          /></client-only>
          Previsiones
        </NuxtLink>

        <NuxtLink
          to="/ajustes"
          class="flex items-center px-4 py-2 font-semibold uppercase rounded-lg hover:bg-gray-200/30"
          @click="open = false"
        >
          <client-only
            ><font-awesome-icon icon="fa-solid fa-sliders" class="w-4 h-4 mr-2"
          /></client-only>
          Ajustes
        </NuxtLink>

        <NuxtLink
          to="/avanzado"
          class="flex items-center px-4 py-2 font-semibold uppercase rounded-lg hover:bg-gray-200/30"
          @click="open = false"
        >
          <client-only
            ><font-awesome-icon icon="fa-solid fa-cogs" class="w-4 h-4 mr-2"
          /></client-only>
          Avanzado
        </NuxtLink>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref } from "vue";
const open = ref(false);
const q = ref("");

function onSearch() {
  if (!q.value.trim()) return;
  navigateTo({ path: "/search", query: { q: q.value.trim() } });
  open.value = false;
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
