<template>
  <div>
    <!-- Cabecera -->
    <div
      class="flex flex-col items-center justify-start w-full gap-4 mt-4 text-center"
    >
      <h2 class="text-2xl font-semibold text-black">
        Selecciona una Comunidad Autónoma
      </h2>

      <div class="flex flex-col items-center gap-2">
        <select
          id="ccaa"
          class="px-3 py-2 text-sm border rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          v-model="internalValue"
          @change="onSelectChange"
        >
          <option value="" disabled>Búsqueda rápida</option>
          <option v-for="c in ccaaList" :key="c.id" :value="c.id">
            {{ regionNamesEs[c.name] || c.name }}
          </option>
        </select>
      </div>

      <!-- Select de provincia -->
      <div
        class="flex flex-col items-center gap-2 mt-2"
        v-if="selectedProvinces.length"
      >
        <select
          v-model="selectedProvince"
          class="px-3 py-2 text-sm border rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option value="" disabled>Selecciona una provincia</option>
          <option v-for="p in selectedProvinces" :key="p.cpro" :value="p.cpro">
            {{ p.nombre }}
          </option>
        </select>
      </div>
    </div>

    <!-- Contenedor del mapa -->
    <div class="flex items-center justify-center w-full mt-8">
      <svg
        :viewBox="`${viewBox.minX} ${viewBox.minY} ${viewBox.width} ${viewBox.height}`"
        xmlns="http://www.w3.org/2000/svg"
        class="w-full select-none max-w-7xl"
        preserveAspectRatio="xMidYMid meet"
        role="img"
      >
        <!-- Península -->
        <g>
          <template v-for="loc in mainlandLocations" :key="loc.id">
            <path
              class="region"
              :class="sel(loc.id)"
              :d="loc.path"
              tabindex="0"
              :aria-label="regionNamesEs[loc.name] || loc.name"
              @click="select(loc.id)"
              @keydown.enter.prevent="select(loc.id)"
              @keydown.space.prevent="select(loc.id)"
            />
          </template>
        </g>

        <!-- Provincias de la CCAA seleccionada -->
        <g v-if="selectedProvinces.length">
          <template v-for="prov in selectedProvinces" :key="prov.cpro">
            <path
              class="region province"
              :class="selProvince(prov.cpro)"
              :d="prov.path"
              tabindex="0"
              :aria-label="prov.nombre"
              @click="selectProvince(prov.cpro)"
              @keydown.enter.prevent="selectProvince(prov.cpro)"
              @keydown.space.prevent="selectProvince(prov.cpro)"
            />
          </template>
        </g>

        <!-- Inset Canarias -->
        <g>
          <rect
            :x="insetRect.x"
            :y="insetRect.y"
            :width="insetRect.width"
            :height="insetRect.height"
            fill="none"
            stroke="#9ca3af"
            stroke-dasharray="4 4"
            rx="6"
            ry="6"
          />
          <text
            :x="insetRect.x + insetRect.width / 2"
            :y="insetRect.y - 6"
            text-anchor="middle"
            class="text-xs fill-gray-400"
          >
            Islas Canarias
          </text>
          <g :transform="canaryTransform">
            <path
              v-if="canary"
              ref="canaryPath"
              class="region"
              :class="selProvince(canary.id)"
              :d="canary.path"
              tabindex="0"
              :aria-label="canary.name"
              @click="selectProvince(canary.id)"
              @keydown.enter.prevent="selectProvince(canary.id)"
              @keydown.space.prevent="selectProvince(canary.id)"
            />
          </g>
        </g>

        <!-- Ceuta y Melilla -->
        <g>
          <circle
            :cx="ceutaPos.x"
            :cy="ceutaPos.y"
            r="10"
            class="region"
            :class="selProvince('ceuta')"
            tabindex="0"
            @click="selectProvince('ceuta')"
            @keydown.enter.prevent="selectProvince('ceuta')"
            @keydown.space.prevent="selectProvince('ceuta')"
          >
            <title>Ceuta</title>
          </circle>
          <circle
            :cx="melillaPos.x"
            :cy="melillaPos.y"
            r="10"
            class="region"
            :class="selProvince('melilla')"
            tabindex="0"
            @click="selectProvince('melilla')"
            @keydown.enter.prevent="selectProvince('melilla')"
            @keydown.space.prevent="selectProvince('melilla')"
          >
            <title>Melilla</title>
          </circle>
          <text
            :x="(ceutaPos.x + melillaPos.x) / 2"
            :y="Math.min(ceutaPos.y, melillaPos.y) - 10"
            text-anchor="middle"
            class="text-xs fill-gray-400"
          >
            Ceuta / Melilla
          </text>
        </g>
      </svg>
    </div>

    <!-- Footer -->
    <div
      class="w-full p-3 mt-4 text-sm text-center text-gray-200 bg-gray-900 md:mt-6"
    >
      <strong>Seleccionado: </strong>
      <span v-if="selectedProvinceName">{{ selectedProvinceName }}</span>
      <span v-else-if="selectedName">{{ selectedName }}</span>
      <span v-else>Ninguno</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import spainMapRaw from "@svg-maps/spain";

/* --- Normalización --- */
function normalizeId(id) {
  return String(id)
    .toLowerCase()
    .replace(/[\s_]+/g, "-")
    .replace(/á/g, "a")
    .replace(/é/g, "e")
    .replace(/í/g, "i")
    .replace(/ó/g, "o")
    .replace(/ú/g, "u")
    .replace(/ñ/g, "n");
}

function parseViewBox(vb) {
  const m = String(vb).trim().split(/\s+/).map(Number);
  const [minX, minY, width, height] = [m[0], m[1], m[2], m[3]].map((n) =>
    Number.isFinite(n) ? n : 0
  );
  return { minX, minY, width: width || 1000, height: height || 1000 };
}

/* --- Datos del mapa: provincias por CCAA --- */
const mapasCCAA = {
  Andalucia: [
    {
      cpro: "01",
      nombre: "Almería",
      path: "M438,250 l5,10 l10,-5 l5,5 l-5,-15 z",
    },
    { cpro: "02", nombre: "Cádiz", path: "M420,270 l15,-5 l5,10 l-20,0 z" },
    { cpro: "03", nombre: "Córdoba", path: "M430,251 l10,5 l5,10 l-10,-5 z" },
    { cpro: "04", nombre: "Granada", path: "M440,260 l5,5 l5,10 l-10,-5 z" },
    { cpro: "05", nombre: "Huelva", path: "M415,255 l5,-5 l5,10 l-10,-5 z" },
    { cpro: "06", nombre: "Jaén", path: "M435,245 l5,5 l5,10 l-5,-5 z" },
    { cpro: "07", nombre: "Málaga", path: "M441,270 l5,5 l5,10 l-10,-5 z" },
    { cpro: "08", nombre: "Sevilla", path: "M425,260 l10,5 l5,10 l-10,-5 z" },
  ],
  Aragon: [
    { cpro: "09", nombre: "Huesca", path: "M550,120 l10,10 l5,-5 l-10,-10 z" },
    {
      cpro: "10",
      nombre: "Zaragoza",
      path: "M560,130 l10,10 l-10,5 l-5,-10 z",
    },
    { cpro: "11", nombre: "Teruel", path: "M550,140 l10,10 l5,5 l-10,-5 z" },
  ],
  Catalunya: [
    {
      cpro: "12",
      nombre: "Barcelona",
      path: "M600,150 l10,10 l5,-5 l-10,-10 z",
    },
    { cpro: "13", nombre: "Girona", path: "M615,140 l5,10 l5,-5 l-5,-10 z" },
    { cpro: "14", nombre: "Lleida", path: "M590,140 l10,5 l5,10 l-10,-5 z" },
    { cpro: "15", nombre: "Tarragona", path: "M600,160 l10,5 l5,10 l-10,-5 z" },
  ],
  Madrid: [
    { cpro: "16", nombre: "Madrid", path: "M500,200 l10,10 l-10,5 l-5,-10 z" },
  ],
  Valencia: [
    { cpro: "17", nombre: "Alicante", path: "M540,220 l10,5 l5,10 l-10,-5 z" },
    { cpro: "18", nombre: "Castellón", path: "M535,200 l5,10 l5,-5 l-5,-10 z" },
    {
      cpro: "19",
      nombre: "Valencia",
      path: "M540,210 l10,10 l-10,5 l-5,-10 z",
    },
  ],
  Galicia: [
    { cpro: "20", nombre: "A Coruña", path: "M350,100 l10,5 l5,10 l-10,-5 z" },
    { cpro: "21", nombre: "Lugo", path: "M360,110 l10,5 l5,10 l-10,-5 z" },
    { cpro: "22", nombre: "Ourense", path: "M370,120 l10,5 l5,10 l-10,-5 z" },
    {
      cpro: "23",
      nombre: "Pontevedra",
      path: "M355,115 l10,5 l5,10 l-10,-5 z",
    },
  ],
  Canarias: [
    { cpro: "24", nombre: "Las Palmas", path: "M200,500 l5,5 l-5,5 l-5,-5 z" },
    {
      cpro: "25",
      nombre: "Santa Cruz de Tenerife",
      path: "M210,500 l5,5 l-5,5 l-5,-5 z",
    },
  ],
  CeutaMelilla: [
    { cpro: "26", nombre: "Ceuta", path: "M450,280 l5,5 l-5,5 l-5,-5 z" },
    { cpro: "27", nombre: "Melilla", path: "M460,280 l5,5 l-5,5 l-5,-5 z" },
  ],
  CastillaLaMancha: [
    { cpro: "28", nombre: "Albacete", path: "M480,230 l10,5 l5,10 l-10,-5 z" },
    {
      cpro: "29",
      nombre: "Ciudad Real",
      path: "M470,240 l10,5 l5,10 l-10,-5 z",
    },
    { cpro: "30", nombre: "Cuenca", path: "M485,220 l10,5 l5,10 l-10,-5 z" },
    {
      cpro: "31",
      nombre: "Guadalajara",
      path: "M495,210 l10,5 l5,10 l-10,-5 z",
    },
    { cpro: "32", nombre: "Toledo", path: "M475,250 l10,5 l5,10 l-10,-5 z" },
  ],
  CastillaLeon: [
    { cpro: "33", nombre: "Ávila", path: "M500,140 l10,5 l5,10 l-10,-5 z" },
    { cpro: "34", nombre: "Burgos", path: "M510,130 l10,5 l5,10 l-10,-5 z" },
    { cpro: "35", nombre: "León", path: "M495,120 l10,5 l5,10 l-10,-5 z" },
    { cpro: "36", nombre: "Palencia", path: "M505,125 l10,5 l5,10 l-10,-5 z" },
    { cpro: "37", nombre: "Salamanca", path: "M490,135 l10,5 l5,10 l-10,-5 z" },
    { cpro: "38", nombre: "Segovia", path: "M500,150 l10,5 l5,10 l-10,-5 z" },
    { cpro: "39", nombre: "Soria", path: "M510,145 l10,5 l5,10 l-10,-5 z" },
    {
      cpro: "40",
      nombre: "Valladolid",
      path: "M505,135 l10,5 l5,10 l-10,-5 z",
    },
    { cpro: "41", nombre: "Zamora", path: "M495,140 l10,5 l5,10 l-10,-5 z" },
  ],
  Extremadura: [
    { cpro: "42", nombre: "Badajoz", path: "M430,200 l10,5 l5,10 l-10,-5 z" },
    { cpro: "43", nombre: "Cáceres", path: "M440,210 l10,5 l5,10 l-10,-5 z" },
  ],
  Navarra: [
    { cpro: "44", nombre: "Navarra", path: "M550,180 l10,5 l5,10 l-10,-5 z" },
  ],
  LaRioja: [
    { cpro: "45", nombre: "La Rioja", path: "M540,170 l10,5 l5,10 l-10,-5 z" },
  ],
  Asturias: [
    { cpro: "46", nombre: "Asturias", path: "M520,100 l10,5 l5,10 l-10,-5 z" },
  ],
  Cantabria: [
    { cpro: "47", nombre: "Cantabria", path: "M525,110 l10,5 l5,10 l-10,-5 z" },
  ],
  PaisVasco: [
    { cpro: "48", nombre: "Álava", path: "M535,105 l10,5 l5,10 l-10,-5 z" },
    { cpro: "49", nombre: "Guipúzcoa", path: "M545,110 l10,5 l5,10 l-10,-5 z" },
    { cpro: "50", nombre: "Vizcaya", path: "M550,105 l10,5 l5,10 l-10,-5 z" },
  ],
  Murcia: [
    { cpro: "51", nombre: "Murcia", path: "M520,230 l10,5 l5,10 l-10,-5 z" },
  ],
  Baleares: [
    {
      cpro: "52",
      nombre: "Islas Baleares",
      path: "M700,250 l10,5 l5,10 l-10,-5 z",
    },
  ],
};

/* --- Nombres para el <select> --- */
const regionNamesEs = {
  Andalusia: "Andalucía",
  Aragon: "Aragón",
  Asturias: "Asturias",
  "Balearic Islands": "Islas Baleares",
  "Basque Country": "País Vasco",
  "Canary Islands": "Islas Canarias",
  Cantabria: "Cantabria",
  "Castile and Leon": "Castilla y León",
  "Castile-La Mancha": "Castilla La Mancha",
  Catalonia: "Cataluña",
  Ceuta: "Ceuta",
  Extremadura: "Extremadura",
  Galicia: "Galicia",
  "La Rioja": "La Rioja",
  Madrid: "Madrid",
  Melilla: "Melilla",
  Murcia: "Región de Murcia",
  Navarre: "Navarra",
  Valencia: "Comunidad Valenciana",
};

/* --- Mapa base --- */
const spainMap = {
  viewBox: spainMapRaw.viewBox,
  locations: spainMapRaw.locations.map((l) => ({
    id: normalizeId(l.id ?? l.name),
    name: String(l.name),
    path: String(l.path),
  })),
};

/* --- Península --- */
const mainlandLocations = computed(() =>
  spainMap.locations.filter((l) => {
    const nid = normalizeId(l.id ?? l.name);
    const nname = normalizeId(l.name);
    return !/canar/.test(nid) && !/canar/.test(nname);
  })
);

/* --- Canarias, Ceuta, Melilla --- */
const canary =
  spainMap.locations.find((l) => /canar/.test(normalizeId(l.name))) || null;

const viewBox = (() => {
  const vb = parseViewBox(spainMap.viewBox);
  const centerOffsetX = vb.minX + vb.width / 2 - 430;
  const topOffset = 0;
  const heightCut = 200;
  return {
    minX: vb.minX - centerOffsetX,
    minY: vb.minY + topOffset,
    width: vb.width,
    height: vb.height - heightCut,
  };
})();

const insetRect = {
  x: viewBox.minX + viewBox.width * 0.66,
  y: viewBox.minY + viewBox.height * 0.75,
  width: viewBox.width * 0.15,
  height: viewBox.height * 0.15,
};
const canaryPath = ref(null);
const canaryTransform = ref("");

onMounted(() => {
  if (canaryPath.value) {
    const bbox = canaryPath.value.getBBox();
    const padding = 6;
    const scaleX = (insetRect.width - padding * 2) / bbox.width;
    const scaleY = (insetRect.height - padding * 2) / bbox.height;
    const scale = Math.min(scaleX, scaleY);
    const tx0 = -bbox.x;
    const ty0 = -bbox.y;
    const placedWidth = bbox.width * scale;
    const placedHeight = bbox.height * scale;
    const dx = insetRect.x + (insetRect.width - placedWidth) / 2;
    const dy = insetRect.y + (insetRect.height - placedHeight) / 2;
    canaryTransform.value = `translate(${dx},${dy}) scale(${scale}) translate(${tx0},${ty0})`;
  }
});

const ceutaPos = {
  x: viewBox.minX + viewBox.width * 0.36,
  y: viewBox.minY + viewBox.height * 0.86,
};
const melillaPos = {
  x: viewBox.minX + viewBox.width * 0.49,
  y: viewBox.minY + viewBox.height * 0.88,
};

/* --- Props y eventos --- */
const props = defineProps({ modelValue: { type: String, default: null } });
const emit = defineEmits(["update:modelValue", "change"]);
const internalValue = ref(props.modelValue ?? "");
const selectedProvince = ref("");

watch(
  () => props.modelValue,
  (v) => (internalValue.value = v)
);

/* --- Lista CCAA --- */
const ccaaList = spainMap.locations
  .map((l) => ({ id: l.id, name: l.name }))
  .concat([
    { id: "ceuta", name: "Ceuta" },
    { id: "melilla", name: "Melilla" },
  ])
  .sort((a, b) => a.name.localeCompare(b.name, "es"));

/* --- Provincias de la CCAA seleccionada --- */
const selectedProvinces = computed(() => {
  if (!internalValue.value) return [];
  const ccaaKey = Object.keys(mapasCCAA).find(
    (ccaa) => normalizeId(ccaa) === internalValue.value
  );
  return ccaaKey ? mapasCCAA[ccaaKey] : [];
});

const selectedName = computed(() => {
  const c = ccaaList.find((c) => c.id === internalValue.value);
  return c ? regionNamesEs[c.name] || c.name : "";
});

const selectedProvinceName = computed(() => {
  const prov = selectedProvinces.value.find(
    (p) => p.cpro === selectedProvince.value
  );
  return prov ? prov.nombre : "";
});

function select(id) {
  internalValue.value = id;
  selectedProvince.value = ""; // Reset provincia al cambiar CCAA
  emit("update:modelValue", id);
  emit("change", id);
}
function selectProvince(cpro) {
  selectedProvince.value = cpro;
}
function onSelectChange() {
  emit("update:modelValue", internalValue.value);
  emit("change", internalValue.value);
}
function sel(id) {
  return internalValue.value === id ? "selected" : "";
}
function selProvince(cpro) {
  return selectedProvince.value === cpro ? "selected" : "";
}
</script>

<style scoped>
.region {
  fill: #e5e7eb;
  stroke: #374151;
  stroke-width: 1;
  cursor: pointer;
  transition: fill 0.15s, stroke 0.15s;
}
.region:hover,
.region:focus {
  fill: #c7d2fe;
  outline: none;
}
.region.selected {
  fill: #6366f1;
  stroke: #4338ca;
}

/* Provincias */
.region.province {
  fill: #fef3c7;
  stroke: #b45309;
}
.region.province.selected {
  fill: #f59e0b;
  stroke: #78350f;
}

text {
  font-size: 12px;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto,
    Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
}
.text-xs {
  font-size: 11px;
}
.fill-gray-400 {
  fill: #9ca3af;
}
</style>
