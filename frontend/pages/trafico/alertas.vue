<script setup>
import { ref, onMounted, nextTick } from "vue";
import { axiosClient } from "~/axiosConfig";
import "leaflet/dist/leaflet.css";

const incidentes = ref([]);
const cargando = ref(true);
let map = null;
const center = ref([40.4168, -3.7038]); // fallback Madrid

// Helper para leer variables CSS del tema
function css(name){
  if (typeof window === 'undefined') return ''
  return getComputedStyle(document.documentElement).getPropertyValue(name).trim()
}

// Función para obtener descripción de la categoría
function descripcionCategoria(iconCategory) {
  const categorias = {
    1: "Accidente",
    2: "Obras",
    3: "Cierre de carril",
    4: "Congestión",
    5: "Clima adverso",
    6: "Vehículo detenido",
    7: "Evento",
    8: "Otros incidentes",
    14: "Otros (no clasificado)",
  };
  return categorias[iconCategory] || "Desconocido";
}

// Función para asignar color según categoría usando el tema
function colorCategoria(iconCategory) {
  const colores = {
    1: css('--color-danger'),
    2: css('--color-warning'),
    3: css('--color-warning'),
    4: css('--color-secondary'),
    5: css('--color-info'),
    6: css('--color-border'),
    7: css('--color-primary'),
    8: css('--color-info'),
    14: css('--color-secondary'),
  };
  return colores[iconCategory] || css('--color-text');
}

// Inicializar Leaflet
async function initMap() {
  if (typeof window === "undefined") return;
  const L = (await import("leaflet")).default;

  // Corrección de íconos
  try {
    if (L.Icon) {
      const Icon = L.Icon;
      Icon.Default.mergeOptions({
        iconRetinaUrl: new URL(
          "leaflet/dist/images/marker-icon-2x.png",
          import.meta.url
        ).href,
        iconUrl: new URL("leaflet/dist/images/marker-icon.png", import.meta.url)
          .href,
        shadowUrl: new URL(
          "leaflet/dist/images/marker-shadow.png",
          import.meta.url
        ).href,
      });
    }
  } catch (e) {
    console.warn("Leaflet Icon Fix falló:", e);
  }

  const mapElement = document.getElementById("mapa");
  if (!mapElement || map) return;

  // Inicializamos mapa
  map = L.map(mapElement).setView(center.value, 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(map);

  if (incidentes.value.length) {
    const allCoords = [];
    incidentes.value.forEach((incidente) => {
      if (!incidente.geometry?.coordinates?.length) return;
      const coords = incidente.geometry.coordinates.map((c) => [c[1], c[0]]);
      const color = colorCategoria(incidente.properties.iconCategory);

      L.polyline(coords, { color, weight: 5, opacity: 0.7 })
        .bindPopup(descripcionCategoria(incidente.properties.iconCategory))
        .addTo(map);

      allCoords.push(...coords);
    });

    if (allCoords.length) map.fitBounds(allCoords, { padding: [20, 20] });
  }
}

// Cargar datos
onMounted(async () => {
  try {
    // Obtener coordenadas y construir un bbox pequeño alrededor
    const pref = await axiosClient.get('/user/location-pref');
    const { lat, lon } = pref.data || {};
    if (lat != null && lon != null) {
      center.value = [lat, lon];
    }
    const delta = 0.02; // ~2km aprox
    const bbox = (lat != null && lon != null)
      ? `${(lon - delta).toFixed(3)},${(lat - delta).toFixed(3)},${(lon + delta).toFixed(3)},${(lat + delta).toFixed(3)}`
      : `-3.73,40.40,-3.68,40.44`; // bbox Madrid
    const res = await axiosClient.get('/tomtom/traffic-incidents', { params: { bbox } });
    incidentes.value = res.data.incidents || [];

    await nextTick();
    initMap();
  } catch (err) {
    console.error("Error al cargar incidentes de tráfico:", err);
  } finally {
    cargando.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen p-4 text-[color:var(--color-text)] bg-[color:var(--color-bg)]">
    <h1 class="mt-12 mb-6 text-2xl font-bold text-center">🚦 Incidencias de tráfico</h1>

    <div v-if="cargando" class="py-10 text-lg text-center">
      Cargando incidentes...
    </div>
    <div v-else-if="!incidentes.length" class="py-10 text-lg text-center">
      No hay incidentes en la zona.
    </div>

    <div v-else class="flex flex-col gap-6">
      <!-- Mapa -->
      <div
        id="mapa"
        class="w-full min-h-[500px] rounded-lg border theme-border shadow-lg theme-surface"
      ></div>

      <!-- Lista de incidentes -->
      <h2 class="text-xl font-semibold">Listado de incidencias</h2>
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div
          v-for="(incidente, index) in incidentes"
          :key="index"
          class="p-4 transition theme-surface border theme-border rounded-lg shadow-md hover:theme-surface-weak"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="font-bold">
              {{ descripcionCategoria(incidente.properties.iconCategory) }}
            </span>
            <span
              class="w-4 h-4 rounded-full"
              :style="{
                backgroundColor: colorCategoria(
                  incidente.properties.iconCategory
                ),
              }"
            ></span>
          </div>
          <div class="mb-1 text-sm theme-text-muted">
            <strong>Coordenadas inicio:</strong>
            {{ incidente.geometry.coordinates[0][1].toFixed(6) }},
            {{ incidente.geometry.coordinates[0][0].toFixed(6) }}
          </div>
          <div class="mb-1 text-sm theme-text-muted">
            <strong>Coordenadas fin:</strong>
            {{ incidente.geometry.coordinates.slice(-1)[0][1].toFixed(6) }},
            {{ incidente.geometry.coordinates.slice(-1)[0][0].toFixed(6) }}
          </div>
          <div class="text-sm theme-text-muted">
            <strong>Número de puntos:</strong>
            {{ incidente.geometry.coordinates.length }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.leaflet-container {
  height: 100%;
  width: 100%;
  border-radius: 0.5rem;
}
</style>
