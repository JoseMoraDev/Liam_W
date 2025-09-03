<template>
  <div class="w-full max-w-4xl mx-auto space-y-6">
    <!-- Menú principal -->
    <div v-if="!showWeatherSubmenu && !showTrafficSubmenu" class="space-y-6">
      <!-- Weather -->
      <div
        @click="showWeatherSubmenu = true"
        class="flex items-center justify-center p-6 bg-gray-800 cursor-pointer rounded-2xl"
      >
        <span class="text-xl font-semibold text-white md:text-2xl"
          >WEATHER</span
        >
      </div>

      <!-- Air Quality -->
      <NuxtLink to="/aqi">
        <div
          class="flex items-center justify-center p-6 bg-gray-800 rounded-2xl"
        >
          <span class="text-xl font-semibold text-white md:text-2xl"
            >AIR QUALITY</span
          >
        </div>
      </NuxtLink>

      <!-- Traffic -->
      <div
        @click="showTrafficSubmenu = true"
        class="flex items-center justify-center p-6 bg-gray-800 cursor-pointer rounded-2xl"
      >
        <span class="text-xl font-semibold text-white md:text-2xl"
          >TRAFFIC</span
        >
      </div>
    </div>

    <!-- Submenu Weather -->
    <div v-if="showWeatherSubmenu" class="space-y-4">
      <NuxtLink to="/weather/snow">
        <div
          class="flex items-center justify-center p-4 bg-gray-800 cursor-pointer rounded-2xl"
        >
          <span class="font-semibold text-white">Nieve y Nivología</span>
        </div>
      </NuxtLink>

      <NuxtLink to="/weather/beach">
        <div
          class="flex items-center justify-center p-4 bg-gray-800 cursor-pointer rounded-2xl"
        >
          <span class="font-semibold text-white">Tiempo en la Playa</span>
        </div>
      </NuxtLink>

      <NuxtLink to="/weather/mountain">
        <div
          class="flex items-center justify-center p-4 bg-gray-800 cursor-pointer rounded-2xl"
        >
          <span class="font-semibold text-white">Montaña y Senderos</span>
        </div>
      </NuxtLink>

      <NuxtLink to="/weather/sea-temperature">
        <div
          class="flex items-center justify-center p-4 bg-gray-800 cursor-pointer rounded-2xl"
        >
          <span class="font-semibold text-white">Temperatura del Mar</span>
        </div>
      </NuxtLink>

      <NuxtLink to="/weather/alerts">
        <div
          class="flex items-center justify-center p-4 bg-gray-800 cursor-pointer rounded-2xl"
        >
          <span class="font-semibold text-white">Alertas Meteorológicas</span>
        </div>
      </NuxtLink>

      <NuxtLink to="/weather/daily">
        <div
          class="flex items-center justify-center p-4 bg-gray-800 cursor-pointer rounded-2xl"
        >
          <span class="font-semibold text-white">Pronóstico Diario</span>
        </div>
      </NuxtLink>

      <NuxtLink to="/weather/hourly">
        <div
          class="flex items-center justify-center p-4 bg-gray-800 cursor-pointer rounded-2xl"
        >
          <span class="font-semibold text-white">Pronóstico Horario</span>
        </div>
      </NuxtLink>

      <!-- Botón volver -->
      <div
        @click="showWeatherSubmenu = false"
        class="flex items-center justify-center p-4 bg-gray-600 cursor-pointer rounded-2xl"
      >
        <span class="font-semibold text-white">Volver</span>
      </div>
    </div>

    <!-- Submenu Traffic -->
    <div v-if="showTrafficSubmenu" class="space-y-4">
      <NuxtLink to="/traffic/status">
        <div
          class="flex items-center justify-center p-4 bg-gray-800 cursor-pointer rounded-2xl"
        >
          <span class="font-semibold text-white">Estado del Tráfico</span>
        </div>
      </NuxtLink>

      <NuxtLink to="/traffic/alerts">
        <div
          class="flex items-center justify-center p-4 bg-gray-800 cursor-pointer rounded-2xl"
        >
          <span class="font-semibold text-white">Alertas de Tráfico</span>
        </div>
      </NuxtLink>

      <!-- Botón volver -->
      <div
        @click="showTrafficSubmenu = false"
        class="flex items-center justify-center p-4 bg-gray-600 cursor-pointer rounded-2xl"
      >
        <span class="font-semibold text-white">Volver</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const showWeatherSubmenu = ref(false);
const showTrafficSubmenu = ref(false);
</script>
