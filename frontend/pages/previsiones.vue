<template>
  <div class="absolute inset-0 w-full h-screen bg-center bg-cover mt-14" style="background-image: url('/img/menu.jpg')">
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Guard Modal: requiere selección de ubicación -->
    <div v-if="showGuardModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 modal-backdrop" @click="closeGuardModal" />
      <div
        class="relative z-10 w-full max-w-md p-6 space-y-4 border rounded-2xl frost-panel border-white/15 modal-card">
        <h3 class="text-xl font-bold text-center text-[color:var(--color-text)]">{{
          te('forecasts.select_location_required') ? t('forecasts.select_location_required') : 'Ubicación requerida' }}
        </h3>
        <p class="text-center text-[color:var(--color-text)]">{{ te('forecasts.must_select_location') ?
          t('forecasts.must_select_location') : 'Debes elegir la ubicación para poder consultar las previsiones.' }}</p>
        <div class="flex justify-center gap-3 pt-2">
          <button class="border frost-card border-white/15" @click="closeGuardModal">{{ te('common.continue') ?
            t('common.continue') : 'Elegir ubicación' }}</button>
        </div>
      </div>
    </div>

    <div :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 overflow-y-auto transition-opacity duration-300 md:p-8">
      <!-- LOCATION -->
      <div class="flex items-center justify-between w-full max-w-md p-4 border rounded-2xl frost-panel border-white/15">
        <div class="flex items-center w-3/4 space-x-3">
          <client-only>
            <font-awesome-icon icon="fa-solid fa-location-dot" class="text-xl theme-text-muted" />
          </client-only>
          <h2 class="text-lg font-semibold text-[color:var(--color-text)]">
            {{ t('forecasts.current_location') }}: <span class="font-bold">{{ municipioDisplay }}</span>
          </h2>
        </div>

        <NuxtLink to="/ubicacion" class="border frost-card border-white/15">{{ t('forecasts.change') }}</NuxtLink>
      </div>

      <!-- WEATHER -->
      <div class="w-full max-w-md p-4 space-y-4 border rounded-2xl frost-panel border-white/15">
        <h2 class="text-xl font-bold text-center text-[color:var(--color-text)] page-title">
          {{ t('forecasts.weather_title') }}
        </h2>

        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/meteo/horaria" class="border frost-card border-white/15">{{ t('forecasts.municipal_hourly') }}
          </NuxtLink>
          <NuxtLink to="/meteo/diaria" class="border frost-card border-white/15">{{ t('forecasts.municipal_daily') }}
          </NuxtLink>
          <NuxtLink to="/meteo/avisos" class="border frost-card border-white/15">{{ t('forecasts.alerts') }}</NuxtLink>
          <NuxtLink to="/meteo/nieve" class="border frost-card border-white/15">{{ t('forecasts.snow') }}</NuxtLink>
          <NuxtLink to="/meteo/playa" class="border frost-card border-white/15">{{ t('forecasts.beach') }}</NuxtLink>
          <NuxtLink to="/meteo/montana" class="border frost-card border-white/15">{{ t('forecasts.mountain') }}
          </NuxtLink>
        </div>
      </div>

      <!-- AIR QUALITY -->
      <div class="w-full max-w-md p-4 space-y-4 border rounded-2xl frost-panel border-white/15">
        <h2 class="text-xl font-bold text-center text-[color:var(--color-text)] page-title">
          {{ t('forecasts.air_title') }}
        </h2>

        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/aire/ambiente" class="border frost-card border-white/15">{{ t('forecasts.air_ambient') }}
          </NuxtLink>
          <NuxtLink to="/aire/polen" class="border frost-card border-white/15">{{ t('forecasts.air_pollen') }}
          </NuxtLink>
        </div>
      </div>

      <!-- TRAFFIC -->
      <div class="w-full max-w-md p-4 space-y-4 border rounded-2xl frost-panel border-white/15">
        <h2 class="text-xl font-bold text-center text-[color:var(--color-text)] page-title">{{
          t('forecasts.traffic_title') }}</h2>

        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/trafico/estado" class="border frost-card border-white/15">{{ t('forecasts.traffic_state') }}
          </NuxtLink>
          <NuxtLink to="/trafico/alertas" class="border frost-card border-white/15">{{ t('forecasts.traffic_alerts') }}
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch } from "vue";
import { navigateTo } from "#app";
import { userLoggedIn, userData } from "~/store/auth";
import { useI18n } from 'vue-i18n'
const { t, te } = useI18n()

const mounted = ref(false);
const showGuardModal = ref(false);

// Preferencias: Landing
const landing = ref('/previsiones');
function loadLanding() {
  try {
    const v = localStorage.getItem('pref_landing');
    if (v) landing.value = v;
  } catch { }
}
function saveLanding() {
  try { localStorage.setItem('pref_landing', landing.value); } catch { }
}

// Preferencias: Avisos (alcance y código)
const alertScope = ref('NONE'); // NONE | PROV | ESP
const regionCode = ref('');
function lsRegionCode() {
  try {
    const uid = currentUser.value?.id;
    // Intentar CCAA y luego provincia, por si el backend consume uno u otro
    const keyCcaa = uid ? `locpref_${uid}_ccaa_code` : 'locpref_ccaa_code';
    const keyProv = uid ? `locpref_${uid}_prov_code` : 'locpref_prov_code';
    return localStorage.getItem(keyCcaa) || localStorage.getItem(keyProv) || '';
  } catch { return ''; }
}
function loadAlerts() {
  try {
    const s = localStorage.getItem('pref_alert_scope');
    const c = localStorage.getItem('pref_alert_region_code');
    if (s) alertScope.value = s;
    regionCode.value = c || '';
  } catch { }
}
function saveAlerts() {
  try {
    localStorage.setItem('pref_alert_scope', alertScope.value);
    localStorage.setItem('pref_alert_region_code', regionCode.value || '');
  } catch { }
}
function onChangeAlertScope() {
  if (alertScope.value === 'ESP') {
    regionCode.value = 'ESP';
  } else if (alertScope.value === 'PROV') {
    regionCode.value = lsRegionCode();
  } else {
    regionCode.value = '';
  }
  saveAlerts();
}

onMounted(() => {
  mounted.value = true;
  refreshMunicipio();
  loadLanding();
  loadAlerts();
  window.addEventListener('storage', refreshMunicipio);

  // Salvaguarda: si no hay ubicación, mostrar modal
  if (!lsMunicipioName()) {
    showGuardModal.value = true;
  }
});

onBeforeUnmount(() => {
  window.removeEventListener('storage', refreshMunicipio);
});

const isLoggedIn = computed(() => userLoggedIn().value);
const currentUser = computed(() => userData().value);

const municipioName = ref("");
const municipioDisplay = computed(() => municipioName.value || t('forecasts.select_location'));

function lsMunicipioName() {
  try {
    const uid = currentUser.value?.id;
    if (isLoggedIn.value && uid) {
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

watch(isLoggedIn, () => {
  refreshMunicipio();
});

function closeGuardModal() {
  showGuardModal.value = false;
  navigateTo('/ubicacion');
}
</script>

<style scoped>
/* Frost styles alineados con meteo/horaria y meteo/diaria */
.frost-panel {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(
      to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent)
    );
  background-blend-mode: normal, normal;
  background-color: transparent;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
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
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.06); /* shadow-inner */
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(
      to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent)
    );
  background-blend-mode: normal, normal;
  background-color: transparent;
}

.frost-card:hover {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 5%, transparent),
      color-mix(in srgb, var(--color-primary) 5%, transparent)),
    linear-gradient(
      to bottom,
      color-mix(in srgb, var(--color-bg) 14%, transparent),
      color-mix(in srgb, var(--color-bg) 14%, transparent)
    );
}

/* Velo adicional en tema claro, como en diaria/horaria */
@media (prefers-color-scheme: light) {
  .frost-card {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 18%, transparent),
        color-mix(in srgb, white 18%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 3%, transparent),
        color-mix(in srgb, var(--color-primary) 3%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-bg) 12%, transparent),
        color-mix(in srgb, var(--color-bg) 12%, transparent));
  }
}

/* Forzar texto blanco dentro de paneles y tarjetas para máxima legibilidad */
:deep(.frost-panel),
:deep(.frost-panel *),
:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
  color: #ffffff !important;
}

/* Títulos de sección siempre en blanco */
.page-title {
  color: #ffffff !important;
}

/* Modal helpers */
.modal-backdrop {
  background: rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(4px);
}
.modal-card {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 5%, transparent),
      color-mix(in srgb, var(--color-primary) 5%, transparent)),
    linear-gradient(
      to bottom,
      color-mix(in srgb, var(--color-bg) 14%, transparent),
      color-mix(in srgb, var(--color-bg) 14%, transparent)
    );
  background-blend-mode: normal, normal;
  background-color: transparent;
}
</style>
