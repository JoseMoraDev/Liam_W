<script setup>
import { ref, onMounted } from "vue";
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";
import { useI18n } from 'vue-i18n';

const aire = ref(null);
const loading = ref(true);
const error = ref(null);

const { t, locale } = useI18n();

// Formatear fecha "jue 02" con locale actual
function formatearFecha(fechaISO) {
  const fecha = new Date(fechaISO);
  const opciones = { weekday: "short", day: "2-digit" };
  return new Intl.DateTimeFormat([locale?.value || 'es-ES', 'es-ES'], opciones)
    .format(fecha)
    .replace(".", "");
}

// Formatea 'YYYY-MM-DD HH:MM:SS' o ISO a 'DD/MM/YYYY HH:MM' respetando locale
function formatFechaHora(s) {
  try {
    if (!s || typeof s !== 'string') return '—';
    const norm = s.replace('T', ' ').trim();
    const [datePart, timePartRaw = ''] = norm.split(' ');
    const [y, m, d] = (datePart || '').split('-');
    const hhmm = timePartRaw.slice(0, 5);
    if (y && m && d && hhmm) {
      try {
        const dt = new Date(`${y}-${m}-${d}T${hhmm}:00`);
        return new Intl.DateTimeFormat([locale?.value || 'es-ES', 'es-ES'], {
          year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
        }).format(dt);
      } catch (e) {
        return `${d}/${m}/${y} ${hhmm}`;
      }
    }
    // Fallback usando Date si el formato difiere
    const dt = new Date(s);
    if (!isNaN(dt)) {
      return new Intl.DateTimeFormat([locale?.value || 'es-ES', 'es-ES'], {
        year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
      }).format(dt);
    }
  } catch (e) { }
  return '—';
}

onMounted(async () => {
  try {
    // Obtener lat/lon guardadas
    const uid = userData()?.value?.id;
    const pref = await axiosClient.get("/user/location-pref", { params: uid ? { user_id: uid } : {} });
    const { lat, lon } = pref.data || {};
    let res;
    if (lat != null && lon != null) {
      res = await axiosClient.get(`/aqicn/feed-geo`, { params: { lat, lon } });
    } else {
      // Fallback a feed-here si no hay coordenadas
      res = await axiosClient.get(`/aqicn/feed-here`);
    }
    // La API de AQICN devuelve { status: 'ok'|'error', data: {...}|'error message' }
    if (res.data?.status === 'ok') {
      aire.value = res.data?.data || null;
    } else {
      error.value = res.data?.data || res.data?.message || t('forecasts.air_ambient_page.error_aqicn');
      aire.value = null;
    }
  } catch (err) {
    console.error(t('forecasts.air_ambient_page.error_console'), err);
    error.value = err?.response?.data?.error || err?.message || t('forecasts.air_ambient_page.error_unknown');
    aire.value = null;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="relative w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 min-h-screen pt-10 p-4 text-[color:var(--color-text)]">
      <h1 class="pt-6 mb-6 text-3xl font-bold tracking-tight text-center page-title">{{ t('forecasts.air_title') }}</h1>

      <div v-if="loading" class="flex items-center justify-center min-h-[30vh]">
        <div class="flex flex-col items-center gap-3">
          <div class="spinner" :aria-label="t('forecasts.air_ambient_page.loading')"></div>
          <div class="loader-text">{{ t('forecasts.air_ambient_page.loading') }}</div>
        </div>
      </div>
      <div v-else-if="error" class="text-red-400">⚠️ {{ t('forecasts.air_ambient_page.error_prefix') }} {{ error }}</div>
      <div v-else-if="!aire">{{ t('forecasts.air_ambient_page.no_data') }}</div>
      <div v-else>
        <!-- Info general -->
        <div class="mb-4 text-center">
          <p class="mb-1 font-semibold text-md text-white/80">{{ t('forecasts.air_ambient_page.closest_station') }}</p>
          <p class="text-xl font-semibold">{{ aire.city?.name || '—' }}</p>
          <p class="mt-6 text-md text-slate-700">
            {{ t('forecasts.air_ambient_page.current_aqi') }} <span class="font-bold">{{ aire.aqi ?? '—' }}</span>
            ({{ (aire.dominentpol || '')?.toString()?.toUpperCase() || '—' }})
          </p>
          <p class="text-md text-slate-700">
            {{ t('forecasts.air_ambient_page.last_update') }} {{ formatFechaHora(aire.time?.s) }}
          </p>
        </div>

        <!-- Valores actuales -->
        <h2 class="mb-2 text-lg font-bold">🔎 {{ t('forecasts.air_ambient_page.current_values') }}</h2>
        <div class="p-4 mb-6 overflow-x-auto border frost-card border-white/15 rounded-2xl">
          <table class="min-w-full border-collapse">
            <thead>
              <tr class="glass-header text-[color:var(--color-text-muted)]">
                <th class="p-2">CO</th>
                <th class="p-2">NO₂</th>
                <th class="p-2">O₃</th>
                <th class="p-2">PM10</th>
                <th class="p-2">PM2.5</th>
                <th class="p-2">🌡️ {{ t('forecasts.air_ambient_page.temp') }}</th>
                <th class="p-2">💧 {{ t('forecasts.air_ambient_page.humidity') }}</th>
                <th class="p-2">📈 {{ t('forecasts.air_ambient_page.pressure') }}</th>
                <th class="p-2">🌬️ {{ t('forecasts.air_ambient_page.wind') }}</th>
              </tr>
            </thead>
            <tbody class="glass-body">
              <tr class="border-b theme-border hover:bg-[color:var(--color-overlay-weak)]">
                <td class="p-2 text-center">{{ aire.iaqi?.co?.v ?? "—" }}</td>
                <td class="p-2 text-center">{{ aire.iaqi?.no2?.v ?? "—" }}</td>
                <td class="p-2 text-center">{{ aire.iaqi?.o3?.v ?? "—" }}</td>
                <td class="p-2 text-center">{{ aire.iaqi?.pm10?.v ?? "—" }}</td>
                <td class="p-2 text-center">{{ aire.iaqi?.pm25?.v ?? "—" }}</td>
                <td class="p-2 text-center">{{ aire.iaqi?.t?.v ?? "—" }}°C</td>
                <td class="p-2 text-center">{{ aire.iaqi?.h?.v ?? "—" }}%</td>
                <td class="p-2 text-center">{{ aire.iaqi?.p?.v ?? "—" }} hPa</td>
                <td class="p-2 text-center">{{ aire.iaqi?.w?.v ?? "—" }} m/s</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pronóstico contaminantes -->
        <h2 class="mb-2 text-lg font-bold">📅 {{ t('forecasts.air_ambient_page.forecast_title') }}</h2>
        <div v-if="aire.forecast?.daily?.pm25?.length"
          class="p-4 overflow-x-auto border frost-card border-white/15 rounded-2xl">
          <table class="min-w-full border-collapse">
            <thead>
              <tr class="glass-header text-[color:var(--color-text-muted)]">
                <th class="p-2 text-left">📆 {{ t('forecasts.air_ambient_page.date') }}</th>
                <th class="p-2">PM2.5 (µg/m³)</th>
                <th class="p-2">PM10 (µg/m³)</th>
                <th class="p-2">O₃ (µg/m³)</th>
                <th class="p-2">UV</th>
              </tr>
            </thead>
            <tbody class="glass-body">
              <tr v-for="(dia, index) in aire.forecast.daily.pm25" :key="dia?.day || index"
                class="border-b theme-border hover:bg-[color:var(--color-overlay-weak)]">
                <td class="p-2 font-semibold">
                  {{ dia?.day ? formatearFecha(dia.day) : '—' }}
                </td>
                <td class="p-2 text-center">
                  {{ aire.forecast?.daily?.pm25?.[index]?.avg ?? '—' }}
                </td>
                <td class="p-2 text-center">
                  {{ aire.forecast?.daily?.pm10?.[index]?.avg ?? '—' }}
                </td>
                <td class="p-2 text-center">
                  {{ aire.forecast?.daily?.o3?.[index]?.avg ?? '—' }}
                </td>
                <td class="p-2 text-center">
                  {{ aire.forecast?.daily?.uvi?.[index]?.max ?? '—' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-slate-400">{{ t('forecasts.air_ambient_page.no_forecast') }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-title {
  color: #ffffff !important;
}

/* Glass muy sutil como en Diaria */
.frost-card {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

.glass-header {
  background-image: linear-gradient(to bottom, color-mix(in srgb, var(--color-bg) 20%, transparent), color-mix(in srgb, var(--color-bg) 20%, transparent));
}

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

  .glass-header {
    background-image: linear-gradient(to bottom,
        color-mix(in srgb, white 28%, transparent),
        color-mix(in srgb, white 28%, transparent));
  }
}

/* Forzar texto blanco como en Diaria dentro de frost-card */
:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
  color: #ffffff !important;
}

/* Spinner con color principal del tema */
.spinner {
  width: 48px;
  height: 48px;
  border-radius: 9999px;
  border: 4px solid color-mix(in srgb, white 75%, var(--color-primary) 25%);
  border-top-color: color-mix(in srgb, white 30%, var(--color-primary) 70%);
  animation: spin 1s linear infinite;
}

.loader-text {
  font-weight: 600;
  color: color-mix(in srgb, white 85%, var(--color-primary) 15%);
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}
</style>
