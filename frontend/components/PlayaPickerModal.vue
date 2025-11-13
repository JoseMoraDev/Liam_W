<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-end justify-center p-4">
    <div class="absolute inset-0 bg-black/40" @click="onClose" />
    <div ref="panelEl"
      class="relative z-10 w-full max-w-2xl p-4 mb-5 border rounded-2xl panel-glass border-white/15 max-h-[85vh] overflow-y-auto">
      <div class="mx-auto mb-2 h-1.5 w-12 rounded-full bg-white/30"></div>
      <header class="sticky top-0 z-10 flex items-center justify-between pb-2 mb-3 header-glass">
        <h2 class="text-lg font-semibold text-white">Selecciona una playa</h2>
        <div class="flex items-center gap-2">
          <button :disabled="props.saving" @click="onClose"
            class="px-3 py-2 text-sm rounded-md btn-glass disabled:opacity-50">Cancelar</button>
          <button :disabled="!sel || props.saving" @click="confirmar"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-white rounded-md btn-primary-glass disabled:opacity-50">
            <span v-if="props.saving"
              class="inline-block w-4 h-4 border-2 rounded-full border-white/80 border-t-transparent animate-spin"></span>
            <span>{{ props.saving ? 'Guardando…' : 'Seleccionar' }}</span>
          </button>
        </div>
      </header>

      <!-- Mapa preview (arriba para que siempre se vea) -->
      <div class="mt-2">
        <h3 class="mb-2 text-sm font-semibold text-white">Mapa</h3>
        <div ref="mapEl" class="w-full h-64 min-h-[16rem] overflow-hidden border rounded-xl border-white/15 map-glass">
        </div>
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
        <section v-if="provincias.length && provSel">
          <div class="flex items-center justify-between mb-2 relative">
            <h3 class="text-sm font-semibold text-white uppercase">Playas de</h3>
            <select :key="provSel" v-model="provSel" @change="fetchProvincia"
              class="px-3 py-1 text-sm text-white rounded-md chip-glass">
              <option v-for="p in provincias" :key="p.id_provincia" :value="String(p.id_provincia).padStart(2,'0')">
                {{ p.nombre_provincia }}
              </option>
            </select>
          </div>
          <div class="mb-2">
            <input v-model="q" @input="debouncedBuscar" type="text" placeholder="Busca aquí tu playa"
              class="w-full px-3 py-2 text-white border rounded-md chip-glass border-white/15 placeholder-white/60" />
          </div>
          <div ref="provListEl"
            class="grid grid-cols-1 gap-2 pr-1 overflow-auto sm:grid-cols-2 md:grid-cols-2 max-h-48">
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
const provControlEl = ref(null)
const provMenuEl = ref(null)
const provOpen = ref(false)
const resolvedMunicipioId = ref(null) // código concatenado resuelto dinámicamente
const loadingLists = ref(false)
const provLocked = ref(false) // evita cambios en bucle de provincia tras fijarla correctamente
const provEnforceUntil = ref(0) // timestamp hasta el que se fuerza que provSel coincida con la del municipio
let docClickHandler = null
let suppressDocClose = false

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
      if (!provLocked.value && provSel.value !== nextProv) {
        provSel.value = nextProv
        await fetchProvincia()
        provLocked.value = true
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
    provLocked.value = true
  } else if (!playasMunicipio.value.length && provSel.value) {
    // Si no hay playas de municipio pero sí cpro, cargar provincia igualmente (sin fallback adicional)
    await fetchProvincia()
  }
  // Tras tener datos municipales, si el modal está abierto, asegurar mapa
  if (props.open) { await initMap(); updateMarker() }
  // Verificación: si hay playas del municipio, forzar provSel a la provincia del municipio
  try {
    if (!provLocked.value && playasMunicipio.value.length) {
      const want = String(playasMunicipio.value[0].id_provincia).padStart(2, '0')
      if (provSel.value !== want) {
        provSel.value = want
        await fetchProvincia()
      }
      provLocked.value = true
    }
  } catch (e) { /* ignore */ }
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
      await fetchProvincia()
    } else {
      // Buscar por id para centrar el mapa aunque no esté en las listas actuales
      const { data } = await axiosClient.get('/playas', { params: { id_playa: props.selectedId, fields: 'id_playa,lat,lon,id_provincia,nombre_playa,nombre_municipio,nombre_provincia', limit: 1 } })
      const row = Array.isArray(data) ? data[0] : data
      if (row && row.id_playa) {
        sel.value = row
        if (row.id_provincia != null) provSel.value = String(row.id_provincia).padStart(2, '0')
        await fetchProvincia()
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
  resizeHandler = () => { try { map && map.invalidateSize() } catch (e) { } }
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
    provLocked.value = false
    provEnforceUntil.value = Date.now() + 5000
    q.value = ''
    try { if (panelEl && panelEl.value) panelEl.value.scrollTop = 0 } catch (e) { }
    await ensureMapReady()
    // 1) Resolver provincia desde selectedId (si existe) ANTES de cargar provincias para evitar fallback visual
    try {
      if (props.selectedId != null) {
        const { data } = await axiosClient.get('/playas', { params: { id_playa: props.selectedId, fields: 'id_playa,id_provincia,lat,lon,nombre_playa,nombre_municipio,nombre_provincia', limit: 1 } })
        const row = Array.isArray(data) ? data[0] : data
        if (row && row.id_playa) {
          sel.value = row
          if (row.id_provincia != null) {
            provSel.value = String(row.id_provincia).padStart(2, '0')
            provLocked.value = true
          }
        }
      }
    } catch (e) { }
    // 2) Si no quedó fijada, usar cpro del padre
    if (!provSel.value && props.cpro) { provSel.value = String(props.cpro).padStart(2, '0'); provLocked.value = true }
    // 3) Cargar catálogo de provincias después de tener provSel
    await fetchProvincias()
    // Con provincia fijada, cargar listas en orden
    await fetchMunicipio()
    await fetchProvincia()
    // Verificación final tras cargar listas: alinear provincia con la del municipio si existe
    try {
      if (!provLocked.value && playasMunicipio.value.length) {
        const want = String(playasMunicipio.value[0].id_provincia).padStart(2, '0')
        if (provSel.value !== want) {
          provSel.value = want
          await fetchProvincia()
        }
        provLocked.value = true
      }
    } catch (e) { }
    // Reconciliar: si hay una playa seleccionada, forzar que el desplegable use su provincia
    try {
      if (!provLocked.value && sel.value && sel.value.id_provincia != null) {
        const want = String(sel.value.id_provincia).padStart(2, '0')
        if (provSel.value !== want) {
          provSel.value = want
          await fetchProvincia()
        }
        provLocked.value = true
      }
    } catch (e) { }
    await ensureMapReady()
  } else {
    try { if (map) { map.remove(); map = null; marker = null } } catch (e) { }
    provLocked.value = false
    provEnforceUntil.value = 0
  }
})
watch([sel, playasMunicipio, playasProvincia], async () => { if (props.open) { await ensureMapReady() } })

// Reaccionar a cambios de props del padre (usuarios nuevos, cambio de ubicación)
watch(() => props.cpro, async (val) => {
  if (val) {
    provSel.value = String(val).padStart(2, '0')
    q.value = ''
    await fetchProvincia()
    provLocked.value = true
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

// Si cambia la playa seleccionada desde el padre, alinea la provincia del desplegable
watch(() => props.selectedId, async (nv, ov) => {
  if (nv == null || nv === ov) return
  try {
    const { data } = await axiosClient.get('/playas', { params: { id_playa: nv, fields: 'id_playa,id_provincia,lat,lon,nombre_playa,nombre_municipio,nombre_provincia', limit: 1 } })
    const row = Array.isArray(data) ? data[0] : data
    if (row && row.id_playa) {
      sel.value = row
      if (row.id_provincia != null) {
        const want = String(row.id_provincia).padStart(2, '0')
        if (!provLocked.value && provSel.value !== want) {
          provSel.value = want
          await fetchProvincia()
        }
        provLocked.value = true
      }
      if (props.open) { await initMap(); updateMarker() }
    }
  } catch (e) { }
})

onMounted(async () => {
  if (props.open) { await ensureMapReady() }
  // Reajustar en scroll del panel (por si cambia el layout)
  try {
    const handler = () => { try { map && map.invalidateSize() } catch (e) { } }
    if (panelEl && panelEl.value) panelEl.value.addEventListener('scroll', handler, { passive: true })
  } catch (e) { }
  // Cerrar menú al hacer click fuera
  try {
    const onDocClick = (ev) => {
      if (suppressDocClose) return
      const t = ev.target
      const inside = (provControlEl?.value && provControlEl.value.contains(t)) || (provMenuEl?.value && provMenuEl.value.contains(t))
      if (!inside) provOpen.value = false
    }
    document.addEventListener('click', onDocClick)
    docClickHandler = onDocClick
  } catch (e) { }
})

onBeforeUnmount(() => {
  try { if (map) { map.remove(); map = null; marker = null } } catch (e) { }
  try { if (resizeHandler) { window.removeEventListener('resize', resizeHandler) } } catch (e) { }
  try { if (ro && mapEl.value) { ro.disconnect(); ro = null } } catch (e) { }
  try { if (docClickHandler) { document.removeEventListener('click', docClickHandler); docClickHandler = null } } catch (e) { }
})

// Guardar provincia al cambiar y limpiar búsqueda para evitar filtros residuales
watch(provSel, async (val, oldVal) => {
  if (val !== oldVal) { q.value = '' }
})

// Durante los primeros 5s tras abrir, si hay playas del municipio, forzar que la provincia del desplegable
// coincida con la provincia del municipio para evitar saltos al primer valor del catálogo.
watch(provSel, async (nv, ov) => {
  if (!props.open) return
  if (Date.now() > provEnforceUntil.value) return
  try {
    if (playasMunicipio.value.length) {
      const want = String(playasMunicipio.value[0].id_provincia).padStart(2, '0')
      if (nv !== want) {
        provSel.value = want
        await fetchProvincia()
      }
    }
  } catch (e) { }
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

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

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
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
  background-image: none;
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
  border: 1px solid rgba(255, 255, 255, 0.18);
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
  border: 1px solid rgba(255, 255, 255, 0.22);
}

/* Chips/select e input con glass teñido del tema principal (no blanco) */
.chip-glass {
  color: #ffffff;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  /* Igual que Diaria: capas glass con tinte primario sutil y velo de fondo */
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  border: 1px solid rgba(255, 255, 255, 0.18);
  background-color: transparent;
}

.chip-glass::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

select.chip-glass {
  appearance: none;
  -webkit-appearance: none;
}

/* Forzar fondo más oscuro del control select y del desplegable para contraste */
:deep(select.chip-glass) {
  /* Mismo glass que Diaria para coherencia visual */
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 16%, transparent),
      color-mix(in srgb, var(--color-primary) 16%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 22%, transparent),
      color-mix(in srgb, var(--color-bg) 22%, transparent)),
    url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 20 20' fill='%23ffffff'><path d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.06z'/></svg>");
  background-repeat: no-repeat;
  background-position: right .6rem center;
  background-size: 12px 12px;
  border-color: rgba(255, 255, 255, 0.07);
  border-width: 1px;
  border-style: solid;
  border-radius: 12px;
  outline: none !important;
  box-shadow: inset 0 1px 4px rgba(0,0,0,.10);
  background-clip: padding-box;
  -webkit-tap-highlight-color: transparent;
  color: #ffffff;
  padding-right: 1.9rem;
}

:deep(select.chip-glass:focus),
:deep(select.chip-glass:focus-visible) {
  outline: none !important;
  box-shadow:
    0 0 0 1px color-mix(in srgb, var(--color-primary) 38%, transparent),
    inset 0 1px 4px rgba(0,0,0,.10);
}

:deep(select.chip-glass) option {
  /* Fondo del menú: tinte del color predominante (primary) + velo de fondo */
  background-color: color-mix(in srgb, var(--color-primary) 40%, color-mix(in srgb, var(--color-bg) 60%, transparent));
  color: #ffffff;
  border: 0;
  line-height: 1.5;
  padding: 6px 10px;
}
:deep(select.chip-glass) option:hover,
:deep(select.chip-glass) option:checked,
:deep(select.chip-glass) option:focus {
  background-color: color-mix(in srgb, var(--color-primary) 55%, color-mix(in srgb, var(--color-bg) 45%, transparent));
  color: #ffffff;
  font-weight: 600;
}

/* Variante tema claro igual a Diaria */
@media (prefers-color-scheme: light) {
  .chip-glass {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 18%, transparent),
        color-mix(in srgb, white 18%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 6%, transparent),
        color-mix(in srgb, var(--color-primary) 6%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-bg) 12%, transparent),
        color-mix(in srgb, var(--color-bg) 12%, transparent));
  }
  :deep(select.chip-glass) {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 10%, transparent),
        color-mix(in srgb, white 10%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 10%, transparent),
        color-mix(in srgb, var(--color-primary) 10%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-bg) 16%, transparent),
        color-mix(in srgb, var(--color-bg) 16%, transparent)),
      url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 20 20' fill='%230b1220'><path d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.06z'/></svg>");
    background-repeat: no-repeat;
    background-position: right .6rem center;
    background-size: 12px 12px;
    color: #ffffff;
    border-color: rgba(0,0,0,.06);
    border-radius: 12px;
    padding-right: 1.9rem;
  }
  :deep(select.chip-glass) option {
    background-color: color-mix(in srgb, var(--color-primary) 36%, color-mix(in srgb, var(--color-bg) 64%, transparent));
    color: #ffffff;
    line-height: 1.5;
    padding: 6px 10px;
  }
}

/* Input usa el mismo tono que el select (no simula estado seleccionado) */
input.chip-glass {
  background-image: inherit;
  border-color: rgba(255, 255, 255, 0.15);
}

/* Mapa fondo glass (sin azul saturado) */
.map-glass {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, white 10%, transparent),
      color-mix(in srgb, white 10%, transparent));
  background-color: transparent;
}

/* Controles de zoom Leaflet con color predominante del tema (glass) */
:deep(.leaflet-control-zoom a) {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 22%, transparent),
      color-mix(in srgb, var(--color-primary) 22%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 16%, transparent),
      color-mix(in srgb, var(--color-bg) 16%, transparent));
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.22);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.22);
}

:deep(.leaflet-control-zoom a:hover),
:deep(.leaflet-control-zoom a:focus) {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 30%, transparent),
      color-mix(in srgb, var(--color-primary) 30%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 18%, transparent),
      color-mix(in srgb, var(--color-bg) 18%, transparent));
  outline: none;
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 45%, transparent);
}

@media (prefers-color-scheme: light) {
  :deep(.leaflet-control-zoom a) {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 18%, transparent),
        color-mix(in srgb, white 18%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 16%, transparent),
        color-mix(in srgb, var(--color-primary) 16%, transparent));
    color: #0b1220;
    border-color: rgba(0, 0, 0, 0.12);
  }
  :deep(.leaflet-control-zoom a:hover),
  :deep(.leaflet-control-zoom a:focus) {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 20%, transparent),
        color-mix(in srgb, white 20%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 22%, transparent),
        color-mix(in srgb, var(--color-primary) 22%, transparent));
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 35%, transparent);
  }
}

/* Opciones de listas con glass sutil */
.option-btn {
  transition: background-color .2s ease, box-shadow .2s ease, border-color .2s ease;
}

.opt-default {
  border-color: rgba(255, 255, 255, 0.15);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, white 18%, transparent),
      color-mix(in srgb, white 18%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 10%, transparent),
      color-mix(in srgb, var(--color-bg) 10%, transparent));
}

.opt-active {
  border-color: rgba(255, 255, 255, 0.22);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 12%, transparent),
      color-mix(in srgb, var(--color-primary) 12%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
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
