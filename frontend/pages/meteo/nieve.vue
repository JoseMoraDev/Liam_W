<template>
  <div
    class="w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg')"
  >
    <!-- Capa translúcida -->
    <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>

    <!-- Contenido -->
    <div
      :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 overflow-y-auto transition-opacity duration-300 md:p-8"
    >
      <!-- Loader -->
      <div
        v-if="loading"
        class="w-full max-w-md p-6 text-center shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <p class="text-gray-900/80">❄️ Cargando boletín nivológico...</p>
      </div>

      <!-- Error -->
      <div
        v-else-if="error"
        class="w-full max-w-md p-6 text-center shadow-inner rounded-2xl bg-red-200/30 backdrop-blur-md"
      >
        <p class="text-red-700">⚠️ Error: {{ error }}</p>
      </div>

      <!-- Boletín -->
      <div
        v-else-if="nivologica"
        class="w-full max-w-3xl p-6 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">
          🌨️ Boletín Nivológico
        </h2>

        <p class="font-semibold text-center text-gray-800">
          Zona: {{ nivologica.zona }}
        </p>

        <div
          class="p-4 mt-2 text-sm leading-relaxed whitespace-pre-line shadow-inner text-gray-900/80 rounded-xl bg-gray-100/20"
        >
          {{ nivologica.boletin }}
        </div>
      </div>

      <!-- Footer -->
      <footer class="mt-auto mb-2 text-xs text-gray-700/70">
        Datos proporcionados por
        <a
          href="https://www.aemet.es"
          target="_blank"
          class="underline hover:text-gray-900"
        >
          AEMET
        </a>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const mounted = ref(false);
const nivologica = ref(null);
const loading = ref(true);
const error = ref(null);

// ⚠️ Aquí puedes cambiar dinámicamente la zona
const zonaId = 1;

onMounted(async () => {
  mounted.value = true;
  try {
    const { data } = await axios.get(
      `http://localhost:8000/api/aemet/nivologica/${zonaId}`
    );
    nivologica.value = data;
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
});
</script>
