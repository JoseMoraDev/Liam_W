<template>
  <div
    class="w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg')"
  >
    <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>

    <div
      :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 overflow-y-auto transition-opacity duration-300 md:p-8"
    >
      <!-- WEATHER -->
      <div
        class="w-full max-w-md p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">
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
        class="w-full max-w-md p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">
          Calidad del aire
        </h2>

        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/aqi/current" class="sub-card">Ambiente</NuxtLink>
          <NuxtLink to="/aqi/forecast" class="sub-card">Polen</NuxtLink>
        </div>
      </div>

      <!-- TRAFFIC -->
      <div
        class="w-full max-w-md p-4 space-y-4 shadow-inner rounded-2xl bg-gray-100/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">Tráfico</h2>

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
  @apply flex items-center justify-center p-4 text-sm font-semibold text-center text-gray-900/90 transition-colors rounded-xl bg-gray-100/20 backdrop-blur-md hover:bg-gray-200/30 shadow-inner;
}
</style>
