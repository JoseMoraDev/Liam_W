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
          <NuxtLink to="/ajustes/interfaz/tema" class="sub-card">
            Tema de colores
          </NuxtLink>
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
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useI18n } from 'vue-i18n'

const mounted = ref(false);
const langOpen = ref(false)
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

onMounted(() => {
  mounted.value = true;
});
</script>

<style scoped>
.sub-card {
  @apply flex items-center justify-center p-4 text-sm font-semibold text-center text-gray-900/90 transition-colors rounded-xl bg-gray-100/20 backdrop-blur-md hover:bg-gray-200/30 shadow-inner;
}
</style>
