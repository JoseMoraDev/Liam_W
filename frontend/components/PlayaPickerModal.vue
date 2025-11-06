<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="onClose" />
    <div class="relative z-10 w-full max-w-3xl p-4 mx-4 rounded-2xl bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md border border-white/30 dark:border-zinc-700/40">
      <header class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Selecciona una playa</h2>
        <button @click="onClose" class="px-2 py-1 text-sm rounded-md bg-black/10 dark:bg-white/10">Cerrar</button>
      </header>

      <div class="mt-4 space-y-6">
        <!-- Playas del municipio (siempre visibles) -->
        <section>
          <h3 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase">Playas de {{ municipioName || 'tu municipio' }}</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-72 overflow-auto pr-1">
            <button v-for="p in playasMunicipio" :key="p.id_playa" @click="!props.saving && select(p)"
                    :class="['w-full text-left px-3 py-2 rounded-md border', sel?.id_playa === p.id_playa ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-500/10' : 'border-white/40 dark:border-zinc-700/40 bg-white/60 dark:bg-black/30']">
              <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ p.nombre_playa }}</div>
              <div class="text-xs text-gray-600 dark:text-gray-400">{{ p.nombre_municipio }} · {{ p.nombre_provincia }}</div>
            </button>
            <p v-if="!playasMunicipio.length" class="text-sm text-gray-500">Sin resultados.</p>
          </div>
        </section>

        <!-- Playas de la provincia (excluyendo municipio) -->
        <section>
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase">Playas de</h3>
            <select v-model="provSel" @change="fetchProvincia"
                    class="px-3 py-1 text-sm rounded-md bg-white/70 dark:bg-black/30 border border-white/40 dark:border-zinc-700/40">
              <option v-for="p in provincias" :key="p.id_provincia" :value="p.id_provincia">
                {{ p.nombre_provincia }}
              </option>
            </select>
          </div>
          <div class="mb-2">
            <label class="block mb-1 text-sm text-gray-600 dark:text-gray-300">Buscador</label>
            <input v-model="q" @input="debouncedBuscar" type="text" placeholder="Nombre de playa"
                   class="w-full px-3 py-2 rounded-md bg-white/70 dark:bg-black/30 border border-white/40 dark:border-zinc-700/40" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-2 max-h-72 overflow-auto pr-1">
            <button v-for="p in playasProvinciaFiltradas" :key="p.id_playa" @click="!props.saving && select(p)"
                    :class="['w-full text-left px-3 py-2 rounded-md border', sel?.id_playa === p.id_playa ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-500/10' : 'border-white/40 dark:border-zinc-700/40 bg-white/60 dark:bg-black/30']">
              <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ p.nombre_playa }}</div>
              <div class="text-xs text-gray-600 dark:text-gray-400">{{ p.nombre_municipio }} · {{ p.nombre_provincia }}</div>
            </button>
            <p v-if="!playasProvinciaFiltradas.length" class="text-sm text-gray-500">Sin resultados.</p>
          </div>
        </section>
      </div>

      <!-- Mapa preview -->
      <div class="mt-5">
        <h3 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-200">Mapa</h3>
        <div ref="mapEl" class="w-full h-64 rounded-xl border border-white/30 dark:border-zinc-700/40 overflow-hidden"></div>
      </div>

      <footer class="flex justify-end mt-5 gap-2">
        <button :disabled="props.saving" @click="onClose" class="px-3 py-2 text-sm rounded-md bg-black/10 dark:bg-white/10 disabled:opacity-50">Cancelar</button>
        <button :disabled="!sel || props.saving" @click="confirmar" class="px-3 py-2 text-sm font-medium text-white rounded-md disabled:opacity-50 bg-blue-600 hover:bg-blue-700 flex items-center gap-2">
          <span v-if="props.saving" class="inline-block w-4 h-4 border-2 border-white/80 border-t-transparent rounded-full animate-spin"></span>
          <span>{{ props.saving ? 'Guardando…' : 'Guardar' }}</span>
        </button>
      </footer>
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
})
const emit = defineEmits(['close','selected'])

const provincias = ref([])
const playasMunicipio = ref([])
const playasProvincia = ref([])
const sel = ref(null)
const provSel = ref(props.cpro ? String(props.cpro).padStart(2,'0') : null)
const q = ref('')
let t = null
let map = null
let marker = null
let Llib = null
let resizeHandler = null
const mapEl = ref(null)

function onClose(){ emit('close') }
function select(p){ sel.value = p }
function confirmar(){ if(sel.value){ emit('selected', sel.value) } }

const currentProvinciaName = computed(()=>{
  const p = provincias.value.find(x=>String(x.id_provincia)===String(provSel.value))
  return p ? p.nombre_provincia : 'provincia'
})

const LS_KEY = 'playaPicker.lastProvince'

// Municipio normalizado a 5 dígitos (cpro + cmun) cuando es posible
const normalizedMunicipioId = computed(()=>{
  const midRaw = props.municipioId != null ? String(props.municipioId) : ''
  if (/^\d{5}$/.test(midRaw)) return midRaw
  if (/^\d{1,4}$/.test(midRaw) && props.cpro) {
    const cpro2 = String(props.cpro).padStart(2,'0')
    const cmun3 = midRaw.padStart(3,'0')
    return cpro2 + cmun3
  }
  return midRaw || null
})

const playasProvinciaFiltradas = computed(()=>{
  const idsMun = new Set(playasMunicipio.value.map(p=>p.id_playa))
  return playasProvincia.value.filter(p=>!idsMun.has(p.id_playa))
})

async function fetchProvincias(){
  try{
    // Derivar provincias desde listado agregado si no hay endpoint propio
    const { data } = await axiosClient.get('/playas', { params:{ fields:'id_provincia,nombre_provincia', limit:5000 }})
    const map = new Map()
    for(const r of data){
      const pid = String(r.id_provincia).padStart(2,'0')
      map.set(pid, r.nombre_provincia)
    }
    provincias.value = Array.from(map, ([id_provincia, nombre_provincia])=>({id_provincia, nombre_provincia})).sort((a,b)=> a.nombre_provincia.localeCompare(b.nombre_provincia))
    // Preselección: provincia guardada en localStorage tiene prioridad
    try {
      const saved = typeof window !== 'undefined' ? window.localStorage.getItem(LS_KEY) : null
      if (saved && map.has(saved)) {
        provSel.value = saved
      }
    } catch(e) { /* ignore */ }
  }catch(e){ /* ignore */ }
}

async function fetchMunicipio(){
  let params = { fields:'id_playa,nombre_playa,nombre_municipio,nombre_provincia,id_provincia,lat,lon', limit:2000 }
  if (props.municipioId) {
    params.municipio = normalizedMunicipioId.value || String(props.municipioId)
  } else if (props.municipioName) {
    params.municipio_nombre = `%${props.municipioName}%`
  } else {
    playasMunicipio.value = []
    return
  }
  // No aplicar búsqueda al listado municipal; siempre mostrar todas las playas del municipio
  // Consultas en paralelo por id y por nombre (si disponible) y unir resultados
  const requests = [axiosClient.get('/playas', { params })]
  if (props.municipioName) {
    requests.push(axiosClient.get('/playas', { params: { municipio_nombre: `%${props.municipioName}%`, fields:'id_playa,nombre_playa,nombre_municipio,nombre_provincia,id_provincia,lat,lon', limit:2000, q: q.value||undefined } }))
  }
  const results = await Promise.allSettled(requests)
  const combined = []
  const seen = new Set()
  for (const r of results) {
    if (r.status === 'fulfilled' && Array.isArray(r.value.data)) {
      for (const p of r.value.data) {
        if (!seen.has(p.id_playa)) { seen.add(p.id_playa); combined.push(p) }
      }
    }
  }
  playasMunicipio.value = combined
  if (!sel.value && playasMunicipio.value.length) {
    sel.value = playasMunicipio.value[0]
  }
  // Si no hay provincia seleccionada, tomar la de la primera playa del municipio
  if (!provSel.value && playasMunicipio.value.length) {
    provSel.value = String(playasMunicipio.value[0].id_provincia).padStart(2,'0')
    await fetchProvincia()
  } else if (!playasMunicipio.value.length && provSel.value) {
    // Si no hay playas de municipio pero sí cpro, cargar provincia igualmente
    await fetchProvincia()
    // Fallback adicional: derivar playas del municipio desde la lista provincial
    if (!playasMunicipio.value.length && playasProvincia.value.length && normalizedMunicipioId.value) {
      const mid = String(normalizedMunicipioId.value)
      const derived = playasProvincia.value.filter(p => String(p.id_municipio) === mid)
      if (derived.length) {
        playasMunicipio.value = derived
        if (!sel.value) sel.value = derived[0]
      }
    }
  }
}

async function fetchProvincia(){
  if(!provSel.value){ playasProvincia.value = []; return }
  const params = { provincia: String(provSel.value).padStart(2,'0'), exclude_municipio: normalizedMunicipioId.value || props.municipioId || undefined, fields:'id_playa,nombre_playa,nombre_municipio,nombre_provincia,lat,lon', limit:2000 }
  if(q.value) params.q = q.value
  const { data } = await axiosClient.get('/playas', { params })
  // Excluir también en cliente por si exclude_municipio no coincide en formato
  const idsMun = new Set(playasMunicipio.value.map(p=>p.id_playa))
  playasProvincia.value = (data || []).filter(p=> !idsMun.has(p.id_playa))
  if (!sel.value && !playasMunicipio.value.length && playasProvincia.value.length) {
    sel.value = playasProvincia.value[0]
  }
}

function debouncedBuscar(){
  clearTimeout(t); t = setTimeout(async ()=>{ await fetchProvincia() }, 250)
}

// init: fijar provSel desde cpro si llega, cargar provincias, luego municipio y por último provincia
if (props.cpro) {
  provSel.value = String(props.cpro).padStart(2,'0')
}
await fetchProvincias()
await fetchMunicipio()
await fetchProvincia()

function getFirstCoords(){
  const src = sel.value ? [sel.value] : (playasMunicipio.value.length ? playasMunicipio.value : playasProvincia.value)
  if(src && src.length){
    const p = src[0]
    const lat = Number(p.lat); const lon = Number(p.lon)
    if(!Number.isNaN(lat) && !Number.isNaN(lon)) return [lat, lon]
  }
  return [40.4168, -3.7038] // Madrid fallback
}

async function ensureLeaflet(){
  if (typeof window === 'undefined') return
  if(!Llib){
    const m = await import('leaflet')
    Llib = m.default || m
    try {
      const iconBase = 'https://unpkg.com/leaflet@1.9.4/dist/images/'
      Llib.Icon.Default.mergeOptions({
        iconUrl: iconBase + 'marker-icon.png',
        iconRetinaUrl: iconBase + 'marker-icon-2x.png',
        shadowUrl: iconBase + 'marker-shadow.png',
      })
    } catch(e) {}
  }
}

async function initMap(){
  await ensureLeaflet()
  await nextTick()
  if(map) return
  const el = mapEl.value
  if(!el) return
  // Esperar a que el modal esté visible para calcular tamaño correctamente
  await new Promise(r=>setTimeout(r, 50))
  const center = getFirstCoords()
  map = Llib.map(el, { zoomControl: true }).setView(center, 10)
  Llib.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }).addTo(map)
  marker = Llib.marker(center).addTo(map)
  // Invalidate size tras abrir para corregir cálculos en contenedores ocultos
  setTimeout(()=>{ try{ map.invalidateSize() }catch(e){} }, 100)
  // Listener de resize
  resizeHandler = () => { try{ map.invalidateSize() }catch(e){} }
  window.addEventListener('resize', resizeHandler)
}

function updateMarker(){
  if(!map) return
  const center = getFirstCoords()
  map.setView(center, map.getZoom() || 10)
  if(marker){ marker.setLatLng(center) } else { marker = Llib.marker(center).addTo(map) }
}

watch(()=>props.open, async (v)=>{
  if(v){
    // Cargar datos antes de inicializar el mapa para centrar en primera playa del municipio
    await fetchMunicipio()
    await fetchProvincia()
    await initMap();
    updateMarker();
    setTimeout(()=>{ try{ map && map.invalidateSize() }catch(e){} }, 100)
  } else {
    // Al cerrar el modal, destruir el mapa para evitar referencias a un contenedor eliminado
    try{ if(map){ map.remove(); map = null; marker = null } }catch(e){}
  }
})
watch([sel, playasMunicipio, playasProvincia], async ()=>{ if(props.open){ if(!map){ await initMap() } updateMarker() } })

// Reaccionar a cambios de props del padre (usuarios nuevos, cambio de ubicación)
watch(() => props.cpro, async (val) => {
  if (val) {
    // Solo sobreescribir si no hay preferencia guardada en localStorage
    try {
      const saved = typeof window !== 'undefined' ? window.localStorage.getItem(LS_KEY) : null
      if (!saved) {
        provSel.value = String(val).padStart(2,'0')
        await fetchProvincia()
      }
    } catch(e) {
      provSel.value = String(val).padStart(2,'0')
      await fetchProvincia()
    }
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

onMounted(async ()=>{
  if(props.open){ await initMap(); updateMarker() }
})

onBeforeUnmount(()=>{ try{ if(map){ map.remove(); map = null; marker = null } }catch(e){} try{ if(resizeHandler){ window.removeEventListener('resize', resizeHandler) } }catch(e){} })

// Guardar provincia al cambiar
watch(provSel, (val)=>{
  try { if (val && typeof window !== 'undefined') { window.localStorage.setItem(LS_KEY, String(val)) } } catch(e){}
})
</script>
