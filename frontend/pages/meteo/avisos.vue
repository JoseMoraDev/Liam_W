<template>
  <div
    class="absolute inset-0 w-full h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg')"
  >
    <!-- Capa oscura -->
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Contenido principal -->
    <div
      :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full h-screen p-4 transition-opacity duration-300 md:p-8"
    >
      <div class="mt-20"></div>

      <!-- Loader mientras carga -->
      <div v-if="loading" class="mt-24 text-xl text-white">
        ⏳ Cargando aviso meteorológico...
      </div>

      <!-- Aviso principal -->
      <div
        v-else-if="aviso"
        class="max-w-lg px-6 py-4 mt-12 text-center shadow-inner rounded-2xl bg-white/10 backdrop-blur-sm"
      >
        <h1 class="text-2xl font-bold text-yellow-300 md:text-3xl">
          🚨 {{ aviso.info.event }}
        </h1>
        <p class="mt-2 text-sm text-gray-200">
          Emitido por {{ aviso.info.senderName }}
        </p>
      </div>

      <!-- Descripción -->
      <div
        v-if="aviso"
        class="max-w-lg px-6 py-4 mt-8 space-y-3 text-center shadow-inner rounded-2xl bg-white/5 backdrop-blur-sm"
      >
        <p class="text-xl font-semibold text-white">
          📍 {{ aviso.info.area.areaDesc }}
        </p>
        <p class="text-gray-200">
          {{ aviso.info.headline }}
        </p>
        <p class="italic text-gray-300">
          {{ aviso.info.description }}
        </p>
      </div>

      <!-- Fechas -->
      <div
        v-if="aviso"
        class="max-w-lg px-6 py-4 mt-8 space-y-2 text-center shadow-inner rounded-2xl bg-white/5 backdrop-blur-sm"
      >
        <p class="text-sm text-gray-200">
          ⏳ Vigente desde
          <span class="font-semibold text-white">{{
            formateaFecha(aviso.info.onset)
          }}</span>
        </p>
        <p class="text-sm text-gray-200">
          🕒 Hasta
          <span class="font-semibold text-white">{{
            formateaFecha(aviso.info.expires)
          }}</span>
        </p>
      </div>

      <!-- Probabilidad + nivel -->
      <div
        v-if="aviso"
        class="max-w-lg px-6 py-4 mt-8 space-y-2 text-center shadow-inner rounded-2xl bg-white/5 backdrop-blur-sm"
      >
        <p class="text-lg font-bold text-yellow-300">
          Nivel: {{ aviso.info.parameters.level.toUpperCase() }}
        </p>
        <p class="text-sm text-gray-200">
          Probabilidad: {{ aviso.info.parameters.probability }}
        </p>
      </div>

      <!-- Instrucciones -->
      <div
        v-if="aviso"
        class="max-w-lg px-6 py-4 mt-8 text-center shadow-inner rounded-2xl bg-white/5 backdrop-blur-sm"
      >
        <p class="text-sm text-gray-100">📝 {{ aviso.info.instruction }}</p>
        <a
          :href="aviso.info.web"
          target="_blank"
          class="block mt-3 text-xs text-gray-300 underline hover:text-white"
        >
          Más información en AEMET
        </a>
      </div>

      <!-- Error -->
      <div v-if="error" class="mt-12 text-center text-red-400">
        ⚠️ Error cargando aviso: {{ error }}
      </div>

      <!-- Spacer flexible -->
      <div class="flex-grow w-full"></div>

      <!-- Footer -->
      <footer
        class="absolute w-full text-xs text-center text-gray-600 bottom-2"
      >
        Datos proporcionados por
        <a
          href="https://www.aemet.es"
          target="_blank"
          class="underline hover:text-white"
          >AEMET</a
        >
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const mounted = ref(false);
const aviso = ref(null);
const loading = ref(true);
const error = ref(null);

const areas = {
  esp: "España",
  61: "Andalucía",
  62: "Aragón",
  63: "Asturias, Principado de",
  64: "Illes Balears",
  78: "Ceuta",
  65: "Canarias",
  66: "Cantabria",
  67: "Castilla y León",
  68: "Castilla - La Mancha",
  69: "Cataluña",
  77: "Comunitat Valenciana",
  70: "Extremadura",
  71: "Galicia",
  72: "Madrid, Comunidad de",
  79: "Melilla",
  73: "Murcia, Región de",
  74: "Navarra",
  75: "País Vasco",
  76: "La Rioja",
};

//! TODO: Hacer las áreas dinámicas

onMounted(async () => {
  mounted.value = true;
  try {
    const { data } = await axios.get(
      "http://localhost:8000/api/aemet/avisos_cap/ultimoelaborado/area/77"
    );
    aviso.value = data;
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
});

// Utilidad para dar formato bonito a fechas
function formateaFecha(iso) {
  const d = new Date(iso);
  return d.toLocaleString("es-ES", {
    weekday: "long",
    hour: "2-digit",
    minute: "2-digit",
    day: "numeric",
    month: "long",
  });
}
</script>
