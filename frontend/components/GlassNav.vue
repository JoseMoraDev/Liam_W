<!-- components/GlassNav.vue -->
<template>
  <nav
    class="fixed inset-x-0 top-0 z-[9999] h-16 flex items-center bg-black/40 backdrop-blur-md border-b"
  >
    <div class="w-full px-3 mx-auto max-w-7xl sm:px-6">
      <div class="flex items-center gap-3 px-3 py-2 rounded-2xl">
        <!-- Botón menú -->
        <button
          @click="open = !open"
          class="inline-flex items-center justify-center w-10 h-10 text-gray-200 transition border rounded-xl border-gray-400/60 hover:bg-white/10"
          aria-label="Abrir menú"
        >
          <font-awesome-icon icon="fa-solid fa-bars" />
        </button>

        <!-- Marca -->
        <NuxtLink
          to="/"
          class="hidden font-semibold tracking-wide text-gray-100 sm:block"
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
              placeholder="Escribe aquí lo que quieres encontrar"
              class="w-full pl-4 text-gray-100 border rounded-full h-11 pr-11 border-gray-400/60 bg-white/10 placeholder:italic placeholder:text-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300/70 focus:border-gray-300/70"
            />
            <button
              type="submit"
              class="absolute inset-y-0 right-0 flex items-center justify-center border-l rounded-r-full w-11 border-gray-400/60 text-gray-100/90 hover:bg-white/10"
              aria-label="Buscar"
            >
              <font-awesome-icon icon="fa-solid fa-magnifying-glass" />
            </button>
          </div>
        </form>

        <!-- Acciones -->
        <div class="items-center hidden gap-2 sm:flex">
          <NuxtLink
            to="/login"
            class="inline-flex items-center h-10 px-3 text-gray-200 border rounded-xl border-gray-400/60 hover:bg-white/10"
          >
            Acceder
          </NuxtLink>
          <NuxtLink
            to="/register"
            class="inline-flex items-center h-10 px-3 text-gray-200 border rounded-xl border-gray-400/60 hover:bg-white/10"
          >
            Crear cuenta
          </NuxtLink>
        </div>

        <!-- Móvil -->
        <NuxtLink
          to="/login"
          class="inline-flex items-center justify-center w-10 h-10 text-gray-200 border sm:hidden rounded-xl border-gray-400/60 hover:bg-white/10"
          aria-label="Acceder"
        >
          <font-awesome-icon icon="fa-regular fa-user" />
        </NuxtLink>
      </div>
    </div>

    <!-- Panel móvil -->
    <transition name="fade">
      <div v-if="open" class="absolute left-0 right-0 px-3 top-16 sm:px-6">
        <div
          class="p-3 mx-auto space-y-2 border shadow-xl max-w-7xl rounded-2xl border-gray-400/50 bg-black/70 backdrop-blur-md"
        >
          <NuxtLink
            to="/"
            class="block px-4 py-3 text-gray-200 border border-transparent rounded-xl hover:bg-white/10 hover:border-gray-400/60"
            @click="open = false"
            >Inicio</NuxtLink
          >
          <NuxtLink
            to="/weather"
            class="block px-4 py-3 text-gray-200 border border-transparent rounded-xl hover:bg-white/10 hover:border-gray-400/60"
            @click="open = false"
            >Weather</NuxtLink
          >
          <NuxtLink
            to="/traffic"
            class="block px-4 py-3 text-gray-200 border border-transparent rounded-xl hover:bg-white/10 hover:border-gray-400/60"
            @click="open = false"
            >Traffic</NuxtLink
          >
          <div class="h-px my-1 bg-gray-400/40"></div>
          <NuxtLink
            to="/login"
            class="block px-4 py-3 text-gray-200 border border-transparent rounded-xl hover:bg-white/10 hover:border-gray-400/60"
            @click="open = false"
            >Acceder</NuxtLink
          >
          <NuxtLink
            to="/register"
            class="block px-4 py-3 text-gray-200 border border-transparent rounded-xl hover:bg-white/10 hover:border-gray-400/60"
            @click="open = false"
            >Crear cuenta</NuxtLink
          >
        </div>
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
