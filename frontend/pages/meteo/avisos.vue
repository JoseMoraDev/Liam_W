<template>
  <div
    class="relative w-full min-h-screen px-4 bg-center bg-cover"
    style="background-image: url('/img/menu.jpg'); background-attachment: fixed;"
  >
    <!-- Capa oscura -->
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Contenido principal -->
    <div :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-4 transition-opacity duration-300 md:p-8">
      <h1 class="mb-4 text-3xl font-extrabold tracking-tight text-center md:text-4xl page-title">Avisos meteorológicos</h1>
      <div class="mt-8"></div>

      <!-- Loader mientras carga -->
      <div v-if="loading" class="flex items-center justify-center min-h-[40vh]">
        <div class="flex flex-col items-center gap-3 loader">
          <div class="spinner" aria-label="Cargando"></div>
          <div class="loader-text">Cargando datos...</div>
        </div>
      </div>

      <!-- Aviso principal -->
      <div v-else-if="aviso" class="max-w-lg px-6 py-4 mt-12 text-center border frost-card border-white/15 rounded-2xl">
        <h1 :class="['text-2xl', 'font-bold', 'md:text-3xl', levelClass(aviso.info.parameters.level)]">
          🚨 {{ aviso.info.event }}
        </h1>
        <p class="mt-2 text-sm text-gray-200">
          Emitido por {{ aviso.info.senderName }}
        </p>
      </div>

      <!-- Descripción -->
      <div v-if="aviso"
        class="max-w-lg px-6 py-4 mt-8 space-y-3 text-center border frost-card border-white/15 rounded-2xl">
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
      <div v-if="aviso"
        class="max-w-lg px-6 py-4 mt-8 space-y-2 text-center border frost-card border-white/15 rounded-2xl">
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
      <div v-if="aviso"
        class="max-w-lg px-6 py-4 mt-8 space-y-2 text-center border frost-card border-white/15 rounded-2xl">
        <p :class="['text-lg', 'font-bold', levelClass(aviso.info.parameters.level)]">
          Nivel: {{ aviso.info.parameters.level.toUpperCase() }}
        </p>
        <p class="text-sm text-gray-200">
          Probabilidad: {{ aviso.info.parameters.probability }}
        </p>
      </div>

      <!-- Instrucciones -->
      <div v-if="aviso" class="max-w-lg px-6 py-4 mt-8 text-center border frost-card border-white/15 rounded-2xl">
        <p class="text-sm text-gray-100">📝 {{ aviso.info.instruction }}</p>
        <a :href="aviso.info.web" target="_blank" class="block mt-3 text-xs text-gray-300 underline hover:text-white">
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
      <footer class="absolute w-full text-xs text-center text-gray-600 bottom-2">
        Datos proporcionados por
        <a href="https://www.aemet.es" target="_blank" class="underline hover:text-white">AEMET</a>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";

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

function norm(s) {
  return String(s || '').toLowerCase().normalize('NFD').replace(/[^a-z0-9]+/g, '').replace(/[\u0300-\u036f]/g, '');
}

function mapRegionFromCodauto(cod) {
  const map = {
    '01': '61', '02': '62', '03': '63', '04': '64', '05': '65', '06': '66', '07': '67', '08': '68', '09': '69', '10': '70', '11': '71', '12': '72', '13': '73', '14': '74', '15': '75', '16': '76', '17': '77', '18': '78', '19': '79'
  };
  const key = cod == null ? null : String(cod).padStart(2, '0');
  return map[key] || null;
}

function mapRegionFromName(name) {
  const n = norm(name);
  const pairs = [
    ['andalucia', '61'], ['aragon', '62'], ['asturias', '63'],
    ['illesbalears', '64'], ['islasbaleares', '64'], ['baleares', '64'],
    ['canarias', '65'], ['cantabria', '66'], ['castillayleon', '67'], ['castillalamancha', '68'],
    ['cataluna', '69'], ['catalunya', '69'], ['cataluna', '69'], ['catalonia', '69'],
    ['extremadura', '70'], ['galicia', '71'], ['madrid', '72'], ['murcia', '73'], ['navarra', '74'], ['paisvasco', '75'], ['larioja', '76'],
    ['comunitatvalenciana', '77'], ['comunidadvalenciana', '77'], ['valencia', '77'],
    ['ceuta', '78'], ['melilla', '79']
  ];
  for (const [k, v] of pairs) { if (n.includes(k)) return v; }
  return null;
}

onMounted(async () => {
  mounted.value = true;
  try {
    // Obtener región dinámica desde preferencias; fallback por ccaa_id o nombre
    const uid = userData()?.value?.id;
    const prefRes = await axiosClient.get('/user/location-pref', { params: uid ? { user_id: uid } : {} });
    const pref = prefRes?.data || {};
    let region = pref.avisos_region || mapRegionFromCodauto(pref.ccaa_id) || mapRegionFromName(pref.ccaa_id) || 'esp';
    console.debug('[Avisos] región usada:', region, 'ccaa_id:', pref.ccaa_id, 'avisos_region:', pref.avisos_region);
    const { data } = await axiosClient.get(`/aemet/avisos_cap/ultimoelaborado/area/${region}`);
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

function levelClass(level) {
  const v = String(level || '').toUpperCase();
  if (v.includes('VERDE')) return 'text-green-400';
  if (v.includes('AMARILLO')) return 'text-yellow-300';
  if (v.includes('NARANJA')) return 'text-orange-400';
  if (v.includes('ROJO')) return 'text-red-500';
  return 'text-gray-200';
}
</script>

<style scoped>
.spinner {
  width: 48px;
  height: 48px;
  border-radius: 9999px;
  border: 4px solid color-mix(in srgb, white 75%, var(--color-primary) 25%);
  border-top-color: color-mix(in srgb, white 30%, var(--color-primary) 70%);
  animation: spin 1s linear infinite;
}

.loader-text {
  font-weight: 600;
  letter-spacing: 0.2px;
  color: color-mix(in srgb, white 85%, var(--color-primary) 15%);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
/* Título en blanco como en horaria/diaria */
.page-title { color: #ffffff !important; }
/* Glass muy sutil para paneles (alineado con horaria/diaria) */
.frost-card {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

/* Velo extra en tema claro */
@media (prefers-color-scheme: light) {
  .frost-card {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 18%, transparent),
        color-mix(in srgb, white 18%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 3%, transparent),
        color-mix(in srgb, var(--color-primary) 3%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-bg) 12%, transparent),
        color-mix(in srgb, var(--color-bg) 12%, transparent));
  }
}

/* Forzar texto blanco dentro del panel para máxima legibilidad */
:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
  color: #ffffff !important;
}

/* Preservar colores de nivel sobre el override global */
:deep(.frost-card .text-green-400) {
  color: #4ade80 !important;
}

:deep(.frost-card .text-yellow-300) {
  color: #fde047 !important;
}

:deep(.frost-card .text-orange-400) {
  color: #fb923c !important;
}

:deep(.frost-card .text-red-500) {
  color: #ef4444 !important;
}
</style>
