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
              :class="sel(canary.id)"
              :d="canary.path"
              tabindex="0"
              :aria-label="canary.name"
              @click="select(canary.id)"
              @keydown.enter.prevent="select(canary.id)"
              @keydown.space.prevent="select(canary.id)"
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
            :class="sel('ceuta')"
            tabindex="0"
            @click="select('ceuta')"
            @keydown.enter.prevent="select('ceuta')"
            @keydown.space.prevent="select('ceuta')"
          >
            <title>Ceuta</title>
          </circle>
          <circle
            :cx="melillaPos.x"
            :cy="melillaPos.y"
            r="10"
            class="region"
            :class="sel('melilla')"
            tabindex="0"
            @click="select('melilla')"
            @keydown.enter.prevent="select('melilla')"
            @keydown.space.prevent="select('melilla')"
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
      <span v-if="selectedName">{{ selectedName }}</span>
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

/* --- Datos del mapa --- */

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

  // Recortar parte inferior (reduce el espacio vacío bajo el mapa)
  const topOffset = 0; // sube el mapa (puedes ajustar)
  const heightCut = 200; // recorta el área vacía inferior

  return {
    minX: vb.minX - centerOffsetX,
    minY: vb.minY + topOffset,
    width: vb.width,
    height: vb.height - heightCut,
  };
})();

/* Posiciones relativas */
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

/* --- Lista CCAA --- */
const ccaaList = spainMap.locations
  .map((l) => ({ id: l.id, name: l.name }))
  .concat([
    { id: "ceuta", name: "Ceuta" },
    { id: "melilla", name: "Melilla" },
  ])
  .sort((a, b) => a.name.localeCompare(b.name, "es"));

/* --- Props y eventos --- */
const props = defineProps({
  modelValue: { type: String, default: null },
});
const emit = defineEmits(["update:modelValue", "change"]);
const internalValue = ref(props.modelValue ?? "");

watch(
  () => props.modelValue,
  (v) => (internalValue.value = v)
);

const selectedName = computed(() => {
  const c = ccaaList.find((c) => c.id === internalValue.value);
  return c ? regionNamesEs[c.name] || c.name : "";
});

function select(id) {
  internalValue.value = id;
  emit("update:modelValue", id);
  emit("change", id);
}
function onSelectChange() {
  emit("update:modelValue", internalValue.value);
  emit("change", internalValue.value);
}
function sel(id) {
  return internalValue.value === id ? "selected" : "";
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
