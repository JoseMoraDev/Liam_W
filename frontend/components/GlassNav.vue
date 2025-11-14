<template>
  <nav class="fixed inset-x-0 top-0 z-[9999] h-16 flex items-center nav-glass border-b border-white/15 shadow-sm">
    <div class="w-full px-3 mx-auto max-w-7xl sm:px-6">
      <div class="flex items-center gap-3 px-3 py-2 rounded-2xl">

        <!-- Botón menú -->
        <button v-if="isLoggedIn" ref="menuButtonRef" @click="open = !open"
          class="inline-flex items-center justify-center w-10 h-10 border frost-icon border-white/15"
          :aria-label="t('aria.open_menu')">
          <client-only><font-awesome-icon icon="fa-solid fa-bars" /></client-only>
        </button>
        <div v-else class="w-10 h-10"></div>

        <!-- Marca -->
        <NuxtLink v-if="isLoggedIn" to="/" class="hidden font-semibold tracking-wide text-[color:var(--color-text)] md:block">
          Previsiones
        </NuxtLink>

        <NuxtLink v-if="isLoggedIn" to="/ubicacion"
          class="items-center hidden gap-2 px-2 py-1 ml-2 text-sm border sm:inline-flex frost-card border-white/15">
          <client-only><font-awesome-icon icon="fa-solid fa-location-dot" class="w-4 h-4" /></client-only>
          <span>{{ municipioDisplay }}</span>
        </NuxtLink>

        <!-- Acciones -->
        <div class="flex items-center gap-2 ml-auto">
          <template v-if="!isLoggedIn">
            <NuxtLink to="/login" class="inline-flex items-center h-10 px-3 border frost-card border-white/15">
              {{ t('nav.login') }}
            </NuxtLink>
            <NuxtLink to="/register" class="inline-flex items-center h-10 px-3 border frost-card border-white/15">
              {{ t('nav.register') }}
            </NuxtLink>
          </template>
          <!-- usuario -->
          <template v-else>
            <span class="px-3 text-gray-700">
              {{ currentUser?.name || t('user.default_name') }}
            </span>
            <button @click="handleLogout" class="inline-flex items-center h-10 px-3 border frost-card border-white/15">
              {{ t('nav.logout') }}
            </button>
          </template>
          <!-- idioma -->
          <div class="relative hidden lg:block">
            <button @click="langOpen = !langOpen"
              class="inline-flex items-center h-10 px-2 text-sm border frost-card border-white/15"
              aria-haspopup="listbox" :aria-expanded="langOpen ? 'true' : 'false'">
              <img :src="activeLocale.flag" :alt="activeLocale.name" class="object-cover w-5 h-5 mr-2 rounded-sm" />
              <span>{{ activeLocale.name }}</span>
            </button>
            <div v-if="langOpen"
              class="absolute right-0 z-[10000] mt-1 w-44 overflow-hidden bg-white border rounded-lg shadow-lg"
              role="listbox">
              <button v-for="l in locales" :key="l.code"
                class="flex items-center w-full px-2 py-2 text-sm text-left hover:bg-gray-50"
                @click="selectLocale(l.code)" role="option">
                <img :src="l.flag" :alt="l.name" class="object-cover w-5 h-5 mr-2 rounded-sm" />
                <span>{{ l.name }}</span>
              </button>
            </div>
          </div>

          <!-- Tema -->
          <div v-if="isLoggedIn" class="relative hidden md:block">
            <button @click="themeOpen = !themeOpen"
              class="inline-flex items-center h-10 px-2 text-sm border frost-card border-white/15"
              aria-haspopup="listbox" :aria-expanded="themeOpen ? 'true' : 'false'">
              <span>Tema</span>
            </button>
            <div v-if="themeOpen"
              class="absolute right-0 z-[10000] mt-1 w-48 overflow-hidden frost-panel border border-white/15 rounded-lg shadow-lg"
              role="listbox">
              <button v-for="t in themeList" :key="t.id"
                class="flex items-center w-full px-3 py-2 text-left text-sm hover:bg-[color:var(--color-overlay-weak)]"
                :class="{ 'opacity-60 cursor-not-allowed': t.id === activeId }" :disabled="t.id === activeId"
                @click="activateTheme(t.id); themeOpen = false" role="option">
                <span class="truncate">{{ t.name }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel móvil -->
    <transition name="fade">
      <div v-if="isLoggedIn && open" ref="menuRef"
        class="fixed p-4 text-gray-800 rounded-2xl shadow-xl menu-panel z-[10000] border border-white/15"
        :style="panelStyle">
        <!-- Menú de un solo nivel -->
        <div class="w-56 space-y-2">
          <NuxtLink to="/previsiones" class="menu-pill" @click="open = false">
            <div class="flex items-center gap-2">
              <client-only><font-awesome-icon icon="fa-solid fa-cloud-sun" class="w-4 h-4" /></client-only>
              <span class="font-semibold tracking-wide uppercase">{{ t('nav.forecasts') }}</span>
            </div>
          </NuxtLink>

          <NuxtLink to="/ajustes" class="menu-pill" @click="open = false">
            <div class="flex items-center gap-2">
              <client-only><font-awesome-icon icon="fa-solid fa-sliders" class="w-4 h-4" /></client-only>
              <span class="font-semibold tracking-wide uppercase">{{ t('nav.settings') }}</span>
            </div>
          </NuxtLink>

          <NuxtLink v-if="currentUser?.role === 'admin'" to="/avanzado" class="menu-pill" @click="open = false">
            <div class="flex items-center gap-2">
              <client-only><font-awesome-icon icon="fa-solid fa-cogs" class="w-4 h-4" /></client-only>
              <span class="font-semibold tracking-wide uppercase">{{ t('nav.advanced') }}</span>
            </div>
          </NuxtLink>
        </div>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { userLoggedIn, logout, userData, checkAuth } from "~/store/auth";
import { ref, onMounted, onBeforeUnmount, computed, watch, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { themes as themesState, activeThemeId as activeThemeIdState, setActiveTheme } from "~/store/theme";

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

const themeList = computed(() => {
  const list = Array.isArray(themesState().value) ? [...themesState().value] : []
  list.sort((a, b) => String(a?.name || '').localeCompare(String(b?.name || ''), undefined, { sensitivity: 'base' }))
  return list
})
const activeId = computed(() => activeThemeIdState().value)
function activateTheme(id) {
  if (!id || id === activeId.value) return
  setActiveTheme(id)
}

const themeOpen = ref(false)

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

// Posicionamiento del panel bajo el botón hamburguesa
const panelStyle = ref({ left: '0px', top: '0px' })
function updatePanelPos() {
  try {
    const btn = menuButtonRef.value
    if (!btn) return
    const rect = btn.getBoundingClientRect()
    panelStyle.value = {
      left: `${Math.round(rect.left)}px`,
      top: `${Math.round(rect.bottom + 14)}px`,
    }
  } catch { }
}
watch(() => open.value, async (v) => { if (v) { await nextTick(); updatePanelPos() } })
function onResize() { updatePanelPos() }
function onScroll() { updatePanelPos() }

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

onMounted(async () => {
  document.addEventListener("click", handleClickOutside);
  refreshMunicipio();
  window.addEventListener("storage", refreshMunicipio);
  // Hidratar datos del usuario (para disponer de role en el nav)
  if (isLoggedIn.value) {
    await checkAuth();
  }
  window.addEventListener('resize', onResize)
  window.addEventListener('scroll', onScroll, { passive: true })
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener("storage", refreshMunicipio);
  window.removeEventListener('resize', onResize)
  window.removeEventListener('scroll', onScroll)
});

watch(isLoggedIn, async () => {
  refreshMunicipio();
  if (isLoggedIn.value) {
    await checkAuth();
  }
});
</script>

<style scoped>
.menu-panel {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 10%, transparent),
      color-mix(in srgb, var(--color-primary) 10%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 18%, transparent),
      color-mix(in srgb, var(--color-bg) 18%, transparent));
  background-color: transparent;
  box-shadow: 0 10px 25px rgba(0, 0, 0, .25), inset 0 1px 0 rgba(255, 255, 255, .06);
}

.menu-pill {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  border-radius: 14px;
  border: 1px solid rgba(255, 255, 255, .18);
  color: #ffffff;
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 18%, transparent),
      color-mix(in srgb, var(--color-primary) 18%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 22%, transparent),
      color-mix(in srgb, var(--color-bg) 22%, transparent));
  transition: background-image .2s ease, transform .15s ease, box-shadow .2s ease, border-color .2s ease;
}

.menu-pill:hover,
.menu-pill:focus-visible {
  outline: none;
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 28%, transparent),
      color-mix(in srgb, var(--color-primary) 28%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 26%, transparent),
      color-mix(in srgb, var(--color-bg) 26%, transparent));
  border-color: color-mix(in srgb, var(--color-primary) 35%, rgba(255, 255, 255, .18));
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 45%, transparent);
  transform: translateY(-1px);
}

.menu-pill:active {
  transform: translateY(0);
  box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 50%, transparent);
}

@media (prefers-color-scheme: light) {
  .menu-panel {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 18%, transparent),
        color-mix(in srgb, white 18%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 10%, transparent),
        color-mix(in srgb, var(--color-primary) 10%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-bg) 14%, transparent),
        color-mix(in srgb, var(--color-bg) 14%, transparent));
  }

  .menu-pill {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 16%, transparent),
        color-mix(in srgb, white 16%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 16%, transparent),
        color-mix(in srgb, var(--color-primary) 16%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-bg) 16%, transparent),
        color-mix(in srgb, var(--color-bg) 16%, transparent));
    color: #0b1220;
    border-color: rgba(0, 0, 0, .12);
  }

  .menu-pill:hover,
  .menu-pill:focus-visible {
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-primary) 35%, transparent);
  }
}
</style>

<style scoped>
.nav-glass {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.frost-panel {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 14%, transparent),
      color-mix(in srgb, var(--color-primary) 14%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 28%, transparent),
      color-mix(in srgb, var(--color-bg) 28%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  /* Prevent backdrop-filter tiling seams */
  overflow: hidden;
  contain: paint;
  isolation: isolate;
  transform: translateZ(0);
  -webkit-transform: translateZ(0);
}

.frost-card {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 0.75rem;
  /* py-2 px-3 */
  font-size: 0.875rem;
  /* text-sm */
  line-height: 1.25rem;
  font-weight: 600;
  /* font-semibold */
  border-radius: 0.75rem;
  /* rounded-xl */
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-duration: 150ms;
  /* transition-colors */
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.06);
  /* shadow-inner */
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

/* Dropdown menu buttons: follow active theme (same model as frost-card) */
.menu-btn {
  border: 1px solid rgba(255, 255, 255, 0.15);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 16%, transparent),
      color-mix(in srgb, var(--color-primary) 16%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 28%, transparent),
      color-mix(in srgb, var(--color-bg) 28%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  color: var(--color-text, inherit);
  /* Avoid composition artifacts on hover repaint */
  backface-visibility: hidden;
  transform: translateZ(0);
  -webkit-transform: translateZ(0);
  will-change: background-image;
}

.menu-btn:hover {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 20%, transparent),
      color-mix(in srgb, var(--color-primary) 20%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 32%, transparent),
      color-mix(in srgb, var(--color-bg) 32%, transparent));
}

.frost-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.75rem;
  /* rounded-xl */
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 6%, transparent),
      color-mix(in srgb, var(--color-primary) 6%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 14%, transparent),
      color-mix(in srgb, var(--color-bg) 14%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.06);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.18s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
