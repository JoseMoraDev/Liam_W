<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

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
    const res = await axios.get(
      "http://localhost:8000/api/tomtom/traffic-flow?point=38.2699,-0.7126&unit=KMPH"
    );
    datos.value = res.data.flowSegmentData;

    if (datos.value) {
      // Dataset de velocidad
      velocidadData.value = {
        labels: ["Velocidad"],
        datasets: [
          {
            label: "Actual",
            data: [datos.value.currentSpeed],
            backgroundColor: "rgba(239, 68, 68, 0.7)", // rojo
          },
          {
            label: "Libre",
            data: [datos.value.freeFlowSpeed],
            backgroundColor: "rgba(34, 197, 94, 0.7)", // verde
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
            backgroundColor: "rgba(59, 130, 246, 0.7)", // azul
          },
          {
            label: "Libre",
            data: [Math.round(datos.value.freeFlowTravelTime / 60)],
            backgroundColor: "rgba(168, 85, 247, 0.7)", // morado
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
            backgroundColor: ["#f59e0b", "#1f2937"], // naranja y gris oscuro
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
  <div class="min-h-screen p-4 text-gray-200 bg-gray-900">
    <h1 class="mb-4 text-xl font-bold">🚦 Estado del tráfico en Elche</h1>

    <div v-if="!datos">Cargando tráfico...</div>

    <div v-else class="flex flex-col gap-6">
      <div class="flex flex-col gap-4 p-4 bg-gray-800 shadow-md rounded-xl">
        <div
          class="flex items-center justify-between pb-2 border-b border-gray-700"
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
        <div
          class="flex items-center justify-between pb-2 border-b border-gray-700"
        >
          <span class="font-semibold">Velocidad libre</span>
          <span class="text-gray-300">{{ datos.freeFlowSpeed }} km/h</span>
        </div>
        <div
          class="flex items-center justify-between pb-2 border-b border-gray-700"
        >
          <span class="font-semibold">Tiempo de viaje actual</span>
          <span>{{ Math.round(datos.currentTravelTime / 60) }} min</span>
        </div>
        <div
          class="flex items-center justify-between pb-2 border-b border-gray-700"
        >
          <span class="font-semibold">Tiempo en condiciones libres</span>
          <span>{{ Math.round(datos.freeFlowTravelTime / 60) }} min</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="font-semibold">Confianza</span>
          <span>{{ (datos.confidence * 100).toFixed(0) }}%</span>
        </div>
        <div
          v-if="datos.roadClosure"
          class="p-3 mt-4 font-bold text-center bg-red-700 rounded-lg"
        >
          ⚠️ Carretera cerrada
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <div class="p-3 bg-gray-800 rounded-lg">
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
                  legend: { position: 'top', labels: { color: '#ddd' } },
                },
                scales: {
                  x: { ticks: { color: '#aaa' } },
                  y: { ticks: { color: '#aaa' }, beginAtZero: true },
                },
              }"
            />
          </div>
        </div>

        <div class="p-3 bg-gray-800 rounded-lg">
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
                  legend: { position: 'top', labels: { color: '#ddd' } },
                },
                scales: {
                  x: { ticks: { color: '#aaa' } },
                  y: { ticks: { color: '#aaa' }, beginAtZero: true },
                },
              }"
            />
          </div>
        </div>

        <div class="p-3 bg-gray-800 rounded-lg">
          <h2 class="mb-2 text-sm font-semibold">Congestión</h2>
          <div style="height: 200px; width: 100%">
            <Doughnut
              v-if="congestionData"
              :data="congestionData"
              :options="{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: { position: 'bottom', labels: { color: '#ddd' } },
                },
              }"
            />
          </div>
        </div>
      </div>

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
          <LPolyline :lat-lngs="coords" color="red" :weight="5" />
          <LMarker :lat-lng="coords[0]" />
        </LMap>
      </client-only>
    </div>
  </div>
</template>
