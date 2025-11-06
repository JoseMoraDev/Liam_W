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

      <!-- Boletines -->
      <div v-else class="w-full max-w-5xl space-y-8">
        <div v-for="b in boletines" :key="b.zona" class="p-6 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md">
          <h2 class="text-xl font-bold text-center text-gray-900/90">🌨️ Boletín Nivológico</h2>
          <p class="font-semibold text-center text-gray-800">Zona: {{ b.zona }}</p>
          <div class="w-full p-4 mt-2 leading-relaxed whitespace-normal shadow-inner text-gray-900/80 rounded-xl bg-gray-100/20 text-[0.95rem]">
            {{ b.boletin }}
          </div>
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
import { axiosClient } from "~/axiosConfig";

const mounted = ref(false);
const boletines = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  mounted.value = true;
  try {
    const [cat, navAra] = await Promise.all([
      axiosClient.get('/aemet/nivologica/0'),
      axiosClient.get('/aemet/nivologica/1'),
    ]);
    const arr = [];
    if (cat?.data) arr.push(cat.data);
    if (navAra?.data) arr.push(navAra.data);
    boletines.value = arr;
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
});
</script>
