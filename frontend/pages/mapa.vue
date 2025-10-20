<script setup>
import { ref } from "vue";
import SVGMap from "vue-svg-map"; // 👈 default import
import spain from "@svg-maps/spain";

const selectedRegion = ref(null);

function onRegionClick(event) {
  const name = event.target.getAttribute("aria-label");
  selectedRegion.value = name;
}
</script>

<template>
  <div class="flex flex-col items-center min-h-screen py-10 bg-gray-100">
    <h1 class="mb-8 text-3xl font-bold text-blue-600">
      Mapa interactivo de España
    </h1>

    <div class="flex flex-col items-start gap-10 md:flex-row">
      <!-- Mapa -->
      <div class="p-4 bg-white shadow-md rounded-2xl">
        <SVGMap
          :map="spain"
          @location-click="onRegionClick"
          class="w-[600px] h-auto"
        />
      </div>

      <!-- Panel de información -->
      <div class="bg-white rounded-2xl shadow-md p-6 w-[300px]">
        <h2 class="mb-4 text-xl font-semibold text-gray-700">Información</h2>
        <p v-if="selectedRegion" class="text-gray-800">
          Has seleccionado:
          <span class="font-bold text-blue-500">{{ selectedRegion }}</span>
        </p>
        <p v-else class="italic text-gray-500">
          Haz clic en una comunidad autónoma para ver su nombre.
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
svg path {
  fill: #93c5fd;
  stroke: #1e3a8a;
  stroke-width: 0.5;
  transition: fill 0.2s, transform 0.2s;
}
svg path:hover {
  fill: #2563eb;
  transform: scale(1.03);
  cursor: pointer;
}
</style>
