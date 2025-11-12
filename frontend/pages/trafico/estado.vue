<script setup>
import { ref, onMounted } from "vue";
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";

// Vue Leaflet
import { LMap, LTileLayer, LMarker, LPolyline } from "@vue-leaflet/vue-leaflet";
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
const velocidadData = ref(null);
const tiempoData = ref(null);
const congestionData = ref(null);
const coords = ref([]);

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
  let hex = v.replace('#','')
  if (hex.length === 3) {
    hex = hex.split('').map(c => c + c).join('')
  }
  const r = parseInt(hex.substring(0,2), 16)
  const g = parseInt(hex.substring(2,4), 16)
  const b = parseInt(hex.substring(4,6), 16)
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
      // Dataset de velocidad
      velocidadData.value = {
        labels: ["Velocidad"],
        datasets: [
          {
            label: "Actual",
            data: [datos.value.currentSpeed],
            backgroundColor: hexToRgba(css('--color-danger'), 0.7), // rojo tema
          },
          {
            label: "Libre",
            data: [datos.value.freeFlowSpeed],
            backgroundColor: hexToRgba(css('--color-success'), 0.7), // verde tema
          },
        ],
      };

      // Dataset de tiempo
      tiempoData.value = {
        labels: ["Tiempo"],
        datasets: [
          {
            label: "Actual",
            data: [Math.round(datos.value.currentTravelTime / 60)],
            backgroundColor: hexToRgba(css('--color-primary'), 0.7), // primario tema
          },
          {
            label: "Libre",
            data: [Math.round(datos.value.freeFlowTravelTime / 60)],
            backgroundColor: hexToRgba(css('--color-secondary'), 0.7), // secundario tema
          },
        ],
      };

      // Dataset congestión
      const congestion = (
        (datos.value.currentSpeed / datos.value.freeFlowSpeed) *
        100
      ).toFixed(0);

      congestionData.value = {
        labels: ["Tráfico", "Libre"],
        datasets: [
          {
            data: [congestion, 100 - congestion],
            backgroundColor: [css('--color-warning'), css('--color-border')], // tema
          },
        ],
      };

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
  } catch (err) {
    console.error("Error al cargar tráfico:", err);
  }
});

// cuando el mapa está listo
const onMapReady = (map) => {
  leafletMap = map;
  if (coords.value.length) {
    map.fitBounds(coords.value, { padding: [20, 20] });
  }
};
</script>

<template>
  <div class="relative w-full min-h-screen bg-center bg-cover" style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 min-h-screen p-4 text-[color:var(--color-text)]">
      <h1 class="mb-6 text-3xl font-bold tracking-tight text-center page-title">🚦 Estado del tráfico</h1>

    <div v-if="!datos">Cargando tráfico...</div>

    <div v-else class="flex flex-col gap-6">
      <div class="flex flex-col gap-4 p-4 border frost-card border-white/15 rounded-2xl">
        <div
          class="flex items-center justify-between pb-2 border-b theme-border"
        >
          <span class="font-semibold">Velocidad actual</span>
          <span
            class="px-3 py-1 font-bold rounded-lg"
            :class="{
              'bg-green-600 text-white':
                datos.currentSpeed > datos.freeFlowSpeed * 0.7,
              'bg-yellow-500 text-black':
                datos.currentSpeed <= datos.freeFlowSpeed * 0.7 &&
                datos.currentSpeed > datos.freeFlowSpeed * 0.4,
              'bg-red-600 text-white':
                datos.currentSpeed <= datos.freeFlowSpeed * 0.4,
            }"
          >
            {{ datos.currentSpeed }} km/h
          </span>
        </div>
        <div class="flex items-center justify-between pb-2 border-b theme-border">
          <span class="font-semibold">Velocidad libre</span>
          <span class="theme-text-muted">{{ datos.freeFlowSpeed }} km/h</span>
        </div>
        <div class="flex items-center justify-between pb-2 border-b theme-border">
          <span class="font-semibold">Tiempo de viaje actual</span>
          <span>{{ Math.round(datos.currentTravelTime / 60) }} min</span>
        </div>
        <div class="flex items-center justify-between pb-2 border-b theme-border">
          <span class="font-semibold">Tiempo en condiciones libres</span>
          <span>{{ Math.round(datos.freeFlowTravelTime / 60) }} min</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="font-semibold">Confianza</span>
          <span>{{ (datos.confidence * 100).toFixed(0) }}%</span>
        </div>
        <div
          v-if="datos.roadClosure"
          class="p-3 mt-4 font-bold text-center rounded-lg text-white bg-[color:var(--color-danger)]"
        >
          ⚠️ Carretera cerrada
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <div class="p-4 border frost-card border-white/15 rounded-2xl">
          <h2 class="mb-2 text-sm font-semibold">Velocidad</h2>
          <div style="height: 200px; width: 100%">
            <Bar
              v-if="velocidadData"
              :data="velocidadData"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'x',
                plugins: {
                  legend: { position: 'top', labels: { color: css('--color-text') } },
                },
                scales: {
                  x: { ticks: { color: css('--color-text-muted') } },
                  y: { ticks: { color: css('--color-text-muted') }, beginAtZero: true },
                },
              }"
            />
          </div>
        </div>

        <div class="p-4 border frost-card border-white/15 rounded-2xl">
          <h2 class="mb-2 text-sm font-semibold">Tiempo</h2>
          <div style="height: 200px; width: 100%">
            <Bar
              v-if="tiempoData"
              :data="tiempoData"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'x',
                plugins: {
                  legend: { position: 'top', labels: { color: css('--color-text') } },
                },
                scales: {
                  x: { ticks: { color: css('--color-text-muted') } },
                  y: { ticks: { color: css('--color-text-muted') }, beginAtZero: true },
                },
              }"
            />
          </div>
        </div>

        <div class="p-4 border frost-card border-white/15 rounded-2xl">
          <h2 class="mb-2 text-sm font-semibold">Congestión</h2>
          <div style="height: 200px; width: 100%">
            <Doughnut
              v-if="congestionData"
              :data="congestionData"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: { position: 'bottom', labels: { color: css('--color-text') } },
                },
              }"
            />
          </div>
        </div>
      </div>

      <div class="p-4 border frost-card border-white/15 rounded-2xl">
        <client-only>
          <LMap
            v-if="coords.length"
            style="height: 250px; width: 100%"
            @ready="onMapReady"
          >
            <LTileLayer
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
              attribution="&copy; OpenStreetMap"
            />
            <LPolyline :lat-lngs="coords" :color="css('--color-danger')" :weight="5" />
            <LMarker :lat-lng="coords[0]" />
          </LMap>
        </client-only>
      </div>
    </div>
    </div>
  </div>
</template>

<style scoped>
.page-title { color: #ffffff !important; }

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
</style>
