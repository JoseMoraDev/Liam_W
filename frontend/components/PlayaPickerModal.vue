<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-end justify-center p-4">
    <div class="absolute inset-0 bg-black/40" @click="onClose" />
    <div
      ref="panelEl"
      class="relative z-10 w-full max-w-2xl p-4 mb-5 border rounded-2xl panel-glass border-white/15 max-h-[85vh] overflow-y-auto">
      <div class="mx-auto mb-2 h-1.5 w-12 rounded-full bg-white/30"></div>
      <header class="sticky top-0 z-10 flex items-center justify-between pb-2 mb-3 header-glass">
        <h2 class="text-lg font-semibold text-white">Selecciona una playa</h2>
        <div class="flex items-center gap-2">
          <button :disabled="props.saving" @click="onClose"
            class="px-3 py-2 text-sm rounded-md btn-glass disabled:opacity-50">Cancelar</button>
          <button :disabled="!sel || props.saving" @click="confirmar"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white btn-primary-glass rounded-md disabled:opacity-50">
            <span v-if="props.saving"
              class="inline-block w-4 h-4 border-2 rounded-full border-white/80 border-t-transparent animate-spin"></span>
            <span>{{ props.saving ? 'Guardando…' : 'Guardar' }}</span>
          </button>
        </div>
      </header>

      <!-- Mapa preview (arriba para que siempre se vea) -->
      <div class="mt-2">
        <h3 class="mb-2 text-sm font-semibold text-white">Mapa</h3>
        <div ref="mapEl" class="w-full h-64 min-h-[16rem] overflow-hidden border rounded-xl border-white/15 map-glass"></div>
      </div>

      <div class="mt-4 space-y-6">
        <!-- Playas del municipio (siempre visibles) -->
        <section>
          <h3 class="mb-2 text-sm font-semibold text-gray-700 uppercase dark:text-gray-200">Playas de {{ municipioName
            || 'tu municipio' }}</h3>
          <div class="grid grid-cols-1 gap-2 pr-1 overflow-auto md:grid-cols-2 max-h-72">
            <button v-for="p in playasMunicipio" :key="p.id_playa" @click="!props.saving && select(p)"
              :class="['w-full text-left px-3 py-2 rounded-md border option-btn', sel?.id_playa === p.id_playa ? 'opt-active' : 'opt-default']">
              <div class="text-sm font-medium text-white">{{ p.nombre_playa }}</div>
              <div class="text-xs italic text-white/90">{{ p.nombre_municipio }} · {{ p.nombre_provincia }}</div>
            </button>
            <p v-if="!playasMunicipio.length" class="text-sm text-gray-500">Sin resultados.</p>
          </div>
        </section>

        <!-- Playas de la provincia (excluyendo municipio) -->
        <section>
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-white uppercase">Playas de</h3>
            <select v-model="provSel" @change="fetchProvincia"
              class="px-3 py-1 text-sm border rounded-md chip-glass border-white/15 text-white">
              <option v-for="p in provincias" :key="p.id_provincia" :value="p.id_provincia">
                {{ p.nombre_provincia }}
              </option>
            </select>
          </div>
          <div class="mb-2">
            <input v-model="q" @input="debouncedBuscar" type="text" placeholder="Busca aquí tu playa"
              class="w-full px-3 py-2 border rounded-md chip-glass border-white/15 placeholder-white/60 text-white" />
          </div>
          <div ref="provListEl" class="grid grid-cols-1 gap-2 pr-1 overflow-auto sm:grid-cols-2 md:grid-cols-2 max-h-48">
            <button v-for="p in playasProvinciaFiltradas" :key="p.id_playa" @click="!props.saving && select(p)"
              :class="['w-full text-left px-3 py-2 rounded-md border option-btn', sel?.id_playa === p.id_playa ? 'opt-active' : 'opt-default']">
              <div class="text-sm font-medium text-white">{{ p.nombre_playa }}</div>
              <div class="text-xs italic text-white/90">{{ p.nombre_municipio }} · {{ p.nombre_provincia }}</div>
            </button>
            <p v-if="!playasProvinciaFiltradas.length" class="text-sm text-gray-500">Sin resultados.</p>
          </div>
        </section>
      </div>

      <!-- Mapa preview (movido arriba) -->

      <!-- Footer eliminado: acciones movidas al header -->
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { axiosClient } from '~/axiosConfig'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  open: { type: Boolean, default: false },
  municipioId: { type: [String, Number], default: null },
  cpro: { type: [String, Number], default: null },
  municipioName: { type: String, default: '' },
  saving: { type: Boolean, default: false },
  selectedId: { type: [String, Number], default: null },
  selectedLat: { type: [String, Number], default: null },
  selectedLon: { type: [String, Number], default: null },
})
const emit = defineEmits(['close', 'selected'])

const provincias = ref([])
const playasMunicipio = ref([])
const playasProvincia = ref([])
const sel = ref(null)
const provSel = ref(props.cpro ? String(props.cpro).padStart(2, '0') : null)
const q = ref('')
let t = null
let map = null
let marker = null
let Llib = null
let resizeHandler = null
let ro = null
const mapEl = ref(null)
const panelEl = ref(null)
const resolvedMunicipioId = ref(null) // código concatenado resuelto dinámicamente
const loadingLists = ref(false)

function onClose() { emit('close') }
async function computeMunicipioMid() {
  // 1) Si ya está normalizado o previamente resuelto, úsalo
  if (normalizedMunicipioId.value) return normalizedMunicipioId.value
  if (resolvedMunicipioId.value) return resolvedMunicipioId.value

  // 2) Si props.municipioId viene en 5 dígitos, úsalo
  if (props.municipioId != null) {
    const raw = String(props.municipioId)
    if (/^\d{5}$/.test(raw)) { resolvedMunicipioId.value = raw; return raw }
    // 3) Si no es 5 dígitos (p.ej. PK 203), delegar resolución al backend
    try {
      const byId = await axiosClient.get('/playas', { params: { municipio: raw, fields: 'id_municipio,id_provincia', limit: 1 } })
      const first = Array.isArray(byId.data) && byId.data[0] ? byId.data[0] : null
      if (first && first.id_municipio) {
        const mid = String(first.id_municipio)
        resolvedMunicipioId.value = mid
        if (!provSel.value && first.id_provincia) {
          provSel.value = String(first.id_provincia).padStart(2, '0')
        }
        return mid
      }
    } catch (e) { /* ignore */ }
  }

  // 4) Resolver por nombre (Elx/Elche) y tomar id_municipio (5 dígitos)
  if (props.municipioName) {
    try {
      const params = { municipio_nombre: `%${props.municipioName}%`, fields: 'id_municipio,id_provincia', limit: 1 }
      if (props.cpro != null) params.id_provincia = String(props.cpro).padStart(2, '0')
      const byName = await axiosClient.get('/playas', { params })
      const first = Array.isArray(byName.data) && byName.data[0] ? byName.data[0] : null
      if (first && first.id_municipio) {
        const mid = String(first.id_municipio)
        resolvedMunicipioId.value = mid
        if (!provSel.value && first.id_provincia) {
          provSel.value = String(first.id_provincia).padStart(2, '0')
        }
        return mid
      }
    } catch (e) { /* ignore */ }
  }
  return null
}

async function select(p) {
  sel.value = p
  // Alinear provincia inferior con la playa seleccionada arriba
  try {
    if (p && p.id_provincia != null) {
      const nextProv = String(p.id_provincia).padStart(2, '0')
      if (provSel.value !== nextProv) {
        provSel.value = nextProv
        await fetchProvincia()
      }
    }
  } catch (e) { }
  // Actualizar el mapa inmediatamente tras seleccionar
  try {
    if (!map && props.open) { await initMap() }
    updateMarker()
    setTimeout(() => { try { map && map.invalidateSize() } catch (e) { } }, 50)
  } catch (e) { }
}
function confirmar() { if (sel.value) { emit('selected', sel.value) } }

const currentProvinciaName = computed(() => {
  const p = provincias.value.find(x => String(x.id_provincia) === String(provSel.value))
  return p ? p.nombre_provincia : 'provincia'
})

const LS_KEY = 'playaPicker.lastProvince'

// Municipio concatenado cpro(2)+cmun(3). Si ya viene a 5 dígitos lo usa; si es PK (<=4) concatena con cpro
const normalizedMunicipioId = computed(() => {
  const midRaw = props.municipioId != null ? String(props.municipioId) : ''
  if (/^\d{5}$/.test(midRaw)) return midRaw
  if (/^\d{1,4}$/.test(midRaw) && props.cpro != null) {
    const cpro2 = String(props.cpro).padStart(2, '0')
    const cmun3 = midRaw.padStart(3, '0')
    return cpro2 + cmun3
  }
  return null
})

const playasProvinciaFiltradas = computed(() => {
  const idsMun = new Set(playasMunicipio.value.map(p => p.id_playa))
  return playasProvincia.value.filter(p => !idsMun.has(p.id_playa))
})
// Limitar a ~3 filas: 6 elementos (2 columnas en sm/md). En móvil (1 col), serán 3 elementos.
const playasProvinciaLimited = computed(() => {
  const arr = playasProvinciaFiltradas.value || []
  return arr.slice(0, 6)
})

async function fetchProvincias() {
  loadingLists.value = true
  try {
    const { data } = await axiosClient.get('/playas', { params: { fields: 'id_provincia,nombre_provincia', limit: 5000 } })
    const map = new Map()
    for (const r of data) {
      const pid = String(r.id_provincia).padStart(2, '0')
      map.set(pid, r.nombre_provincia)
    }
    provincias.value = Array.from(map, ([id_provincia, nombre_provincia]) => ({ id_provincia, nombre_provincia })).sort((a, b) => a.nombre_provincia.localeCompare(b.nombre_provincia))
  } catch (e) { /* ignore */ }
  finally { loadingLists.value = false }
}

async function fetchMunicipio() {
  // Requiere municipio concatenado. Si no se puede construir, vacía.
  loadingLists.value = true
  const mid = await computeMunicipioMid()
  if (!mid) { playasMunicipio.value = []; return }
  // Formar variables solicitadas de forma dinámica
  const codigo_provincia = mid.slice(0, 2)
  const codigo_municipio = mid.slice(2, 5)
  // Asegurar selección de provincia por defecto
  if (!provSel.value) { provSel.value = codigo_provincia }
  const params = { municipio: mid, fields: 'id_playa,nombre_playa,nombre_municipio,nombre_provincia,id_provincia,lat,lon', limit: 2000 }
  const { data } = await axiosClient.get('/playas', { params })
  playasMunicipio.value = Array.isArray(data) ? data : []
  if (!sel.value && playasMunicipio.value.length) {
    sel.value = playasMunicipio.value[0]
  }
  // Si no hay provincia seleccionada, tomar la de la primera playa del municipio
  if (!provSel.value && playasMunicipio.value.length) {
    provSel.value = String(playasMunicipio.value[0].id_provincia).padStart(2, '0')
    await fetchProvincia()
  } else if (!playasMunicipio.value.length && provSel.value) {
    // Si no hay playas de municipio pero sí cpro, cargar provincia igualmente (sin fallback adicional)
    await fetchProvincia()
  }
  // Tras tener datos municipales, si el modal está abierto, asegurar mapa
  if (props.open) { await initMap(); updateMarker() }
  loadingLists.value = false
}

async function fetchProvincia() {
  loadingLists.value = true
  if (!provSel.value) { playasProvincia.value = []; return }
  const excl = (await computeMunicipioMid()) || undefined
  const params = { provincia: String(provSel.value).padStart(2, '0'), exclude_municipio: excl, fields: 'id_playa,nombre_playa,nombre_municipio,nombre_provincia,lat,lon', limit: 2000 }
  if (q.value) params.q = q.value
  const { data } = await axiosClient.get('/playas', { params })
  // Excluir también en cliente por si exclude_municipio no coincide en formato
  const idsMun = new Set(playasMunicipio.value.map(p => p.id_playa))
  playasProvincia.value = (data || []).filter(p => !idsMun.has(p.id_playa))
  if (!sel.value && !playasMunicipio.value.length && playasProvincia.value.length) {
    sel.value = playasProvincia.value[0]
  }
  // Resetear scroll de la lista de provincia al principio
  try {
    if (provListEl && provListEl.value) {
      provListEl.value.scrollTop = 0
    }
  } catch (e) { /* ignore */ }
  // Preseleccionar la playa activa si está en la lista de provincia
  try {
    if (!sel.value && props.selectedId != null) {
      const s = playasProvincia.value.find(p => String(p.id_playa) === String(props.selectedId))
      if (s) sel.value = s
    }
  } catch (e) { }
}

function debouncedBuscar() {
  clearTimeout(t); t = setTimeout(async () => { await fetchProvincia() }, 250)
}

// init: cargar provincias, resolver municipio y fijar provincia en ese orden
await fetchProvincias()
if (props.cpro) {
  provSel.value = String(props.cpro).padStart(2, '0')
}
await fetchMunicipio()
await fetchProvincia()

// Si llega selectedId desde el padre, asegurar selección y provincia
try {
  if (props.selectedId != null) {
    const inMun = playasMunicipio.value.find(p => String(p.id_playa) === String(props.selectedId))
    const inProv = playasProvincia.value.find(p => String(p.id_playa) === String(props.selectedId))
    const s = inMun || inProv
    if (s) {
      sel.value = s
      if (s.id_provincia != null) provSel.value = String(s.id_provincia).padStart(2, '0')
    } else {
      // Buscar por id para centrar el mapa aunque no esté en las listas actuales
      const { data } = await axiosClient.get('/playas', { params: { id_playa: props.selectedId, fields: 'id_playa,lat,lon,id_provincia,nombre_playa,nombre_municipio,nombre_provincia', limit: 1 } })
      const row = Array.isArray(data) ? data[0] : data
      if (row && row.id_playa) {
        sel.value = row
        if (row.id_provincia != null) provSel.value = String(row.id_provincia).padStart(2, '0')
        await ensureMapReady()
      }
    }
  }
} catch (e) { }


function getFirstCoords() {
  // Prioridad: selección activa; luego coords pasadas desde el padre; luego listas
  if (sel.value) {
    const lat = Number(sel.value.lat); const lon = Number(sel.value.lon)
    if (!Number.isNaN(lat) && !Number.isNaN(lon)) return [lat, lon]
  }
  if (props.selectedLat != null && props.selectedLon != null) {
    const la = Number(props.selectedLat); const lo = Number(props.selectedLon)
    if (!Number.isNaN(la) && !Number.isNaN(lo)) return [la, lo]
  }
  const src = (playasMunicipio.value.length ? playasMunicipio.value : playasProvincia.value)
  if (src && src.length) {
    const p = src[0]
    const lat = Number(p.lat); const lon = Number(p.lon)
    if (!Number.isNaN(lat) && !Number.isNaN(lon)) return [lat, lon]
  }
  return [40.4168, -3.7038] // Madrid fallback
}

async function ensureLeaflet() {
  if (typeof window === 'undefined') return
  if (!Llib) {
    const m = await import('leaflet')
    Llib = m.default || m
    try {
      const iconBase = 'https://unpkg.com/leaflet@1.9.4/dist/images/'
      Llib.Icon.Default.mergeOptions({
        iconUrl: iconBase + 'marker-icon.png',
        iconRetinaUrl: iconBase + 'marker-icon-2x.png',
        shadowUrl: iconBase + 'marker-shadow.png',
      })
    } catch (e) { }
  }
}

async function initMap() {
  await ensureLeaflet()
  await nextTick()
  if (map) return
  const el = mapEl.value
  if (!el) return
  // Esperar a que el modal esté visible para calcular tamaño correctamente
  await new Promise(r => setTimeout(r, 60))
  const center = getFirstCoords()
  map = Llib.map(el, { zoomControl: true }).setView(center, 10)
  Llib.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }).addTo(map)
  // Usar circleMarker para evitar dependencia de iconos externos
  marker = Llib.circleMarker(center, { radius: 6, color: '#3b82f6', weight: 2, fillColor: '#60a5fa', fillOpacity: 0.9 }).addTo(map)
  // Invalidate size tras abrir para corregir cálculos en contenedores ocultos
  requestAnimationFrame(() => { try { map && map.invalidateSize() } catch (e) { } })
  setTimeout(() => { try { map && map.invalidateSize() } catch (e) { } }, 120)
  // Listener de resize
  resizeHandler = () => { try { map.invalidateSize() } catch (e) { } }
  window.addEventListener('resize', resizeHandler)
  // Observer de cambios de tamaño/visibilidad del contenedor
  try {
    ro = new ResizeObserver(() => { try { map && map.invalidateSize() } catch (e) { } })
    ro.observe(el)
  } catch (e) { }
}

function updateMarker() {
  if (!map) return
  const center = getFirstCoords()
  map.setView(center, map.getZoom() || 10)
  if (marker) { marker.setLatLng(center) } else { marker = Llib.circleMarker(center, { radius: 6, color: '#3b82f6', weight: 2, fillColor: '#60a5fa', fillOpacity: 0.9 }).addTo(map) }
}

async function ensureMapReady() {
  await nextTick()
  const el = mapEl.value
  if (!el) return
  if (!map) { await initMap() }
  try {
    const r = el.getBoundingClientRect()
    if (r.width < 50 || r.height < 50) {
      setTimeout(() => { try { map && map.invalidateSize() } catch (e) { } }, 60)
      setTimeout(() => { try { map && map.invalidateSize() } catch (e) { } }, 140)
    } else {
      try { map && map.invalidateSize() } catch (e) { }
    }
  } catch (e) { }
  updateMarker()
}

watch(() => props.open, async (v) => {
  if (v) {
    q.value = ''
    try { if (panelEl && panelEl.value) panelEl.value.scrollTop = 0 } catch (e) { }
    await ensureMapReady()
    await fetchProvincias()
    if (props.cpro) provSel.value = String(props.cpro).padStart(2, '0')
    await fetchMunicipio()
    await fetchProvincia()
    // Reafirmar la playa activa pasada desde el padre
    try {
      if (props.selectedId != null) {
        const s = (playasMunicipio.value.concat(playasProvincia.value)).find(p => String(p.id_playa) === String(props.selectedId))
        if (s) {
          sel.value = s
          if (s.id_provincia != null) provSel.value = String(s.id_provincia).padStart(2, '0')
        }
      }
    } catch (e) { }
    await ensureMapReady()
  } else {
    try { if (map) { map.remove(); map = null; marker = null } } catch (e) { }
  }
})
watch([sel, playasMunicipio, playasProvincia], async () => { if (props.open) { await ensureMapReady() } })

// Reaccionar a cambios de props del padre (usuarios nuevos, cambio de ubicación)
watch(() => props.cpro, async (val) => {
  if (val) {
    provSel.value = String(val).padStart(2, '0')
    q.value = ''
    await fetchProvincia()
  }
})
watch(() => props.municipioId, async () => {
  await fetchMunicipio()
  await fetchProvincia()
  if (props.open) { await initMap(); updateMarker() }
})
watch(() => props.municipioName, async () => {
  await fetchMunicipio()
  await fetchProvincia()
  if (props.open) { await initMap(); updateMarker() }
})

onMounted(async () => {
  if (props.open) { await ensureMapReady() }
  // Reajustar en scroll del panel (por si cambia el layout)
  try {
    const handler = () => { try { map && map.invalidateSize() } catch (e) { } }
    if (panelEl && panelEl.value) panelEl.value.addEventListener('scroll', handler, { passive: true })
  } catch (e) { }
})

onBeforeUnmount(() => {
  try { if (map) { map.remove(); map = null; marker = null } } catch (e) { }
  try { if (resizeHandler) { window.removeEventListener('resize', resizeHandler) } } catch (e) { }
  try { if (ro && mapEl.value) { ro.disconnect(); ro = null } } catch (e) { }
})

// Guardar provincia al cambiar y limpiar búsqueda para evitar filtros residuales
watch(provSel, async (val, oldVal) => {
  if (val !== oldVal) { q.value = '' }
})
</script>

<style scoped>
.spinner {
  width: 40px;
  height: 40px;
  border-radius: 9999px;
  border: 4px solid color-mix(in srgb, white 75%, var(--color-primary) 25%);
  border-top-color: color-mix(in srgb, white 30%, var(--color-primary) 70%);
  animation: spin 1s linear infinite;
}
.loader-text {
  font-weight: 600;
  letter-spacing: 0.2px;
  color: color-mix(in srgb, black 30%, var(--color-primary) 70%);
}
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* Panel glass como Diaria (.frost-card) */
.panel-glass {
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
  /* Evita la "sombra/cuadro" del header sticky fuera de los bordes redondeados */
  overflow: hidden;
}

/* Cabecera glass como Diaria (.glass-header) */
.header-glass {
  position: sticky;
  top: 0;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background-image: linear-gradient(to bottom, color-mix(in srgb, var(--color-bg) 20%, transparent), color-mix(in srgb, var(--color-bg) 20%, transparent));
  border-bottom: none;
  box-shadow: none;
  border-top-left-radius: inherit;
  border-top-right-radius: inherit;
}

/* Botones glass */
.btn-glass {
  color: #ffffff;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 24%, transparent),
      color-mix(in srgb, var(--color-bg) 24%, transparent));
  border: 1px solid rgba(255,255,255,0.18);
}
.btn-primary-glass {
  color: #ffffff;
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 16%, transparent),
      color-mix(in srgb, var(--color-primary) 16%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 14%, transparent),
      color-mix(in srgb, var(--color-bg) 14%, transparent));
  border: 1px solid rgba(255,255,255,0.22);
}

/* Chips/select e input con glass teñido del tema principal (no blanco) */
.chip-glass {
  color: #ffffff;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, white 18%, transparent),
      color-mix(in srgb, white 18%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 10%, transparent),
      color-mix(in srgb, var(--color-bg) 10%, transparent));
  border: 1px solid rgba(255,255,255,0.15);
  background-color: transparent;
}
.chip-glass::placeholder { color: rgba(255,255,255,0.7); }
select.chip-glass { appearance: none; -webkit-appearance: none; }

/* Input usa el mismo tono que el select (no simula estado seleccionado) */
input.chip-glass { background-image: inherit; border-color: rgba(255,255,255,0.15); }

/* Mapa fondo glass (sin azul saturado) */
.map-glass {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, white 10%, transparent),
      color-mix(in srgb, white 10%, transparent));
  background-color: transparent;
}

/* Opciones de listas con glass sutil */
.option-btn {
  transition: background-color .2s ease, box-shadow .2s ease, border-color .2s ease;
}
.opt-default {
  border-color: rgba(255,255,255,0.15);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, white 18%, transparent),
      color-mix(in srgb, white 18%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 10%, transparent),
      color-mix(in srgb, var(--color-bg) 10%, transparent));
}
.opt-active {
  border-color: rgba(255,255,255,0.22);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 12%, transparent),
      color-mix(in srgb, var(--color-primary) 12%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}

/* Tema claro: mismas capas que Diaria para el panel */
@media (prefers-color-scheme: light) {
  .panel-glass {
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

  .header-glass {
    background-image: linear-gradient(to bottom,
        color-mix(in srgb, white 28%, transparent),
        color-mix(in srgb, white 28%, transparent));
  }
}

/* Texto blanco dentro del panel, como en Diaria */
:deep(.panel-glass),
:deep(.panel-glass *),
:deep(.panel-glass th),
:deep(.panel-glass td) {
  color: #ffffff !important;
}
</style>
