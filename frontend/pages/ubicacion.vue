<script setup>
import { ref, computed, watch, onMounted, nextTick } from "vue";
import { axiosClient } from "../axiosConfig.js";
const comunidad = ref(null);
const municipios = ref([]);
const modalOpen = ref(false);
const municipioSearch = ref("");
const municipioSearchEl = ref(null);

const loading = ref(false);
const selectedProvinceName = ref("");
const selectedProvinceCpro = ref("");
const selectedAreaCode = ref("");
const cpList = ref([]); // comunidades_provincias
const selectedMunicipio = ref(null);
const selectedMunicipioId = ref("");

// Cargar comunidades_provincias para conocer el mapeo provincia -> área montañosa
onMounted(async () => {
  try {
    const res = await axiosClient.get("comunidades-provincias");
    cpList.value = Array.isArray(res.data) ? res.data : [];
  } catch (e) {
    console.error("Error cargando comunidades_provincias", e);
    cpList.value = [];
  }
});

// (UI de área montañosa y selects principales se trasladan a MapaEspana)

const filteredMunicipios = computed(() => {
  const q = municipioSearch.value.trim().toLowerCase();
  if (!q) return municipios.value;
  return municipios.value.filter((m) => String(m.nombre).toLowerCase().includes(q));
});

const PROV_CPRO = {
  "Álava": "01",
  "Albacete": "02",
  "Alicante": "03",
  "Almería": "04",
  "Ávila": "05",
  "Badajoz": "06",
  "Baleares": "07",
  "Barcelona": "08",
  "Burgos": "09",
  "Cáceres": "10",
  "Cádiz": "11",
  "Castellón": "12",
  "Ciudad Real": "13",
  "Córdoba": "14",
  "A Coruña": "15",
  "Cuenca": "16",
  "Girona": "17",
  "Granada": "18",
  "Guadalajara": "19",
  "Gipuzkoa": "20",
  "Huelva": "21",
  "Huesca": "22",
  "Jaén": "23",
  "León": "24",
  "Lleida": "25",
  "La Rioja": "26",
  "Lugo": "27",
  "Madrid": "28",
  "Málaga": "29",
  "Murcia": "30",
  "Navarra": "31",
  "Ourense": "32",
  "Asturias": "33",
  "Palencia": "34",
  "Las Palmas": "35",
  "Pontevedra": "36",
  "Salamanca": "37",
  "Santa Cruz de Tenerife": "38",
  "Cantabria": "39",
  "Segovia": "40",
  "Sevilla": "41",
  "Soria": "42",
  "Tarragona": "43",
  "Teruel": "44",
  "Toledo": "45",
  "Valencia": "46",
  "Valladolid": "47",
  "Bizkaia": "48",
  "Zamora": "49",
  "Zaragoza": "50",
  "Ceuta": "51",
  "Melilla": "52",
};

async function onProvinceChange(payload) {
  // No abrir el modal cuando el cambio de provincia sea programático (restauración inicial)
  if (payload?.auto) return;
  const name = payload?.name || "";
  const fallback = payload?.cpro || "";
  const cpro = PROV_CPRO[name] || fallback;
  if (!cpro) return;
  selectedProvinceName.value = name;
  selectedProvinceCpro.value = cpro;
  // Autocompletar el área montañosa a partir de comunidades_provincias
  const match = cpList.value.find((r) => String(r.cpro) === String(cpro));
  selectedAreaCode.value = match?.codAreaMont || "";
  try {
    // open modal immediately and show loader
    modalOpen.value = true;
    loading.value = true;
    const res = await axiosClient.get(`municipios/${cpro}`);
    // Fix potential mojibake in names (UTF-8 seen as ISO-8859-1)
    const fixMojibake = (s) => {
      if (typeof s !== 'string') return s;
      // Heuristic: only attempt if suspicious sequences present
      if (!/[ÃÂ]/.test(s)) return s;
      try {
        // Convert each char code to byte and decode as UTF-8
        const bytes = new Uint8Array(Array.from(s, ch => ch.charCodeAt(0)));
        const dec = new TextDecoder('utf-8');
        const fixed = dec.decode(bytes);
        return fixed;
      } catch {
        try {
          // Fallback legacy fix
          return decodeURIComponent(escape(s));
        } catch {
          return s;
        }
      }
    };
    municipios.value = Array.isArray(res.data)
      ? res.data.map(m => ({ ...m, nombre: fixMojibake(m?.nombre) }))
      : [];
    // Reset municipio seleccionado al cambiar de provincia
    selectedMunicipio.value = null;
    selectedMunicipioId.value = "";
    // Limpiar persistencia previa de municipio al cambiar de provincia
    try {
      localStorage.removeItem('locpref_municipio_id');
      localStorage.removeItem('locpref_municipio_name');
      // Si hay usuario autenticado, también limpiar clave namespaced
      try {
        const me = await axiosClient.get('me');
        const uid = me?.data?.id ?? me?.data?.user?.id;
        if (uid) {
          localStorage.removeItem(`locpref_${uid}_municipio_id`);
          localStorage.removeItem(`locpref_${uid}_municipio_name`);
        }
      } catch { }
    } catch { }
    console.log("Municipios (", cpro, "):", municipios.value);
  } catch (e) {
    console.error("Error cargando municipios", e);
  }
  finally {
    loading.value = false;
    await nextTick();
    try { if (modalOpen.value && municipioSearchEl.value) municipioSearchEl.value.focus(); } catch { }
  }
}

watch(
  () => modalOpen.value,
  (open) => {
    if (!open) municipioSearch.value = "";
  }
);

function onMunicipioClick(m) {
  if (!m) return;
  selectedMunicipio.value = m;
  selectedMunicipioId.value = m.id ?? "";
  modalOpen.value = false;
}

const municipioOptions = computed(() =>
  (municipios.value || []).map(m => ({ id: m.id, name: m.nombre }))
);

watch(
  () => selectedMunicipioId.value,
  (id) => {
    if (!id) return;
    const m = (municipios.value || []).find(mm => String(mm.id) === String(id));
    if (m) selectedMunicipio.value = m;
    // Persistir selección de municipio en localStorage (genérico + namespaced si hay user)
    (async () => {
      try {
        localStorage.setItem('locpref_municipio_id', String(id));
        localStorage.setItem('locpref_municipio_name', String(m?.nombre || ''));
        try {
          const me = await axiosClient.get('me');
          const uid = me?.data?.id ?? me?.data?.user?.id;
          if (uid) {
            localStorage.setItem(`locpref_${uid}_municipio_id`, String(id));
            localStorage.setItem(`locpref_${uid}_municipio_name`, String(m?.nombre || ''));
          }
        } catch { }
      } catch { }
    })();
  }
);

// Los selects principales y el área montañosa están en MapaEspana
</script>

<template>
  <div class="absolute inset-0 w-full h-screen bg-center bg-cover mt-14" style="background-image: url('/img/menu.jpg')">
    <div class="absolute inset-0 bg-black/10"></div>
    <div class="relative z-10 w-full min-h-screen p-6 overflow-y-auto md:p-8">
      <!-- Selección de CCAA y provincia se mantiene en MapaEspana -->

      <MapaEspana v-model="comunidad" v-model:municipioId="selectedMunicipioId" :municipios="municipios"
        :suppressRestore="true" class="mt-10" @change="(id) => console.log('Elegida:', id)"
        @province-change="onProvinceChange" />



      <div v-if="modalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-black/30" @click="modalOpen = false"></div>
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="z-50 w-full max-w-md p-5 mx-4 border shadow-xl rounded-2xl frost-panel border-white/15">
            <div class="flex items-center justify-between">
              <h3 class="text-base font-semibold text-[color:var(--color-text)]">Selecciona el municipio en {{
                selectedProvinceName }}</h3>
              <button type="button" class="px-3 py-1 text-sm border rounded-full frost-card border-white/15"
                @click="modalOpen = false">
                Cerrar
              </button>
            </div>
            <div v-if="loading" class="flex items-center justify-center py-10 mt-6">
              <div class="w-8 h-8 border-2 border-indigo-500 rounded-full animate-spin border-t-transparent"></div>
            </div>
            <template v-else>
              <div class="mt-3">
                <input ref="municipioSearchEl" v-model="municipioSearch" type="text" placeholder="Buscar municipio..."
                  class="w-full px-3 py-2 text-sm border rounded focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <div class="mt-3 overflow-y-auto max-h-80">
                <div v-if="!municipios.length" class="px-2 py-3 text-sm text-gray-500">
                  No hay municipios disponibles.
                </div>
                <ul v-else class="divide-y divide-gray-200">
                  <li v-for="m in filteredMunicipios" :key="m.id"
                    class="px-3 py-2 text-sm cursor-pointer hover:bg-[color:var(--color-overlay-weak)]"
                    @click="onMunicipioClick(m)" role="button" tabindex="0">
                    {{ m.nombre }}
                  </li>
                </ul>
                <div v-if="municipios.length && !filteredMunicipios.length" class="px-2 py-3 text-sm text-gray-500">
                  No hay coincidencias.
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Alinear selects + Guardar en el mismo eje Y y centrar X */
:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6) {
  align-items: end !important;
}

:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > .flex.items-end) {
  align-items: center !important;
}

/* Guardar como un "select" más (estilo frost-card) */
:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > .flex.items-end > button) {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 4%, transparent),
      color-mix(in srgb, var(--color-primary) 4%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  background-color: transparent;
  background-blend-mode: normal, normal;
  color: var(--color-text);
  border-color: rgba(255, 255, 255, 0.15);
  border-width: 1px;
  border-style: solid;
  border-radius: 0.75rem;
  box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.06);
}

:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > .flex.items-end > button:hover) {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 6%, transparent),
      color-mix(in srgb, var(--color-primary) 6%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 14%, transparent),
      color-mix(in srgb, var(--color-bg) 14%, transparent));
}

/* Colores de selects según tema (sin tocar el tema global) */
:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 select) {
  background-color: var(--color-surface);
  color: var(--color-text);
  border-color: var(--color-border);
}

:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 select:disabled) {
  cursor: not-allowed;
}

:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 select:disabled) {
  opacity: 0.7;
}

:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 option[disabled]) {
  color: var(--color-text-muted);
}

/* Etiquetas y título con color de tema */
:deep(.flex.flex-col.items-center.justify-start.w-full.gap-4.mt-4.text-center > h2) {
  color: var(--color-text) !important;
}

:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > div > span) {
  /* Por defecto: hereda del tema; forzamos colores por tema en media queries */
  color: var(--color-text);
  padding: 2px 8px;
  border-radius: 8px;
  backdrop-filter: blur(2px);
  font-weight: 600;
}

/* En temas claros, chip más oscuro (primario) */
@media (prefers-color-scheme: light) {
  :deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > div > span) {
    /* Mucho más oscuro para máxima legibilidad en claro */
    background-color: color-mix(in srgb, var(--color-bg) 80%, var(--color-primary) 20%);
    color: #000000 !important;
    border: 1px solid color-mix(in srgb, var(--color-bg) 74%, var(--color-primary) 26%);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
  }

  /* Asegurar texto negro en chips con clase explícita */
  :deep(.chip-label) {
    color: #000000 !important;
  }

  /* Mensajes de lista (no hay municipios / no hay coincidencias): texto más oscuro */
  :deep(.frost-panel .max-h-80 .text-gray-500) {
    color: color-mix(in srgb, var(--color-text) 92%, black 8%) !important;
  }

  /* Guardar: mismo color que chip */
  :deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > .flex.items-end > button) {
    background-image: none !important;
    background-color: color-mix(in srgb, var(--color-bg) 80%, var(--color-primary) 20%) !important;
    color: #000000 !important;
    border-color: color-mix(in srgb, var(--color-bg) 74%, var(--color-primary) 26%) !important;
  }

  :deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > .flex.items-end > button:hover) {
    background-color: color-mix(in srgb, var(--color-bg) 76%, var(--color-primary) 24%) !important;
  }
}

/* En temas oscuros, chip más intenso (primario) */
@media (prefers-color-scheme: dark) {
  :deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > div > span) {
    /* Más oscuro y menos vivo también en oscuro */
    background-color: color-mix(in srgb, var(--color-bg) 62%, var(--color-primary) 38%);
    color: #ffffff !important;
    border: 1px solid color-mix(in srgb, var(--color-bg) 58%, var(--color-primary) 42%);
  }

  /* Asegurar texto blanco en chips con clase explícita */
  :deep(.chip-label) {
    color: #ffffff !important;
  }

  /* Mensajes de lista en fondo claro: forzar texto oscuro para contraste */
  :deep(.frost-panel .max-h-80 .text-gray-500) {
    color: rgba(0, 0, 0, 0.88) !important;
  }

  /* Guardar: mismo color que chip */
  :deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > .flex.items-end > button) {
    background-image: none !important;
    background-color: color-mix(in srgb, var(--color-bg) 62%, var(--color-primary) 38%) !important;
    color: #ffffff !important;
    border-color: color-mix(in srgb, var(--color-bg) 58%, var(--color-primary) 42%) !important;
  }

  :deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > .flex.items-end > button:hover) {
    background-color: color-mix(in srgb, var(--color-bg) 58%, var(--color-primary) 42%) !important;
  }
}


/* Aclarar SOLO CCAA y Zona Montañosa */
:deep(#ccaa) {
  background-color: color-mix(in srgb, var(--color-bg) 80%, white 20%);
  color: var(--color-text);
  border-color: color-mix(in srgb, var(--color-border) 70%, white 30%);
}

:deep(.flex.flex-row.flex-wrap.items-start.justify-center.gap-6 > div:nth-child(4) select) {
  background-color: color-mix(in srgb, var(--color-bg) 80%, white 20%);
  color: var(--color-text);
  border-color: color-mix(in srgb, var(--color-border) 70%, white 30%);
}

.frost-panel {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 10%, transparent),
      color-mix(in srgb, var(--color-bg) 10%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.frost-card {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  line-height: 1.25rem;
  font-weight: 600;
  border-radius: 9999px;
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-duration: 150ms;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.06);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 4%, transparent),
      color-mix(in srgb, var(--color-primary) 4%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 10%, transparent),
      color-mix(in srgb, var(--color-bg) 10%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
}

.frost-card:hover {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 6%, transparent),
      color-mix(in srgb, var(--color-primary) 6%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
}
</style>
