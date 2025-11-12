<template>
  <div class="relative w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <!-- Capa oscura -->
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Contenido principal -->
    <div :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-4 transition-opacity duration-300 md:p-8">
      <h1 class="mt-16 mb-4 text-3xl font-extrabold tracking-tight text-center md:text-4xl page-title">Avisos
        meteorológicos</h1>
      <div v-if="loading || error || !aviso" class="mb-2 text-xs text-white/70">
        loading: {{ loading }} | aviso: {{ !!aviso }} | error: {{ !!error }}
        <div class="mt-1 opacity-80">
          <span>req: {{ lastReqMethod }} {{ lastReqUrl }}</span>
          <span v-if="lastStatus"> | status: {{ lastStatus }}</span>
          <span v-if="lastErrStatus"> | err: {{ lastErrStatus }}</span>
        </div>
      </div>

      <!-- Loader mientras carga -->
      <div v-if="loading" class="flex items-center justify-center w-full min-h-[40vh]">
        <div class="flex flex-col items-center gap-3 loader">
          <div class="spinner" aria-label="Cargando"></div>
          <div class="loader-text">Cargando avisos...</div>
        </div>
      </div>

      <!-- Error principal (solo si no hay aviso para mostrar) -->
      <div v-else-if="error && !aviso"
        class="max-w-lg px-6 py-4 mt-8 text-center border frost-card border-white/15 rounded-2xl">
        <p class="text-red-300">⚠️ {{ error }}</p>
      </div>

      <!-- Aviso principal -->
      <div v-else-if="aviso" class="max-w-lg px-6 py-4 mt-8 text-center border frost-card border-white/15 rounded-2xl">
        <h2 :class="['text-2xl', 'font-bold', 'md:text-3xl']" v-html="eventWithNivel(aviso.info?.event || '')"></h2>
        <p class="mt-2 text-sm text-gray-200">
          Emitido por {{ aviso.info?.senderName || 'AEMET' }}
        </p>
      </div>

      <!-- Sin avisos -->
      <div v-else class="max-w-lg px-6 py-4 mt-8 text-center border frost-card border-white/15 rounded-2xl">
        <p class="text-white/90">No hay avisos para tu región en este momento.</p>
        <div v-if="lastData" class="mt-3 text-left">
          <p class="mb-1 text-xs text-white/70">Datos recibidos:</p>
          <pre class="p-2 text-xs whitespace-pre-wrap rounded-md bg-black/30 text-white/90">{{ lastData }}</pre>
        </div>
      </div>

      <!-- Descripción -->
      <div v-if="aviso"
        class="max-w-lg px-6 py-4 mt-4 space-y-3 text-center border frost-card border-white/15 rounded-2xl">
        <p class="text-xl font-semibold text-white">
          📍 {{ aviso.info?.area?.areaDesc || '' }}
        </p>
        <p class="text-gray-200">
          {{ aviso.info?.headline || '' }}
        </p>
        <p class="italic text-gray-300">
          {{ aviso.info?.description || '' }}
        </p>
      </div>

      <!-- Fechas -->
      <div v-if="aviso"
        class="max-w-lg px-6 py-4 mt-4 space-y-2 text-center border frost-card border-white/15 rounded-2xl">
        <p class="text-sm text-gray-200">
          ⏳ Vigente desde
          <span class="font-semibold text-white">{{
            formateaFecha(aviso.info?.onset)
          }}</span>
        </p>
        <p class="text-sm text-gray-200">
          🕒 Hasta
          <span class="font-semibold text-white">{{
            formateaFecha(aviso.info?.expires)
          }}</span>
        </p>
      </div>

      <!-- Probabilidad + nivel -->
      <div v-if="aviso"
        class="max-w-lg px-6 py-4 mt-4 space-y-2 text-center border frost-card border-white/15 rounded-2xl">
        <p class="text-lg">
          <span class="font-bold level-chip" :class="levelTextClass(aviso.info?.parameters?.level)">
            Nivel: {{ (aviso.info?.parameters?.level || '').toUpperCase() }}
          </span>
        </p>
        <p class="text-sm text-gray-200">
          Probabilidad: {{ aviso.info?.parameters?.probability || '' }}
        </p>
      </div>

      <!-- Instrucciones -->
      <div v-if="aviso" class="max-w-lg px-6 py-4 mt-4 text-center border frost-card border-white/15 rounded-2xl">
        <p class="text-sm text-gray-100">📝 {{ aviso.info?.instruction || '' }}</p>
        <a :href="aviso.info?.web || '#'" target="_blank"
          class="block mt-3 text-xs text-gray-300 underline hover:text-white">
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

<style scoped>
/* Título en blanco */
.page-title {
  color: #ffffff !important;
}

/* Loader pastel */
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
  color: color-mix(in srgb, white 85%, var(--color-primary) 15%);
}

@keyframes spin {
  from {
    transform: rotate(0)
  }

  to {
    transform: rotate(360deg)
  }
}

/* Frost glass como en horaria/diaria */
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

/* Velo blanquecino extra en claro */
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

/* Texto blanco dentro de los paneles */
:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card p),
:deep(.frost-card a) {
  color: #ffffff !important;
}

/* Colores SOLO para el texto del chip Nivel */
.level-chip.level-green {
  color: #22c55e !important;
}

.level-chip.level-yellow {
  color: #eab308 !important;
}

.level-chip.level-orange {
  color: #f97316 !important;
}

.level-chip.level-red {
  color: #ef4444 !important;
}
</style>

<script setup>
import { ref, onMounted, watch } from "vue";
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";

const mounted = ref(false);
const aviso = ref(null);
const loading = ref(true);
const error = ref(null);
const lastData = ref(null);
const lastReqMethod = ref('');
const lastReqUrl = ref('');
const lastStatus = ref('');
const lastErrStatus = ref('');

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
  // Timeout de seguridad para evitar spinner infinito
  setTimeout(() => {
    if (loading.value) {
      console.warn('[Avisos] Forzando fin de carga por timeout de seguridad');
      loading.value = false;
    }
  }, 8000);
  try {
    const uid = userData()?.value?.id;
    // Región por defecto rápida para evitar bloqueo
    let region = '77';
    try {
      const prefUrl = `/api/user/location-pref${uid ? `?user_id=${encodeURIComponent(uid)}` : ''}`;
      const prefRes = await fetch(prefUrl, { method: 'GET' });
      const pref = prefRes.ok ? await prefRes.json() : {};
      const detected = pref.avisos_region || mapRegionFromCodauto(pref.ccaa_id) || mapRegionFromName(pref.ccaa_id);
      if (detected) region = detected;
      console.debug('[Avisos] región usada:', region, 'ccaa_id:', pref.ccaa_id, 'avisos_region:', pref.avisos_region);
    } catch (prefErr) {
      console.warn('[Avisos] preferencias no disponibles, usando región por defecto', region, prefErr?.message);
    }
    const url = `/api/aemet/avisos_cap/ultimoelaborado/area/${region}`;
    const res = await fetch(url, { method: 'GET' });
    // En cuanto tenemos respuesta, cortamos el spinner para evitar bloqueos visuales
    loading.value = false;
    lastReqMethod.value = 'GET';
    lastReqUrl.value = url;
    lastStatus.value = String(res?.status || '');
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    let data = await res.text();
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    // Detectar HTML (Tomcat u otros)
    if (typeof data === 'string' && /<\s*!doctype|<\s*html/i.test(data)) {
      const snippet = data.slice(0, 200);
      console.error('[Avisos] HTML recibido desde API. status:', res?.status, 'preview:', snippet);
      throw new Error('La API devolvió un error o contenido no válido');
    }
    // Si es string pero parece JSON, intentar parsear
    if (typeof data === 'string' && /^[\[{]/.test(data.trim())) {
      try { data = JSON.parse(data); } catch (e) { /* mantener string si no parsea */ }
    }
    console.debug('[Avisos] respuesta API (posible JSON):', data, 'content-type:', ct);
    // Asignación directa si trae info en raíz
    if (data && typeof data === 'object' && data.info) {
      aviso.value = data;
      error.value = null;
      try { lastData.value = JSON.stringify(data, null, 2); } catch (_) { lastData.value = String(data); }
      console.debug('[Avisos] asignado aviso desde info en raíz, saliendo del flujo');
      return; // evitar que cualquier estado posterior ensucie el render
    } else {
      // Normalizar estructura
      let first = null;
      const candidates = [];
      if (Array.isArray(data)) candidates.push(data[0]);
      if (data && typeof data === 'object') {
        candidates.push(data);
        if (Array.isArray(data.items)) candidates.push(data.items[0]);
        if (Array.isArray(data.result)) candidates.push(data.result[0]);
        if (Array.isArray(data.avisos)) candidates.push(data.avisos[0]);
        if (Array.isArray(data.features)) candidates.push(data.features[0]);
      }
      first = candidates.find(x => x && typeof x === 'object' && x.info) || null;
      aviso.value = first || null;
    }
    if (aviso.value) error.value = null;
    try { lastData.value = JSON.stringify(data, null, 2); } catch (_) { lastData.value = String(data); }
    // Desactivado refresco en background por preferencias tardías para evitar errores 500 adicionales
  } catch (err) {
    console.error('[Avisos] error:', err);
    // Registrar error
    lastErrStatus.value = '';
    const errUrl = lastReqUrl.value;
    const errMethod = lastReqMethod.value || 'GET';
    // Solo marcar error si es el GET de avisos (no preflight OPTIONS ni otras rutas)
    if (!aviso.value && errMethod === 'GET' && errUrl.includes('/aemet/avisos_cap/ultimoelaborado/area/')) {
      error.value = err?.message || 'La API devolvió un error o contenido no válido';
    } else if (aviso.value) {
      console.warn('[Avisos] error ignorado porque ya hay aviso pintable');
    }
  } finally {
    console.debug('[Avisos] finalizando carga. aviso?', !!aviso.value, 'error?', !!error.value);
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

function eventWithNivel(eventText) {
  const s = String(eventText || '');
  // Preferimos "de nivel <color>", si no existe, usamos "nivel <color>"
  const reDe = /(de\s+nivel\s+(verde|amarillo|naranja|rojo))/ig;
  const reSolo = /(nivel\s+(verde|amarillo|naranja|rojo))/ig;

  let re = reDe;
  if (!re.test(s)) re = reSolo; // si no hay "de nivel", buscar solo "nivel"
  re.lastIndex = 0; // reset después del test

  // Primero, detectar color de la primera coincidencia
  let firstMatch = re.exec(s);
  if (!firstMatch) return escapeHtml(s);
  const colorWord = (firstMatch[2] || '').toLowerCase();
  const clsMap = { verde: 'level-green', amarillo: 'level-yellow', naranja: 'level-orange', rojo: 'level-red' };
  const cls = clsMap[colorWord] || '';

  // Ahora, recorrer todas las coincidencias para envolver solo la primera y eliminar el resto
  const parts = [];
  let lastIndex = 0;
  let count = 0;
  re.lastIndex = 0;
  let m;
  while ((m = re.exec(s)) !== null) {
    const start = m.index;
    const end = m.index + m[0].length;
    // Texto previo escapado
    if (start > lastIndex) parts.push(escapeHtml(s.slice(lastIndex, start)));
    if (count === 0) {
      // Envolver la primera ocurrencia
      parts.push(`<span class="level-chip ${cls}">${escapeHtml(m[0])}</span>`);
    } else {
      // Eliminar duplicados posteriores (no añadimos nada)
    }
    lastIndex = end;
    count++;
  }
  // Resto del texto
  if (lastIndex < s.length) parts.push(escapeHtml(s.slice(lastIndex)));
  return parts.join('');
}

function levelClass(level) {
  const v = String(level || '').toUpperCase();
  if (v.includes('VERDE')) return 'text-green-400';
  if (v.includes('AMARILLO')) return 'text-yellow-300';
  if (v.includes('NARANJA')) return 'text-orange-400';
  if (v.includes('ROJO')) return 'text-red-500';
  return 'text-gray-200';
}

function levelTextClass(level) {
  const v = String(level || '').toUpperCase();
  if (v.includes('VERDE')) return 'level-green';
  if (v.includes('AMARILLO')) return 'level-yellow';
  if (v.includes('NARANJA')) return 'level-orange';
  if (v.includes('ROJO')) return 'level-red';
  return '';
}
</script>
