<script setup>
import { ref, onMounted, nextTick, computed } from "vue";
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";
import "leaflet/dist/leaflet.css";

const incidentes = ref([]);
const cargando = ref(true);
const error = ref(null);
let map = null;
const center = ref([40.4168, -3.7038]); // fallback Madrid
const selectedIdx = ref(null);
let highlightLayer = null;

// Helper para leer variables CSS del tema
function css(name) {
  if (typeof window === 'undefined') return ''
  return getComputedStyle(document.documentElement).getPropertyValue(name).trim()
}

// Nombre de vía para el chip
function nombreVia(props = {}) {
  const rn = Array.isArray(props.roadNumbers) && props.roadNumbers.length ? props.roadNumbers[0] : null
  const from = props.from && typeof props.from === 'string' ? props.from : (props.from?.name || null)
  const to = props.to && typeof props.to === 'string' ? props.to : (props.to?.name || null)
  return rn || from || to || 'Vía desconocida'
}

// Extraer coordenadas [lat, lon] desde la geometría
function coordsFromGeometry(geom) {
  if (!geom) return []
  if (geom.type === 'Point' && Array.isArray(geom.coordinates)) {
    const [lon, lat] = geom.coordinates
    return [[lat, lon]]
  }
  if (geom.type === 'LineString' && Array.isArray(geom.coordinates)) {
    return geom.coordinates.map(([lon, lat]) => [lat, lon])
  }
  if (geom.type === 'MultiLineString' && Array.isArray(geom.coordinates)) {
    const out = []
    geom.coordinates.forEach(seg => {
      if (Array.isArray(seg)) out.push(...seg.map(([lon, lat]) => [lat, lon]))
    })
    return out
  }
  return []
}

// Fijar foco en una incidencia: resaltar en mapa y hacer scroll al mapa
async function focusIncidente(incidente, idx) {
  try { selectedIdx.value = idx } catch (_) { }
  if (!map) {
    await nextTick()
    if (!map) return
  }
  try {
    const L = (await import('leaflet')).default
    // Eliminar highlight previo
    try { highlightLayer && map.removeLayer(highlightLayer) } catch (_) { }
    highlightLayer = null

    const geom = incidente?.geometry
    const coords = coordsFromGeometry(geom)
    const themePrimary = css('--color-primary') || '#38bdf8'

    if (coords.length > 1) {
      highlightLayer = L.polyline(coords, { color: themePrimary, weight: 6, opacity: 0.95 }).addTo(map)
      try { map.fitBounds(coords, { padding: [24, 24] }) } catch (_) { }
    } else if (coords.length === 1) {
      const [lat, lon] = coords[0]
      highlightLayer = L.circleMarker([lat, lon], { radius: 8, color: themePrimary, weight: 3, fillColor: themePrimary, fillOpacity: 0.9 }).addTo(map)
      try { map.setView([lat, lon], Math.max(map.getZoom(), 15), { animate: true }) } catch (_) { }
    }

    // Desplazar vista hacia el mapa
    try {
      const el = document.getElementById('mapa')
      if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' })
    } catch (_) { }
  } catch (e) { /* noop */ }
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
  try { map.scrollWheelZoom && map.scrollWheelZoom.disable(); } catch (_) { }

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(map);

  if (incidentes.value.length) {
    const allCoords = [];
    incidentes.value.forEach((incidente) => {
      const geom = incidente?.geometry;
      const props = incidente?.properties || {};
      const color = colorCategoria(props.iconCategory);
      if (!geom) return;

      try {
        if (geom.type === 'LineString' && Array.isArray(geom.coordinates)) {
          const coords = geom.coordinates.map(([lon, lat]) => [lat, lon]);
          if (coords.length) {
            L.polyline(coords, { color, weight: 5, opacity: 0.7 })
              .bindPopup(descripcionCategoria(props.iconCategory))
              .addTo(map);
            allCoords.push(...coords);
          }
        } else if (geom.type === 'MultiLineString' && Array.isArray(geom.coordinates)) {
          geom.coordinates.forEach((segment) => {
            const coords = segment.map(([lon, lat]) => [lat, lon]);
            if (coords.length) {
              L.polyline(coords, { color, weight: 5, opacity: 0.7 })
                .bindPopup(descripcionCategoria(props.iconCategory))
                .addTo(map);
              allCoords.push(...coords);
            }
          });
        } else if (geom.type === 'Point' && Array.isArray(geom.coordinates)) {
          const [lon, lat] = geom.coordinates;
          const ll = [lat, lon];
          L.circleMarker(ll, { radius: 6, color, weight: 2, fillColor: color, fillOpacity: 0.7 })
            .bindPopup(descripcionCategoria(props.iconCategory))
            .addTo(map);
          allCoords.push(ll);
        }
      } catch (_) { /* ignore malformed geometry */ }
    });

    if (allCoords.length) {
      map.fitBounds(allCoords, { padding: [20, 20] });
    }
  }

  // Asegurar render tras layout
  setTimeout(() => {
    try { map.invalidateSize(); } catch (_) { }
  }, 0);
}

// Cargar datos
onMounted(async () => {
  try {
    // Obtener coordenadas y construir un bbox pequeño alrededor
    const uid = userData()?.value?.id;
    const pref = await axiosClient.get('/user/location-pref', { params: uid ? { user_id: uid } : {} });
    const { lat, lon } = pref.data || {};
    const latNum = Number(lat);
    const lonNum = Number(lon);
    if (Number.isFinite(latNum) && Number.isFinite(lonNum)) {
      center.value = [latNum, lonNum];
    }
    const delta = 0.05; // ~5-6km aprox
    const refLat = Number.isFinite(latNum) ? latNum : 40.4168;
    const refLon = Number.isFinite(lonNum) ? lonNum : -3.7038;
    const west = (refLon - delta).toFixed(5);
    const south = (refLat - delta).toFixed(5);
    const east = (refLon + delta).toFixed(5);
    const north = (refLat + delta).toFixed(5);
    const bbox = `${west},${south},${east},${north}`;
    const res = await axiosClient.get('/tomtom/traffic-incidents', { params: { bbox } });
    incidentes.value = res.data.incidents || [];

    await nextTick();
    initMap();
  } catch (err) {
    console.error("Error al cargar incidentes de tráfico:", err);
    const details = err?.response?.data?.details || err?.message;
    error.value = details ? `No se pudieron cargar las alertas de tráfico. ${details}` : 'No se pudieron cargar las alertas de tráfico.';
  } finally {
    cargando.value = false;
  }
});

// Resumen para hero
const totalIncidentes = computed(() => incidentes.value.length || 0);
const categoriasContadas = computed(() => {
  const map = new Map();
  for (const it of incidentes.value) {
    const k = it?.properties?.iconCategory;
    map.set(k, (map.get(k) || 0) + 1);
  }
  return Array.from(map.entries()).slice(0, 3); // top 3
});
</script>

<template>
  <div class="relative w-full min-h-screen px-10 bg-center bg-cover"
    style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 min-h-screen p-4 text-[color:var(--color-text)] pt-10">
      <h1 class="pt-10 mb-6 text-3xl font-bold tracking-tight text-center page-title">Incidencias de tráfico</h1>

      <div v-if="cargando" class="flex items-center justify-center min-h-[30vh]">
        <div class="flex flex-col items-center gap-3">
          <div class="spinner" aria-label="Cargando"></div>
          <div class="loader-text">Cargando incidentes...</div>
        </div>
      </div>

      <div v-else-if="error" class="max-w-md p-4 mx-auto text-center border rounded-xl border-white/20 frost-card">
        {{ error }}
      </div>

      <div v-else class="flex flex-col gap-6">
        <!-- Hero -->
        <div class="p-5 border frost-card border-white/15 rounded-2xl">
          <div class="flex items-center justify-between gap-3">
            <div>
              <div class="inline-flex items-center px-3 py-1 text-xs font-semibold text-black bg-yellow-500 rounded-lg">
                Alertas activas
              </div>
              <div class="mt-3 text-4xl font-extrabold leading-tight">{{ totalIncidentes }}</div>
              <div class="mt-1 text-sm text-white/70">en los alrededores</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-white/70">Principales categorías</div>
              <div class="mt-1 text-sm text-white/80">
                <template v-for="([cat, count], i) in categoriasContadas" :key="cat">
                  <span v-if="i > 0" class="text-white/50"> · </span>
                  <span>{{ descripcionCategoria(cat) }} ({{ count }})</span>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Mapa -->
        <div class="p-4 border frost-card border-white/15 rounded-2xl">
          <div class="w-full h-[360px] md:h-[460px] lg:h-[560px] rounded-lg overflow-hidden">
            <div id="mapa" class="w-full h-full"></div>
          </div>
        </div>

        <!-- Lista de incidentes -->
        <h2 class="text-xl font-semibold">Listado de incidencias</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div v-for="(incidente, index) in incidentes" :key="index" @click="focusIncidente(incidente, index)"
            @keydown.enter.prevent.stop="focusIncidente(incidente, index)"
            @keydown.space.prevent.stop="focusIncidente(incidente, index)" role="button" tabindex="0" :class="[
              'p-4 transition border frost-card rounded-2xl hover:opacity-95 outline-none',
              selectedIdx === index
                ? 'border-white/40 ring-2 ring-[color:var(--color-primary)]/60'
                : 'border-white/15'
            ]">
            <div class="flex items-start justify-between mb-1">
              <div class="font-bold leading-snug">{{ nombreVia(incidente.properties) }}</div>
              <span class="mt-0.5 w-4 h-4 rounded-full flex-shrink-0"
                :style="{ backgroundColor: colorCategoria(incidente.properties.iconCategory) }"></span>
            </div>
            <div class="mb-1 text-xs tracking-wide uppercase text-white/70">
              {{ descripcionCategoria(incidente.properties.iconCategory) }}
            </div>
            <div class="text-sm text-white/80">
              <strong>Inicio:</strong>
              {{ incidente.geometry.coordinates[0][1].toFixed(6) }}, {{ incidente.geometry.coordinates[0][0].toFixed(6)
              }}
              <span class="mx-2 text-white/50">·</span>
              <strong>Fin:</strong>
              {{ incidente.geometry.coordinates.slice(-1)[0][1].toFixed(6) }}, {{
                incidente.geometry.coordinates.slice(-1)[0][0].toFixed(6) }}
            </div>
          </div>
        </div>
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

<style>
.leaflet-container {
  height: 100%;
  width: 100%;
  border-radius: 0.5rem;
}
</style>
