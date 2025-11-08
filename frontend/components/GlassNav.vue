<!-- TODO: cuando el usuario se loguea no se actualiza esto -->
<template>
  <nav
    class="fixed inset-x-0 top-0 z-[9999] h-16 flex items-center bg-gray-100/40 backdrop-blur-md border-b border-gray-300/40 shadow-sm"
  >
    <div class="w-full px-3 mx-auto max-w-7xl sm:px-6">
      <div class="flex items-center gap-3 px-3 py-2 rounded-2xl">
        <!-- Botón menú -->
        <button
          v-if="isLoggedIn"
          ref="menuButtonRef"
          @click="open = !open"
          class="inline-flex items-center justify-center w-10 h-10 text-gray-700 transition border rounded-xl border-gray-300/50 hover:bg-gray-200/20"
          :aria-label="t('aria.open_menu')"
        >
          <client-only
            ><font-awesome-icon icon="fa-solid fa-bars"
          /></client-only>
        </button>
        <div v-else class="w-10 h-10"></div>

        <!-- Marca -->
        <NuxtLink
          to="/"
          class="hidden font-semibold tracking-wide text-gray-800 sm:block"
        >
          Live Ambience
        </NuxtLink>

        <NuxtLink
          to="/ubicacion"
          class="hidden px-2 py-1 ml-2 text-sm text-gray-800 border rounded-lg sm:inline-flex items-center gap-2 border-gray-300/60 bg-white/50 hover:bg-white/70"
        >
          <client-only><font-awesome-icon icon="fa-solid fa-location-dot" class="w-4 h-4" /></client-only>
          <span>{{ municipioDisplay }}</span>
        </NuxtLink>

        <!-- Buscador -->
        <form
          v-if="isLoggedIn"
          @submit.prevent="onSearch"
          class="flex items-center justify-center flex-1"
        >
          <div class="relative w-full max-w-xl">
            <input
              v-model="q"
              type="search"
              :placeholder="t('nav.search_placeholder')"
              class="w-full pl-4 text-gray-800 border rounded-full h-11 pr-11 border-gray-300/50 bg-gray-50/50 placeholder:italic placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300/50 focus:border-gray-300/50"
              @input="onInput"
              @focus="showSuggestions = true"
              @blur="onBlur"
            />
            <button
              type="submit"
              class="absolute inset-y-0 right-0 flex items-center justify-center text-gray-700 border-l rounded-r-full w-11 border-gray-300/50 hover:bg-gray-200/20"
              :aria-label="t('aria.search')"
            >
              <client-only
                ><font-awesome-icon icon="fa-solid fa-magnifying-glass"
              /></client-only>
            </button>

            <div
              v-if="showSuggestions && q"
              class="absolute z-50 w-full mt-1 overflow-hidden bg-white border rounded-lg shadow-lg"
            >
              <template v-if="filteredTerms.length">
                <ul class="py-1 divide-y divide-gray-100">
                  <li
                    v-for="t in filteredTerms"
                    :key="t"
                    class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-50"
                    @mousedown.prevent="selectTerm(t)"
                  >
                    {{ t }}
                  </li>
                </ul>
              </template>
              <div v-else class="px-3 py-2 text-sm text-gray-500">
                {{ t('nav.search_no_results') }}
              </div>
            </div>
          </div>
        </form>
        <div v-else class="flex-1">
          <div class="w-full max-w-xl h-11"></div>
        </div>

        <!-- Acciones -->
        <div class="items-center hidden gap-2 ml-auto sm:flex">
          <template v-if="!isLoggedIn">
            <NuxtLink
              to="/login"
              class="inline-flex items-center h-10 px-3 text-gray-700 border rounded-xl border-gray-300/50 hover:bg-gray-200/20"
            >
              {{ t('nav.login') }}
            </NuxtLink>
            <NuxtLink
              to="/register"
              class="inline-flex items-center h-10 px-3 text-gray-700 border rounded-xl border-gray-300/50 hover:bg-gray-200/20"
            >
              {{ t('nav.register') }}
            </NuxtLink>
          </template>
          <template v-else>
            <span class="px-3 text-gray-700">
              {{ currentUser?.name || t('user.default_name') }}
            </span>
            <button
              @click="handleLogout"
              class="inline-flex items-center h-10 px-3 text-gray-700 border rounded-xl border-gray-300/50 hover:bg-gray-200/20"
            >
              {{ t('nav.logout') }}
            </button>
          </template>
          <div class="relative">
            <button
              @click="langOpen = !langOpen"
              class="inline-flex items-center h-10 px-2 text-sm text-gray-700 border rounded-xl border-gray-300/50 bg-white/50 hover:bg-white/70"
              aria-haspopup="listbox"
              :aria-expanded="langOpen ? 'true' : 'false'"
            >
              <img
                :src="activeLocale.flag"
                :alt="activeLocale.name"
                class="w-5 h-5 mr-2 rounded-sm object-cover"
              />
              <span>{{ activeLocale.name }}</span>
            </button>
            <div
              v-if="langOpen"
              class="absolute right-0 z-[10000] mt-1 w-44 overflow-hidden bg-white border rounded-lg shadow-lg"
              role="listbox"
            >
              <button
                v-for="l in locales"
                :key="l.code"
                class="flex items-center w-full px-2 py-2 text-left text-sm hover:bg-gray-50"
                @click="selectLocale(l.code)"
                role="option"
              >
                <img :src="l.flag" :alt="l.name" class="w-5 h-5 mr-2 rounded-sm object-cover" />
                <span>{{ l.name }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Móvil -->
        <NuxtLink
          to="/login"
          class="inline-flex items-center justify-center w-10 h-10 text-gray-700 border sm:hidden rounded-xl border-gray-300/50 hover:bg-gray-200/20"
          :aria-label="t('nav.login')"
        >
          <client-only
            ><font-awesome-icon icon="fa-regular fa-user"
          /></client-only>
        </NuxtLink>
      </div>
    </div>

    <!-- Panel móvil -->
    <transition name="fade">
      <div
        v-if="isLoggedIn && open"
        ref="menuRef"
        class="absolute p-4 space-y-2 text-gray-800 rounded-lg shadow-lg top-16 left-4 bg-gray-50/90 backdrop-blur-md w-max"
      >
        <!-- Menú de un solo nivel -->
        <NuxtLink
          to="/previsiones"
          class="flex items-center px-4 py-2 font-semibold uppercase rounded-lg hover:bg-gray-200/30"
          @click="open = false"
        >
          <client-only
            ><font-awesome-icon
              icon="fa-solid fa-cloud-sun"
              class="w-4 h-4 mr-2"
          /></client-only>
          {{ t('nav.forecasts') }}
        </NuxtLink>

        <NuxtLink
          to="/ajustes"
          class="flex items-center px-4 py-2 font-semibold uppercase rounded-lg hover:bg-gray-200/30"
          @click="open = false"
        >
          <client-only
            ><font-awesome-icon icon="fa-solid fa-sliders" class="w-4 h-4 mr-2"
          /></client-only>
          {{ t('nav.settings') }}
        </NuxtLink>

        <NuxtLink
          to="/avanzado"
          class="flex items-center px-4 py-2 font-semibold uppercase rounded-lg hover:bg-gray-200/30"
          @click="open = false"
        >
          <client-only
            ><font-awesome-icon icon="fa-solid fa-cogs" class="w-4 h-4 mr-2"
          /></client-only>
          {{ t('nav.advanced') }}
        </NuxtLink>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { userLoggedIn, logout, userData } from "~/store/auth";
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import { useI18n } from "vue-i18n";

const open = ref(false);
const q = ref("");
const showSuggestions = ref(false);

// Allowlisted terms
const allowlist = [
  "previsiones",
  "ajustes",
  "avanzado",
  "personalizar ubicación",
  "previsión municipal horaria",
  "previsión municipal diaria",
  "avisos",
  "previsión nivológica",
  "previsión playa",
  "previsión montaña",
  "calidad ambiental del aire",
  "polen en el aire",
  "estado del tráfico",
  "alertas de tráfico",
  "ajustes",
  "ubicaciones predeterminadas",
  "ubicaciones favoritas",
  "configurar alertas personalizadas",
  "tema de colores",
];

function norm(s) {
  return String(s)
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .trim();
}

const filteredTerms = computed(() => {
  const query = norm(q.value);
  if (!query) return allowlist;
  return allowlist.filter((t) => norm(t).includes(query));
});

// Idiomas
const { t, locale, setLocale } = useI18n();
const locales = [
  { code: "es", name: "Español", flag: "/flags/es.svg" },
  { code: "ca", name: "Català", flag: "/flags/catalunya.svg" },
  { code: "val", name: "Valencià", flag: "/flags/valencia.svg" },
  { code: "gl", name: "Galego", flag: "/flags/galicia.svg" },
  { code: "eu", name: "Euskara", flag: "/flags/euskadi.svg" },
  { code: "ary", name: "العربية المغربية", flag: "/flags/maroc.svg" },
];
const currentLocale = computed({
  get: () => locale.value,
  set: (val) => setLocale(val),
});

const langOpen = ref(false);
const activeLocale = computed(() => locales.find((l) => l.code === currentLocale.value) || locales[0]);
function selectLocale(code) {
  setLocale(code);
  langOpen.value = false;
}

function onInput() {
  showSuggestions.value = true;
}

function onBlur() {
  // Delay to allow click selection
  setTimeout(() => (showSuggestions.value = false), 100);
}

function selectTerm(t) {
  q.value = t;
  showSuggestions.value = false;
  onSearch();
}

// Referencias al menú
const menuRef = ref(null);
const menuButtonRef = ref(null);

// Computed reactivos (enlazan directamente con el store)
const isLoggedIn = computed(() => userLoggedIn().value);
const currentUser = computed(() => userData().value);

const municipioName = ref("");
const municipioDisplay = computed(() => municipioName.value || "Selecciona ubicación");

function lsMunicipioName() {
  try {
    const uid = currentUser.value?.id;
    if (userLoggedIn().value && uid) {
      const v = localStorage.getItem(`locpref_${uid}_municipio_name`);
      if (v) return v;
    }
    return localStorage.getItem("locpref_municipio_name") || "";
  } catch {
    return "";
  }
}

function refreshMunicipio() {
  municipioName.value = lsMunicipioName();
}

// Búsqueda
function onSearch() {
  if (!q.value.trim()) return;
  // Only navigate if input matches allowlist term (case/accents-insensitive)
  const input = norm(q.value);
  const match = allowlist.find((t) => norm(t) === input);
  if (!match) return; // ignore non-allowlisted queries
  navigateTo({ path: "/search", query: { q: match } });
  open.value = false;
}

// Logout
function handleLogout() {
  logout();
  navigateTo("/login");
}

// Cerrar menú al hacer click fuera
function handleClickOutside(e) {
  if (
    open.value &&
    menuRef.value &&
    !menuRef.value.contains(e.target) &&
    !menuButtonRef.value.contains(e.target)
  ) {
    open.value = false;
  }
}

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  refreshMunicipio();
  window.addEventListener("storage", refreshMunicipio);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener("storage", refreshMunicipio);
});

watch(isLoggedIn, () => {
  refreshMunicipio();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
