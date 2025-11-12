<script setup>
import { ref, onMounted, watch, nextTick, onBeforeUnmount } from "vue";
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";
import PlayaPickerModal from "~/components/PlayaPickerModal.vue";
import 'leaflet/dist/leaflet.css'

const datos = ref([]);
const nombrePlaya = ref("");
const openPicker = ref(false);
const municipioId = ref(null);
const municipioName = ref("");
const cpro = ref(null);
const codigoPlaya = ref(null);
const playaProvCpro = ref(null);
const loading = ref(true);
const savingSelection = ref(false);
const error = ref(null);

// Coordenadas y mapa
const lat = ref(null);
const lon = ref(null);
const mapEl = ref(null);
let map = null;
let marker = null;
let Llib = null;

function parseCoord(v) {
  if (v == null) return NaN;
  if (typeof v === 'string') {
    const s = v.replace(',', '.');
    const n = parseFloat(s);
    return Number.isFinite(n) ? n : NaN;
  }
  const n = Number(v);
  return Number.isFinite(n) ? n : NaN;
}

function normalizeCoords(la, lo, cpro) {
  let latN = la, lonN = lo;
  // Heurística: si la "lat" es pequeña (<10) y la "lon" está entre 20 y 60, seguramente están intercambiadas
  if (Number.isFinite(latN) && Number.isFinite(lonN)) {
    if (Math.abs(latN) < 10 && Math.abs(lonN) >= 20 && Math.abs(lonN) <= 60) {
      const tmp = latN; latN = lonN; lonN = tmp;
    }
    // Si ya es negativa (Oeste), no tocar.
    if (lonN >= 0) {
      // España: permitir positivo sólo en Balears (07) y Cataluña (08,17,43,25)
      const cp = String(cpro || '').padStart(2,'0');
      const allowPositive = (cp === '07' || cp === '08' || cp === '17' || cp === '43' || cp === '25');
      if (!allowPositive && lonN > 0) { lonN = -Math.abs(lonN); }
    }
  }
  return [latN, lonN];
}

async function ensureLeaflet() {
  if (!Llib) {
    const m = await import('leaflet');
    Llib = m.default || m;
    try {
      const iconBase = 'https://unpkg.com/leaflet@1.9.4/dist/images/';
      Llib.Icon.Default.mergeOptions({
        iconUrl: iconBase + 'marker-icon.png',
        iconRetinaUrl: iconBase + 'marker-icon-2x.png',
        shadowUrl: iconBase + 'marker-shadow.png',
      });
    } catch (e) { }
  }
}

function hasCoords() {
  const la = parseCoord(lat.value), lo = parseCoord(lon.value);
  return Number.isFinite(la) && Number.isFinite(lo);
}

async function initMap() {
  if (!mapEl.value || !hasCoords()) return;
  await ensureLeaflet();
  await nextTick();
  let [la, lo] = normalizeCoords(parseCoord(lat.value), parseCoord(lon.value), playaProvCpro.value);
  const center = [la, lo];
  if (!map) {
    map = Llib.map(mapEl.value, { zoomControl: true }).setView(center, 12);
    Llib.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap',
      minZoom: 2,
      maxNativeZoom: 19,
      subdomains: ['a', 'b', 'c'],
      style: 'mapbox://styles/mapbox/streets-v11'
    }).addTo(map);
    const content = `${nombrePlaya.value || 'Playa seleccionada'}<br><small>${la.toFixed(6)}, ${lo.toFixed(6)}</small>`;
    marker = Llib.circleMarker(center, { radius: 6, color: '#3b82f6', weight: 2, fillColor: '#60a5fa', fillOpacity: 0.9 }).addTo(map).bindPopup(content, { closeButton: false });
    try { marker.openPopup(); } catch (e) { }
  } else {
    map.setView(center, map.getZoom() || 13);
    const content = `${nombrePlaya.value || 'Playa seleccionada'}<br><small>${la.toFixed(6)}, ${lo.toFixed(6)}</small>`;
    if (marker) { marker.setLatLng(center).bindPopup(content); try { marker.openPopup(); } catch (e) { } }
    else { marker = Llib.circleMarker(center, { radius: 6, color: '#3b82f6', weight: 2, fillColor: '#60a5fa', fillOpacity: 0.9 }).addTo(map).bindPopup(content); try { marker.openPopup(); } catch (e) { } }
  }
}

function updateCoordsFromResponse(playa) {
  const la = playa?.lat ?? playa?.Lat ?? playa?.latitude ?? playa?.Latitud ?? playa?.latitud;
  const lo = playa?.lon ?? playa?.lng ?? playa?.Lon ?? playa?.longitude ?? playa?.Longitud ?? playa?.longitud;
  const prov = playa?.id_provincia ?? playa?.cpro ?? playa?.provincia;
  if (prov != null) playaProvCpro.value = String(prov).padStart(2,'0');
  let pLa = parseCoord(la); let pLo = parseCoord(lo);
  ;[pLa, pLo] = normalizeCoords(pLa, pLo, playaProvCpro.value);
  if (Number.isFinite(pLa) && Number.isFinite(pLo)) { lat.value = pLa; lon.value = pLo; }
}

async function fetchCoordsById(idPlaya) {
  try {
    if (!idPlaya) return;
    const { data } = await axiosClient.get('/playas', { params: { id_playa: idPlaya, fields: 'lat,lon,id_provincia', limit: 1 } });
    const row = Array.isArray(data) ? data[0] : data;
    if (row?.id_provincia != null) playaProvCpro.value = String(row.id_provincia).padStart(2,'0');
    let pLa = parseCoord(row?.lat); let pLo = parseCoord(row?.lon);
    ;[pLa, pLo] = normalizeCoords(pLa, pLo, playaProvCpro.value);
    if (Number.isFinite(pLa) && Number.isFinite(pLo)) { lat.value = pLa; lon.value = pLo; }
  } catch (e) { /* ignore */ }
}

// Función para formatear fecha estilo "lun 02"
function formatearFechaYYYYMMDD(fechaNum) {
  if (!fechaNum) return "—";
  const str = fechaNum.toString();
  const year = str.substring(0, 4);
  const month = str.substring(4, 6);
  const day = str.substring(6, 8);
  const fecha = new Date(`${year}-${month}-${day}`);
  const opciones = { weekday: "short", day: "2-digit" };
  return new Intl.DateTimeFormat("es-ES", opciones)
    .format(fecha)
    .replace(".", "");
}

async function cargarPrediccion(idPlaya) {
  try {
    loading.value = true; error.value = null;
    const { data } = await axiosClient.get(`/aemet/playa/${idPlaya}`, { params: { t: Date.now() } });
    const playa = Array.isArray(data) ? data[0] : data;
    nombrePlaya.value = playa?.nombre || "";
    datos.value = playa?.prediccion?.dia || [];
    if (!hasCoords()) {
      updateCoordsFromResponse(playa);
    }
    if (!hasCoords()) { await fetchCoordsById(idPlaya); }
  } catch (e) {
    error.value = e?.message || 'Error cargando predicción de playa';
  } finally {
    loading.value = false;
    await nextTick();
    await initMap();
  }
}

async function guardarSeleccion(pl) {
  savingSelection.value = true;
  try {
    const uid = userData()?.value?.id;
    await axiosClient.post('/user/location-pref', { user_id: uid, codigo_playa: pl.id_playa });
    codigoPlaya.value = pl.id_playa;
    if (pl?.id_provincia != null) playaProvCpro.value = String(pl.id_provincia).padStart(2,'0');
    nombrePlaya.value = pl.nombre_playa;
    // Guardar coordenadas desde el selector si están disponibles
    if (pl) {
      let pLa = parseCoord(pl.lat ?? pl.Lat ?? pl.latitude ?? pl.Latitud ?? pl.latitud);
      let pLo = parseCoord(pl.lon ?? pl.lng ?? pl.Lon ?? pl.longitude ?? pl.Longitud ?? pl.longitud);
      ;[pLa, pLo] = normalizeCoords(pLa, pLo, playaProvCpro.value);
      if (Number.isFinite(pLa) && Number.isFinite(pLo)) { lat.value = pLa; lon.value = pLo; }
    }
    await cargarPrediccion(pl.id_playa);
  } catch (e) {
    console.error(e);
  } finally {
    savingSelection.value = false;
    openPicker.value = false;
  }
}

onMounted(async () => {
  try {
    const uid = userData()?.value?.id;
    const { data } = await axiosClient.get('/user/location-pref', { params: uid ? { user_id: uid } : {} });
    municipioId.value = data?.municipio_id != null ? String(data.municipio_id) : null;
    municipioName.value = data?.municipio_name || '';
    cpro.value = data?.cpro ? String(data.cpro).padStart(2, '0') : null;
    codigoPlaya.value = data?.codigo_playa || null;
    if (codigoPlaya.value) {
      await cargarPrediccion(codigoPlaya.value);
    } else {
      openPicker.value = true;
    }
  } catch (e) {
    error.value = e?.message || 'Error cargando preferencias de usuario';
  } finally {
    loading.value = false;
  }
});

watch([lat, lon], async () => { if (!loading.value) { await initMap(); } });

onBeforeUnmount(() => { try { if (map) { map.remove(); map = null; marker = null; } } catch (e) { } });
</script>

<template>
  <div class="relative w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 min-h-screen p-4 pt-10 text-gray-200">
      <h1 class="mb-4 text-3xl font-extrabold tracking-tight text-center md:text-4xl page-title">Previsión en la playa
      </h1>
      <div v-if="!loading" class="flex flex-wrap items-center justify-center gap-3 mb-4 text-center">
        <h2 class="flex items-center gap-2 text-2xl font-semibold text-white/95">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-white/90">
            <path fill-rule="evenodd"
              d="M12 2.25c-3.728 0-6.75 3.022-6.75 6.75 0 4.637 5.37 10.164 6.1 10.89a.75.75 0 0 0 1.1 0c.73-.726 6.3-6.253 6.3-10.89 0-3.728-3.022-6.75-6.75-6.75Zm0 9.75a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"
              clip-rule="evenodd" />
          </svg>
          <span>{{ nombrePlaya || 'Selecciona una playa' }}</span>
        </h2>
        <button @click="openPicker = true" class="btn-glass-primary">
          {{ nombrePlaya ? 'Cambiar' : 'Elegir playa' }}
        </button>
      </div>

      <div v-if="loading" class="flex items-center justify-center min-h-[30vh]">
        <div class="flex flex-col items-center gap-3">
          <div class="spinner" aria-label="Cargando"></div>
          <div class="loader-text">Cargando datos...</div>
        </div>
      </div>
      <div v-else-if="error" class="text-red-300">{{ error }}</div>
      <div v-else-if="!codigoPlaya" class="text-slate-200">Elige una playa para ver la predicción.</div>

      <div v-else-if="datos && datos.length" class="space-y-4">
        <div class="p-4 overflow-x-auto border frost-card border-white/15 rounded-2xl">
          <table class="min-w-full border-collapse">
            <thead>
              <tr class="glass-header text-[color:var(--color-text-muted)]">
                <th class="p-2 text-left">Fecha</th>
                <th class="p-2">Cielo</th>
                <th class="p-2">Viento</th>
                <th class="p-2">Oleaje</th>
                <th class="p-2">Tª máx</th>
                <th class="p-2">Sens. térmica</th>
                <th class="p-2">Tª agua</th>
                <th class="p-2">UV máx</th>
              </tr>
            </thead>
            <tbody class="glass-body">
              <tr v-for="dia in datos" :key="dia.fecha"
                class="border-b theme-border hover:bg-[color:var(--color-overlay-weak)]">
                <!-- Fecha -->
                <td class="p-2 font-semibold">
                  {{ formatearFechaYYYYMMDD(dia.fecha) }}
                </td>

                <!-- Estado del cielo -->
                <td class="p-2 text-center">
                  {{ dia.estadoCielo?.descripcion1 || "—" }}
                </td>

                <!-- Viento -->
                <td class="p-2 text-center">
                  {{ dia.viento?.descripcion1 || "—" }}
                </td>

                <!-- Oleaje -->
                <td class="p-2 text-center">
                  {{ dia.oleaje?.descripcion1 || "—" }}
                </td>

                <!-- Temperatura máxima -->
                <td class="p-2 text-center">
                  {{ dia.tMaxima?.valor1 || dia.tmaxima?.valor1 || "—" }}°C
                </td>

                <!-- Sensación térmica -->
                <td class="p-2 text-center">
                  {{
                    dia.sTermica?.descripcion1 || dia.stermica?.descripcion1 || "—"
                  }}
                </td>

                <!-- Temperatura del agua -->
                <td class="p-2 text-center">
                  {{ dia.tAgua?.valor1 || dia.tagua?.valor1 || "—" }}°C
                </td>

                <!-- Índice UV máximo -->
                <td class="p-2 text-center">
                  {{ dia.uvMax?.valor1 || "—" }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="hasCoords()" class="p-4 border frost-card border-white/15 rounded-2xl">
          <h3 class="mb-2 text-sm font-semibold">Mapa</h3>
          <div ref="mapEl" class="w-full h-64 overflow-hidden rounded-xl"></div>
        </div>
      </div>

      <PlayaPickerModal :open="openPicker" :municipio-id="municipioId" :cpro="cpro" :municipio-name="municipioName"
        :saving="savingSelection" @close="openPicker = false" @selected="guardarSeleccion" />
    </div>
  </div>
</template>

<style scoped>
.page-title {
  color: #ffffff !important;
}

/* Glass y colores replicados de meteo/horaria.vue */
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

.glass-header {
  background-image: linear-gradient(to bottom, color-mix(in srgb, var(--color-bg) 20%, transparent), color-mix(in srgb, var(--color-bg) 20%, transparent));
}

.glass-body td {
  color: inherit;
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

  .glass-header {
    background-image: linear-gradient(to bottom,
        color-mix(in srgb, white 28%, transparent),
        color-mix(in srgb, white 28%, transparent));
  }
}

:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
  color: #ffffff !important;
}

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

/* Leaflet: asegurar contraste de controles y fondo del mapa */
:deep(.leaflet-container) { background: #dbeafe; /* sky-100 aprox para evitar parpadeo azul */ }
:deep(.leaflet-control-zoom a) {
  background: rgba(255,255,255,0.9);
  color: #111827; /* slate-900 */
  border: 1px solid rgba(0,0,0,0.15);
  box-shadow: 0 1px 2px rgba(0,0,0,0.12);
}
:deep(.leaflet-control-zoom a:hover) { background: #ffffff; }
@media (prefers-color-scheme: dark) {
  :deep(.leaflet-control-zoom a) {
    background: rgba(17,24,39,0.85); /* slate-900 */
    color: #e5e7eb; /* gray-200 */
    border-color: rgba(255,255,255,0.2);
  }
  :deep(.leaflet-control-zoom a:hover) { background: rgba(17,24,39,0.95); }
}
/* Evitar filtros accidentales en tiles/markers por estilos globales */
:deep(.leaflet-pane img),
:deep(.leaflet-pane canvas) { filter: none !important; opacity: 1 !important; }

/* Botón glass primario con buen contraste en ambos temas */
.btn-glass-primary {
  padding: 0.375rem 0.75rem;
  /* ~py-1.5 px-3 */
  font-size: 0.875rem;
  border-radius: 0.75rem;
  /* ~rounded-xl */
  border: 1px solid rgba(255, 255, 255, 0.25);
  color: #fff;
  background-image: linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 16%, transparent),
      color-mix(in srgb, var(--color-primary) 16%, transparent)),
    linear-gradient(to bottom,
      rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.08));
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  transition: background-color .2s ease, opacity .2s ease, border-color .2s ease;
}

.btn-glass-primary:hover {
  border-color: rgba(255, 255, 255, 0.35);
  background-image: linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 22%, transparent),
      color-mix(in srgb, var(--color-primary) 22%, transparent)),
    linear-gradient(to bottom,
      rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.12));
}

.btn-glass-primary:disabled {
  opacity: .6;
  cursor: not-allowed;
}

@media (prefers-color-scheme: light) {
  .btn-glass-primary {
    color: #0f172a;
    /* slate-900 aprox para máximo contraste sobre velo claro */
    border-color: rgba(0, 0, 0, 0.1);
    background-image: linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 14%, transparent),
        color-mix(in srgb, var(--color-primary) 14%, transparent)),
      linear-gradient(to bottom, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0.55));
  }

  .btn-glass-primary:hover {
    border-color: rgba(0, 0, 0, 0.18);
    background-image: linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 18%, transparent),
        color-mix(in srgb, var(--color-primary) 18%, transparent)),
      linear-gradient(to bottom, rgba(255, 255, 255, 0.65), rgba(255, 255, 255, 0.65));
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}
</style>
