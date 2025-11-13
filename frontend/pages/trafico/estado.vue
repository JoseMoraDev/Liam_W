<script setup>
import { ref, onMounted, computed } from "vue";
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";

// Vue Leaflet
import { LMap, LTileLayer, LMarker, LPolyline, LCircle } from "@vue-leaflet/vue-leaflet";
import "leaflet/dist/leaflet.css";

// Chart.js
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
} from "chart.js";
import { Bar, Doughnut } from "vue-chartjs";

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement
);

const datos = ref(null);
const coords = ref([]);
const loading = ref(true);
const error = ref(null);

let leafletMap = null; // guardamos el objeto real del mapa

// Helpers de tema
function css(name) {
  if (typeof window === 'undefined') return ''
  return getComputedStyle(document.documentElement).getPropertyValue(name).trim()
}

function hexToRgba(input, alpha = 1) {
  if (!input) return `rgba(0,0,0,${alpha})`
  const v = input.trim()
  if (v.startsWith('rgba')) {
    // Reemplaza el canal alpha
    return v.replace(/rgba\(([^,]+),\s*([^,]+),\s*([^,]+),\s*[^)]+\)/, `rgba($1,$2,$3,${alpha})`)
  }
  if (v.startsWith('rgb(')) {
    return v.replace(/rgb\(([^,]+),\s*([^,]+),\s*[^)]+\)/, `rgba($1,$2,$3,${alpha})`)
  }
  // Hex #RRGGBB o #RGB
  let hex = v.replace('#', '')
  if (hex.length === 3) {
    hex = hex.split('').map(c => c + c).join('')
  }
  const r = parseInt(hex.substring(0, 2), 16)
  const g = parseInt(hex.substring(2, 4), 16)
  const b = parseInt(hex.substring(4, 6), 16)
  return `rgba(${r}, ${g}, ${b}, ${alpha})`
}

// 💡 CORRECCIÓN SSR/LEAFLET: Mover la redefinición de íconos.
// El código de la corrección de íconos no debe estar en el alcance global del script
// para evitar el error 'window is not defined' en entornos SSR/Nuxt/Vite.
// El bloque de código original ha sido eliminado de aquí:
/*
import { Icon } from "leaflet";
import iconUrl from "leaflet/dist/images/marker-icon.png";
import iconRetinaUrl from "leaflet/dist/images/marker-icon-2x.png";
import shadowUrl from "leaflet/dist/images/marker-shadow.png";
Icon.Default.mergeOptions({ iconUrl, iconRetinaUrl, shadowUrl });
*/

onMounted(async () => {
  // 💡 CORRECCIÓN SSR/LEAFLET: Realizar la redefinición de íconos dentro de onMounted
  // ya que en este punto, el código ya está en el entorno del navegador (client-side).
  if (typeof window !== "undefined") {
    // Importamos dinámicamente o redefinimos el constructor para asegurar la compatibilidad.
    try {
      const { Icon } = await import("leaflet");

      // Reimportamos las rutas de los assets para que el bundler las procese
      const iconUrl = new URL(
        "leaflet/dist/images/marker-icon.png",
        import.meta.url
      ).href;
      const iconRetinaUrl = new URL(
        "leaflet/dist/images/marker-icon-2x.png",
        import.meta.url
      ).href;
      const shadowUrl = new URL(
        "leaflet/dist/images/marker-shadow.png",
        import.meta.url
      ).href;

      // Asignamos las rutas corregidas a la configuración por defecto
      Icon.Default.mergeOptions({
        iconUrl,
        iconRetinaUrl,
        shadowUrl,
      });
    } catch (e) {
      console.warn(
        "No se pudo aplicar la corrección de íconos de Leaflet. Verifique las rutas de los assets.",
        e
      );
    }
  }

  try {
    loading.value = true;
    // Obtener coordenadas de la ubicación guardada
    const uid = userData()?.value?.id;
    const pref = await axiosClient.get('/user/location-pref', { params: uid ? { user_id: uid } : {} });
    const { lat, lon } = pref.data || {};
    const params = (lat != null && lon != null)
      ? { point: `${lat},${lon}`, unit: 'KMPH' }
      : { point: `40.4168,-3.7038`, unit: 'KMPH' }; // Fallback Madrid
    const res = await axiosClient.get('/tomtom/traffic-flow', { params });
    datos.value = res.data.flowSegmentData;

    if (datos.value) {
      // Coordenadas para el mapa
      coords.value = datos.value.coordinates.coordinate.map((c) => [
        c.latitude,
        c.longitude,
      ]);

      // Si el mapa ya está montado, ajustamos la vista
      if (leafletMap && coords.value.length) {
        leafletMap.fitBounds(coords.value, { padding: [20, 20] });
      }
    }
    loading.value = false;
  } catch (err) {
    console.error("Error al cargar tráfico:", err);
    error.value = 'No se pudieron cargar los datos de tráfico.';
    loading.value = false;
  }
});

// cuando el mapa está listo
const onMapReady = (map) => {
  leafletMap = map;
  if (coords.value.length) {
    map.fitBounds(coords.value, { padding: [20, 20] });
  }
  // Asegurar cálculo correcto de tamaño tras montar
  setTimeout(() => {
    try { map.invalidateSize(); } catch (_) { }
  }, 0);
};

// ========= Derivados para UI clara =========
const speedRatio = computed(() => {
  if (!datos.value) return 0;
  const f = Number(datos.value.freeFlowSpeed) || 0;
  const c = Number(datos.value.currentSpeed) || 0;
  return f > 0 ? c / f : 0;
});

const delayMin = computed(() => {
  if (!datos.value) return 0;
  const diff = (Number(datos.value.currentTravelTime) || 0) - (Number(datos.value.freeFlowTravelTime) || 0);
  return Math.max(0, Math.round(diff / 60));
});

const reliability = computed(() => {
  if (!datos.value) return 0;
  const conf = Number(datos.value.confidence) || 0;
  return Math.round(conf * 100);
});

const status = computed(() => {
  if (!datos.value) return { label: '—', color: css('--color-border'), badge: 'bg-gray-500 text-white' };
  if (datos.value.roadClosure) return { label: 'Cerrado', color: css('--color-danger'), badge: 'bg-red-600 text-white' };
  const r = speedRatio.value;
  if (r >= 0.8) return { label: 'Fluido', color: css('--color-success'), badge: 'bg-green-600 text-white' };
  if (r >= 0.6) return { label: 'Denso', color: css('--color-warning'), badge: 'bg-yellow-500 text-black' };
  return { label: 'Congestión', color: css('--color-danger'), badge: 'bg-red-600 text-white' };
});

// Centro aproximado del segmento para centrar el área
const center = computed(() => {
  if (!coords.value.length) return [40.4168, -3.7038];
  let lat = 0, lon = 0;
  for (const [a, b] of coords.value) { lat += a; lon += b; }
  return [lat / coords.value.length, lon / coords.value.length];
});

// Radio de ciudad (m) — 5km
const cityRadius = 5000;
</script>

<template>
  <div class="relative w-full min-h-screen px-10 pt-10 bg-center bg-cover"
    style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 min-h-screen p-4 text-[color:var(--color-text)] pt-10 px-10">
      <h1 class="mt-6 text-3xl font-bold tracking-tight text-center page-title">Estado del tráfico</h1>

      <div v-if="loading" class="flex items-center justify-center min-h-[30vh]">
        <div class="flex flex-col items-center gap-3">
          <div class="spinner" aria-label="Cargando"></div>
          <div class="loader-text">Cargando tráfico...</div>
        </div>
      </div>

      <div v-else-if="error" class="max-w-md p-4 mx-auto text-center border rounded-xl border-white/20 frost-card">
        {{ error }}
      </div>

      <div v-else-if="datos" class="flex flex-col gap-6 mt-10">
        <!-- Hero resumen -->
        <div class="p-5 border frost-card border-white/15 rounded-2xl">
          <div class="flex items-center justify-between gap-3">
            <div>
              <div :class="['inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold', status.badge]">
                {{ status.label }}
              </div>
              <div class="mt-3 text-4xl font-extrabold leading-tight">{{ datos.currentSpeed }}<span
                  class="text-2xl font-bold"> km/h</span></div>
              <div class="mt-1 text-sm text-white/70">Libre: {{ datos.freeFlowSpeed }} km/h · FRC: {{ datos.frc }}</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-white/70">Retraso</div>
              <div class="text-3xl font-extrabold">{{ delayMin }}<span class="text-xl font-bold"> min</span></div>
              <div class="mt-1 text-sm text-white/70">Confianza: {{ reliability }}%</div>
            </div>
          </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
          <div class="p-4 border frost-card border-white/15 rounded-2xl">
            <div class="text-sm text-white/70">Velocidad actual</div>
            <div class="mt-1 text-2xl font-bold">{{ datos.currentSpeed }} km/h</div>
          </div>
          <div class="p-4 border frost-card border-white/15 rounded-2xl">
            <div class="text-sm text-white/70">Velocidad libre</div>
            <div class="mt-1 text-2xl font-bold">{{ datos.freeFlowSpeed }} km/h</div>
          </div>
          <div class="p-4 border frost-card border-white/15 rounded-2xl">
            <div class="text-sm text-white/70">Relación</div>
            <div class="mt-1 text-2xl font-bold">{{ Math.round(speedRatio * 100) }}%</div>
          </div>
          <div class="p-4 border frost-card border-white/15 rounded-2xl">
            <div class="text-sm text-white/70">Confianza</div>
            <div class="mt-1 text-2xl font-bold">{{ reliability }}%</div>
          </div>
        </div>

        <!-- Mapa -->
        <div class="w-full p-4 border frost-card border-white/15 rounded-2xl">
          <client-only>
            <div class="w-full h-[360px] md:h-[460px] lg:h-[560px]">
              <LMap v-if="coords.length" style="height: 100%; width: 100%" :options="{ scrollWheelZoom: false }"
                @ready="onMapReady">
                <LTileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                  attribution="&copy; OpenStreetMap" />
                <LCircle :lat-lng="center" :radius="cityRadius" :color="status.color" :weight="2" :fill="true"
                  :fillColor="status.color" :fillOpacity="0.25" />
                <LMarker :lat-lng="center" />
              </LMap>
            </div>
          </client-only>
        </div>

        <!-- Nota fuente -->
        <div class="text-sm text-center text-white/60">Fuente: TomTom Traffic Flow API</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-title {
  color: #ffffff !important;
}

/* Glass muy sutil como en Diaria */
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

/* Forzar texto blanco dentro de frost-card */
:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
  color: #ffffff !important;
}

/* Spinner */
.spinner {
  width: 38px;
  height: 38px;
  border: 3px solid color-mix(in srgb, white 30%, transparent);
  border-top-color: color-mix(in srgb, var(--color-primary) 70%, white 30%);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.loader-text {
  color: color-mix(in srgb, white 75%, transparent);
  font-weight: 600;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Zoom +/- con el color predominante del tema (igual que 'Cambiar') */
:deep(.leaflet-control-zoom a) {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 32%, transparent),
      color-mix(in srgb, var(--color-primary) 32%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 18%, transparent),
      color-mix(in srgb, var(--color-bg) 18%, transparent));
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.26);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.28);
}

:deep(.leaflet-control-zoom a:hover),
:deep(.leaflet-control-zoom a:focus) {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 45%, transparent),
      color-mix(in srgb, var(--color-primary) 45%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 22%, transparent),
      color-mix(in srgb, var(--color-bg) 22%, transparent));
  outline: none;
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 55%, transparent);
}

@media (prefers-color-scheme: light) {
  :deep(.leaflet-control-zoom a) {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 18%, transparent),
        color-mix(in srgb, white 18%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 22%, transparent),
        color-mix(in srgb, var(--color-primary) 22%, transparent));
    color: #0b1220;
    border-color: rgba(0, 0, 0, 0.16);
  }

  :deep(.leaflet-control-zoom a:hover),
  :deep(.leaflet-control-zoom a:focus) {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 22%, transparent),
        color-mix(in srgb, white 22%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 30%, transparent),
        color-mix(in srgb, var(--color-primary) 30%, transparent));
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 45%, transparent);
  }
}
</style>
