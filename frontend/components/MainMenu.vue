<template>
  <div
    class="w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg')"
  >
    <div class="absolute inset-0 bg-black/40"></div>

    <div
      :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 overflow-y-auto transition-opacity duration-300 md:p-8"
    >
      <!-- WEATHER -->
      <div
        class="w-full max-w-md p-4 space-y-4 border border-gray-400 rounded-2xl bg-white/10"
      >
        <h2 class="text-xl font-bold text-center text-white">
          Previsión Meteorológica
        </h2>

        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/weather/snow" class="sub-card"
            >Municipal Horaria</NuxtLink
          >
          <NuxtLink to="/weather/beach" class="sub-card"
            >Municipal Diaria</NuxtLink
          >
          <NuxtLink to="/weather/mountain" class="sub-card">Avisos</NuxtLink>
          <NuxtLink to="/weather/hourly" class="sub-card">Nivológica</NuxtLink>
          <NuxtLink to="/weather/sea-temperature" class="sub-card"
            >Playa</NuxtLink
          >
          <NuxtLink to="/weather/alerts" class="sub-card">Montaña</NuxtLink>
        </div>
      </div>

      <!-- AIR QUALITY -->
      <div
        class="w-full max-w-md p-4 space-y-4 border border-gray-400 rounded-2xl bg-white/10"
      >
        <h2 class="text-xl font-bold text-center text-white">
          Calidad del aire
        </h2>

        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/aqi/current" class="sub-card">Ambiente</NuxtLink>
          <NuxtLink to="/aqi/forecast" class="sub-card">Polen</NuxtLink>
        </div>
      </div>

      <!-- TRAFFIC -->
      <div
        class="w-full max-w-md p-4 space-y-4 border border-gray-400 rounded-2xl bg-white/10"
      >
        <h2 class="text-xl font-bold text-center text-white">Tráfico</h2>

        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/traffic/status" class="sub-card">Estado</NuxtLink>
          <NuxtLink to="/traffic/alerts" class="sub-card">Alertas</NuxtLink>
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
  @apply flex items-center justify-center p-4 text-sm font-semibold text-center text-gray-200 transition-colors border border-gray-400 rounded-xl bg-gray-700/40 hover:bg-gray-500 hover:text-white;
}
</style>
