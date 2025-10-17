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
        <label for="ccaa" class="text-sm text-center text-gray-500"
          >Búsqueda rápida</label
        >
        <select
          id="ccaa"
          class="px-3 py-2 text-sm border rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          v-model="internalValue"
          @change="onSelectChange"
        >
          <option value="" disabled>-- Selecciona --</option>
          <option v-for="c in ccaaList" :key="c.id" :value="c.id">
            {{ c.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Contenedor del mapa centrado -->
    <div class="flex items-center justify-center w-full mt-8">
      <svg
        :viewBox="`${viewBox.minX} ${viewBox.minY} ${viewBox.width} ${viewBox.height}`"
        xmlns="http://www.w3.org/2000/svg"
        class="w-full select-none max-w-7xl"
        preserveAspectRatio="xMidYMid meet"
        role="img"
      >
        <!-- Ppenínsula de España -->
        <g>
          <template v-for="loc in mainlandLocations" :key="loc.id">
            <path
              class="region"
              :class="sel(loc.id)"
              :d="loc.path"
              tabindex="0"
              :aria-label="loc.name"
              @click="select(loc.id)"
              @keydown.enter.prevent="select(loc.id)"
              @keydown.space.prevent="select(loc.id)"
            />
          </template>
        </g>
      </svg>
    </div>

    <!-- Footer fijo -->
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
import { watch, ref, computed, onMounted, onUnmounted } from "vue";
import spainMapRaw from "@svg-maps/spain";

/* --- Normalización de nombres --- */
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

/* --- Mapa importado con ids normalizados --- */
const spainMap = {
  viewBox: spainMapRaw.viewBox,
  locations: spainMapRaw.locations.map((l) => ({
    id: normalizeId(l.id ?? l.name),
    name: String(l.name),
    path: String(l.path),
  })),
};

/* --- Filtrar Canarias del mapa principal --- */
const mainlandLocations = computed(() =>
  spainMap.locations.filter((l) => {
    const nid = normalizeId(l.id ?? l.name);
    const nname = normalizeId(l.name);
    return !/canar/.test(nid) && !/canar/.test(nname);
  })
);

/* --- Lista desplegable de CCAA --- */
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

const internalValue = ref(props.modelValue);
watch(
  () => props.modelValue,
  (v) => (internalValue.value = v)
);

const selectedName = computed(
  () => ccaaList.find((c) => c.id === internalValue.value)?.name || ""
);

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

/* --- Ajuste de posición --- */
const viewBox = (() => {
  const vb = parseViewBox(spainMap.viewBox);
  // opcional: centrar horizontalmente la península
  const centerOffsetX = vb.minX + vb.width / 2 - 400; // 500 = mitad de ancho del canvas deseado
  return { ...vb, minX: vb.minX - centerOffsetX };
})();
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
.fill-gray-300 {
  fill: #d1d5db;
}
</style>
