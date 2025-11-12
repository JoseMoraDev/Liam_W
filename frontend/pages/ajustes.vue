<template>
  <div class="absolute inset-0 w-full h-screen bg-center bg-cover mt-14" style="background-image: url('/img/menu.jpg')">
    <!-- Capa oscura -->
    <div class="absolute inset-0 bg-black/5"></div>

    <!-- Contenido -->
    <div :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 overflow-y-auto transition-opacity duration-300 md:p-8">
      <!-- Título principal -->
      <div class="w-full max-w-md p-4 border rounded-2xl frost-panel border-white/15">
        <h1 class="text-2xl font-bold text-center text-[color:var(--color-text)]">Ajustes</h1>
      </div>

      <!-- Idioma -->
      <div class="w-full max-w-md p-4 space-y-4 border rounded-2xl frost-panel border-white/15">
        <h2 class="text-xl font-bold text-center text-[color:var(--color-text)]">
          {{ t('settings.language') }}
        </h2>

        <div class="grid grid-cols-1 gap-4">
          <button @click="langOpen = true" class="border frost-card border-white/15">
            {{ t('settings.change_language') }}
          </button>
        </div>
      </div>



      <!-- Interfaz -->
      <div class="w-full max-w-md p-4 space-y-4 border rounded-2xl frost-panel border-white/15">
        <h2 class="text-xl font-bold text-center text-[color:var(--color-text)]">Interfaz</h2>

        <div class="grid grid-cols-1 gap-4">
          <button @click="themeOpen = true" class="w-full border frost-card border-white/15">
            Tema de colores
          </button>
        </div>
      </div>
      <!-- Modal idioma -->
      <transition name="fade">
        <div v-if="langOpen" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/5"
          @click.self="langOpen = false">
          <div class="p-4 bg-white shadow-xl w-80 rounded-2xl">
            <h3 class="mb-3 text-lg font-semibold text-[color:var(--color-text)]">{{ t('settings.language') }}</h3>
            <div class="flex flex-col space-y-2">
              <button v-for="l in locales" :key="l.code"
                class="flex items-center w-full px-3 py-2 text-left rounded-lg hover:bg-gray-100"
                @click="selectLocale(l.code)">
                <img :src="l.flag" :alt="l.name" class="object-cover w-5 h-5 mr-2 rounded-sm" />
                <span>{{ l.name }}</span>
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Modal: seleccionar tema de colores -->
      <transition name="fade">
        <div v-if="themeOpen" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/5"
          @click.self="themeOpen = false">
          <div class="w-full max-w-md p-4 bg-white shadow-xl rounded-2xl">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-lg font-semibold text-[color:var(--color-text)]">Tema de colores</h3>
              <button @click="themeOpen = false"
                class="px-3 py-1 text-gray-700 bg-gray-100 border rounded-md">Cerrar</button>
            </div>
            <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-1">
              <div v-for="t in themeList" :key="t.id"
                class="flex items-center justify-between p-2 border rounded-lg cursor-pointer border-gray-200/60 hover:bg-[color:var(--color-overlay-weak)]"
                :class="{ 'bg-[color:var(--color-overlay-weak)] border-[color:var(--color-primary)]': t.id === activeId }"
                @click="setActiveTheme(t.id)">
                <div>
                  <div class="font-medium text-gray-900">{{ t.name }}</div>
                  <div class="text-xs text-gray-500">{{ t.id }}</div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="flex -space-x-1">
                    <span v-for="(v, k) in previewVars(t.vars)" :key="k"
                      class="inline-block w-5 h-5 border rounded-full border-gray-300/60"
                      :style="{ backgroundColor: v }"></span>
                  </div>
                  <template v-if="t.id === activeId">
                    <span
                      class="inline-flex items-center justify-center h-8 min-w-[88px] px-3 py-1 text-sm rounded-lg text-white bg-[color:var(--color-success)]">Activo</span>
                  </template>
                  <button v-else
                    class="inline-flex items-center justify-center h-8 min-w-[88px] px-3 py-1 text-sm text-white rounded-lg bg-[color:var(--color-primary)]"
                    @click.stop="setActiveTheme(t.id)">Activar</button>
                </div>
              </div>
              <div v-if="!themeList.length" class="p-3 text-center text-gray-500">No hay temas. Crea uno en Avanzado.
              </div>
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


// (Se eliminó la sección de alertas y su lógica)

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
const themeList = computed(() => {
  const list = Array.isArray(themes().value) ? [...themes().value] : []
  return list.sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''), 'es', { sensitivity: 'base' }))
})
const activeId = activeThemeId()
function previewVars(vars) {
  const keys = [
    '--color-bg', '--color-surface', '--color-primary', '--color-secondary', '--color-accent', '--color-success', '--color-warning', '--color-danger'
  ].filter(k => vars?.[k])
  const out = {}
  keys.forEach(k => out[k] = vars[k])
  return out
}
// Activación directa por botón en cada tema

onMounted(() => {
  mounted.value = true;
});
</script>

<style scoped>
/* Ultra frosted glass for Ajustes */
.frost-panel {
  /* Adaptive frost: a pinch more primary + veil mixed with theme bg so it works in light/dark */
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
  padding: 0.5rem 0.75rem; /* py-2 px-3 */
  font-size: 0.875rem; /* text-sm */
  line-height: 1.25rem;
  font-weight: 600; /* font-semibold */
  border-radius: 0.75rem; /* rounded-xl */
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-duration: 150ms; /* transition-colors */
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.06); /* shadow-inner */
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 5%, transparent),
      color-mix(in srgb, var(--color-primary) 5%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
}

.frost-card:hover {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 7%, transparent),
      color-mix(in srgb, var(--color-primary) 7%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 14%, transparent),
      color-mix(in srgb, var(--color-bg) 14%, transparent));
}

/* Lighter glass for Ajustes to reveal more of the background image */
.glass-panel.glass-panel--light {
  /* Frosted super ligero: sin tintado, solo velo blanco muy suave */
  background-image:
    linear-gradient(to bottom,
      transparent,
      transparent),
    linear-gradient(rgba(255, 255, 255, 0.10), rgba(255, 255, 255, 0.10)) !important;
  background-color: transparent !important;
  background-blend-mode: normal, normal !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.glass-card.glass-card--light {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 2%, transparent),
      color-mix(in srgb, var(--color-primary) 2%, transparent)),
    linear-gradient(rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.12)) !important;
  background-color: transparent !important;
  background-blend-mode: normal, normal !important;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.glass-card.glass-card--light:hover {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 4%, transparent),
      color-mix(in srgb, var(--color-primary) 4%, transparent)),
    linear-gradient(rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.14)) !important;
  background-blend-mode: normal, normal !important;
}
</style>
