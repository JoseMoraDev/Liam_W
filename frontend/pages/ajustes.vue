<template>
  <div
    class="w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg')"
  >
    <!-- Capa translúcida -->
    <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>

    <!-- Contenido -->
    <div
      :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 overflow-y-auto transition-opacity duration-300 md:p-8"
    >
      <!-- Título principal -->
      <div
        class="w-full max-w-md p-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h1 class="text-2xl font-bold text-center text-gray-900/90">Ajustes</h1>
      </div>

      <!-- Idioma -->
      <div
        class="w-full max-w-md p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">
          {{ t('settings.language') }}
        </h2>

        <div class="grid grid-cols-1 gap-4">
          <button @click="langOpen = true" class="sub-card">
            {{ t('settings.change_language') }}
          </button>
        </div>
      </div>

      <!-- Alertas -->
      <div
        class="w-full max-w-md p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">Alertas</h2>

        <div class="grid grid-cols-1 gap-4">
          <NuxtLink to="/ajustes/alertas" class="sub-card">
            Configurar alertas personalizadas
          </NuxtLink>
        </div>
      </div>

      <!-- Interfaz -->
      <div
        class="w-full max-w-md p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md"
      >
        <h2 class="text-xl font-bold text-center text-gray-900/90">Interfaz</h2>

        <div class="grid grid-cols-1 gap-4">
          <button @click="themeOpen = true" class="sub-card">
            Tema de colores
          </button>
        </div>
      </div>
      <!-- Modal idioma -->
      <transition name="fade">
        <div
          v-if="langOpen"
          class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50"
          @click.self="langOpen = false"
        >
          <div class="w-80 p-4 bg-white rounded-2xl shadow-xl">
            <h3 class="mb-3 text-lg font-semibold text-gray-800">{{ t('settings.language') }}</h3>
            <div class="flex flex-col space-y-2">
              <button
                v-for="l in locales"
                :key="l.code"
                class="flex items-center w-full px-3 py-2 text-left rounded-lg hover:bg-gray-100"
                @click="selectLocale(l.code)"
              >
                <img :src="l.flag" :alt="l.name" class="w-5 h-5 mr-2 rounded-sm object-cover" />
                <span>{{ l.name }}</span>
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Modal: seleccionar tema de colores -->
      <transition name="fade">
        <div
          v-if="themeOpen"
          class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50"
          @click.self="themeOpen = false"
        >
          <div class="w-full max-w-md p-4 bg-white rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-lg font-semibold text-gray-800">Tema de colores</h3>
              <button @click="themeOpen = false" class="px-3 py-1 text-gray-700 bg-gray-100 border rounded-md">Cerrar</button>
            </div>
            <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-1">
              <label
                v-for="t in themeList"
                :key="t.id"
                class="flex items-center justify-between p-2 border rounded-lg cursor-pointer hover:bg-gray-50 border-gray-200/60"
              >
                <div class="flex items-center gap-3">
                  <input type="radio" name="theme" :value="t.id" v-model="selectedTheme" />
                  <div>
                    <div class="font-medium text-gray-900">{{ t.name }}</div>
                    <div class="text-xs text-gray-500">{{ t.id }}</div>
                  </div>
                </div>
                <div class="flex -space-x-1">
                  <span v-for="(v,k) in previewVars(t.vars)" :key="k" class="inline-block w-5 h-5 border rounded-full border-gray-300/60" :style="{ backgroundColor: v }"></span>
                </div>
              </label>
              <div v-if="!themeList.length" class="p-3 text-center text-gray-500">No hay temas. Crea uno en Avanzado.</div>
            </div>
            <div class="flex justify-end gap-2 mt-3">
              <button @click="themeOpen = false" class="px-3 py-2 text-gray-800 bg-gray-100 border rounded-md">Cancelar</button>
              <button @click="applyTheme" class="px-3 py-2 text-white bg-gray-900 border rounded-md">Aplicar</button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useI18n } from 'vue-i18n'
import { themes, activeThemeId, setActiveTheme } from "~/store/theme";

const mounted = ref(false);
const langOpen = ref(false)
const themeOpen = ref(false)
const { t, setLocale } = useI18n()

const locales = [
  { code: "es", name: "Español", flag: "/flags/es.svg" },
  { code: "ca", name: "Català", flag: "/flags/catalunya.svg" },
  { code: "val", name: "Valencià", flag: "/flags/valencia.svg" },
  { code: "gl", name: "Galego", flag: "/flags/galicia.svg" },
  { code: "eu", name: "Euskara", flag: "/flags/euskadi.svg" },
  { code: "ary", name: "العربية المغربية", flag: "/flags/maroc.svg" },
]

function selectLocale(code) {
  setLocale(code)
  langOpen.value = false
}

// Temas
const themeList = computed(() => themes().value)
const selectedTheme = ref(activeThemeId().value || (themeList.value[0]?.id || ''))
function previewVars(vars){
  const keys = [
    '--color-bg','--color-surface','--color-primary','--color-secondary','--color-accent','--color-success','--color-warning','--color-danger'
  ].filter(k => vars?.[k])
  const out = {}
  keys.forEach(k => out[k] = vars[k])
  return out
}
function applyTheme(){
  if (!selectedTheme.value) return
  setActiveTheme(selectedTheme.value)
  themeOpen.value = false
}

onMounted(() => {
  mounted.value = true;
});
</script>

<style scoped>
.sub-card {
  @apply flex items-center justify-center p-4 text-sm font-semibold text-center text-gray-900/90 transition-colors rounded-xl bg-gray-100/20 backdrop-blur-md hover:bg-gray-200/30 shadow-inner;
}
</style>
