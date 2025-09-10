<template>
  <div class="w-full">
    <div class="flex items-center justify-between gap-4 mb-4">
      <h2 class="text-2xl font-semibold">Selecciona una Comunidad Autónoma</h2>
      <div class="flex items-center gap-2">
        <label for="ccaa" class="text-sm text-gray-600">Búsqueda rápida</label>
        <select
          id="ccaa"
          class="px-2 py-1 text-sm border rounded"
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

    <svg
      :viewBox="spainMap.viewBox"
      xmlns="http://www.w3.org/2000/svg"
      class="w-full max-w-5xl mx-auto select-none"
      role="img"
      aria-label="Mapa de España por Comunidades Autónomas con Canarias en inset"
    >
      <!-- Mapa principal (todas menos Islas Canarias) -->
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

      <!-- Inset de Canarias -->
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
          class="text-xs fill-gray-600"
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

      <!-- Marcadores de Ceuta y Melilla -->
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
          class="text-xs fill-gray-600"
        >
          Ceuta / Melilla
        </text>
      </g>
    </svg>

    <div class="p-3 mt-4 text-sm text-gray-700 rounded-lg bg-gray-50">
      <strong>Seleccionado:</strong>
      <span v-if="selectedName">{{ selectedName }}</span>
      <span v-else>Ninguno</span>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import spainMapRaw from "@svg-maps/spain";

/* --- Utilidades --- */
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

/* --- Separar Canarias --- */
const canary =
  spainMap.locations.find((l) => {
    const nid = normalizeId(l.id ?? l.name); // p.ej. "es-cn" o "canary-islands" o "islas-canarias"
    const nname = normalizeId(l.name); // normalizado
    return /canar/.test(nid) || /canar/.test(nname); // coincide "canar" => "canary" / "canarias"
  }) || null;

const mainlandLocations = spainMap.locations.filter(
  (l) => !canary || l.id !== canary.id
);

/* --- Select (añadimos Ceuta/Melilla) --- */
const ccaaList = spainMap.locations
  .map((l) => ({ id: l.id, name: l.name }))
  .concat([
    { id: "ceuta", name: "Ceuta" },
    { id: "melilla", name: "Melilla" },
  ])
  .sort((a, b) => a.name.localeCompare(b.name, "es"));

/* --- v-model --- */
const props = defineProps({ modelValue: { type: String, default: null } });
const emit = defineEmits(["update:modelValue", "change"]);

const internalValue = ref(props.modelValue);
watch(
  () => props.modelValue,
  (v) => {
    internalValue.value = v;
  }
);

const selectedName = computed(
  () => (ccaaList.find((c) => c.id === internalValue.value) || {}).name || ""
);

function select(id) {
  internalValue.value = id;
  emit("update:modelValue", internalValue.value);
  emit("change", internalValue.value);
}
function onSelectChange() {
  emit("update:modelValue", internalValue.value);
  emit("change", internalValue.value);
}
function sel(id) {
  return internalValue.value === id ? "selected" : "";
}

/* --- ViewBox / inset Canarias --- */
const viewBox = parseViewBox(spainMap.viewBox);
// Cuadro del inset (a la derecha-bajo)
const insetRect = {
  x: viewBox.minX + viewBox.width * 0.82, // más a la derecha
  y: viewBox.minY + viewBox.height * 0.4, // más arriba
  width: viewBox.width * 0.15, // más estrecho
  height: viewBox.height * 0.15, // más bajo
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

/* --- Marcadores aproximados de Ceuta/Melilla (ajustables) --- */
const ceutaPos = {
  x: viewBox.minX + viewBox.width * 0.49, // más centrado bajo Andalucía
  y: viewBox.minY + viewBox.height * 0.54,
};
const melillaPos = {
  x: viewBox.minX + viewBox.width * 0.535, // un poco a la derecha de Ceuta
  y: viewBox.minY + viewBox.height * 0.56,
};
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
.fill-gray-600 {
  fill: #4b5563;
}
</style>
