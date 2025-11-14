<template>
  <div class="relative w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <!-- Capa oscura como en horaria/diaria -->
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Contenido -->
    <div :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 transition-opacity duration-300 md:p-8">
      <h1 class="mb-2 text-3xl font-bold tracking-tight text-center page-title">{{ t('forecasts.snow') }}</h1>
      <!-- Loader -->
      <div v-if="loading" class="flex items-center justify-center w-full min-h-[40vh]">
        <div class="flex flex-col items-center gap-3 loader">
          <div class="spinner" :aria-label="t('forecasts.snow_page.loading')"></div>
          <div class="loader-text">{{ t('forecasts.snow_page.loading') }}</div>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="w-full max-w-md p-6 text-center border frost-card border-white/15 rounded-2xl">
        <p>⚠️ {{ t('forecasts.snow_page.error_prefix') }} {{ error }}</p>
      </div>

      <!-- Boletines -->
      <div v-else class="w-full max-w-5xl space-y-8">
        <div v-for="b in boletines" :key="b.zona" class="p-6 space-y-4 border frost-card border-white/15 rounded-2xl">
          <h2 class="text-xl font-bold">🌨️ {{ t('forecasts.snow_page.bulletin_title') }}</h2>
          <p class="font-semibold">{{ t('forecasts.snow_page.zone_label') }} {{ b.zonaT || b.zona }}</p>
          <div
            class="w-full p-4 mt-2 leading-relaxed whitespace-normal shadow-inner rounded-xl border border-white/15 text-[0.95rem] frost-inner">
            {{ b.boletinT || traducirBoletin(b.boletin) }}
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer class="mt-auto mb-2 text-xs text-gray-700/70">
        {{ t('forecasts.snow_page.data_provider_prefix') }}
        <a href="https://www.aemet.es" target="_blank" class="underline hover:text-gray-900">
          AEMET
        </a>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useI18n } from 'vue-i18n'
import { axiosClient } from "~/axiosConfig";

const mounted = ref(false);
const boletines = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  mounted.value = true;
  try {
    const [cat, navAra] = await Promise.all([
      axiosClient.get('/aemet/nivologica/0'),
      axiosClient.get('/aemet/nivologica/1'),
    ]);
    const arr = [];
    if (cat?.data) arr.push(cat.data);
    if (navAra?.data) arr.push(navAra.data);
    // Local i18n substitutions first
    const pre = arr.map((b) => ({
      ...b,
      zona_pre: traducirBoletin(b.zona),
      boletin_pre: traducirBoletin(b.boletin),
    }));
    // Auto-translate with LibreTranslate if needed
    const target = safeTarget(mapLocaleToLibre(locale?.value || 'es'));
    if (target && target !== 'es') {
      const translated = await Promise.all(pre.map(async (b) => {
        const zonaT = await translate(b.zona_pre || b.zona || '', target);
        const boletinT = await translate(b.boletin_pre || b.boletin || '', target);
        return { ...b, zonaT, boletinT };
      }));
      boletines.value = translated;
    } else {
      boletines.value = pre.map(b => ({ ...b, zonaT: b.zona_pre || b.zona, boletinT: b.boletin_pre || b.boletin }));
    }
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
});

const { t, locale } = useI18n();

function traducirBoletin(txt) {
  try {
    let s = String(txt || '');
    // Frases comunes
    s = s.replace(/Agencia Estatal de Meteorolog[íi]a/gi, t('forecasts.snow_phrases.agency_full'));
    s = s.replace(/Informaci[óo]n nivol[óo]gica para el/gi, t('forecasts.snow_phrases.snow_info_for'));
    s = s.replace(/Elaborado el d[íi]a/gi, t('forecasts.snow_phrases.prepared_on_day'));
    s = s.replace(/Se da por finalizada la campa[ñn]a de predicci[óo]n del peligro de aludes en/gi, t('forecasts.snow_phrases.campaign_finished_for'));
    s = s.replace(/No obstante, si las condiciones meteorol[óo]gicas as[íi] lo determinaran, se emitir[íi]a una predicci[óo]n especial\./gi, t('forecasts.snow_phrases.however_if_weather'));
    s = s.replace(/Se recuerda que, siempre que haya nieve, la ausencia total de peligro no existe\./gi, t('forecasts.snow_phrases.reminder_no_zero_risk'));
    s = s.replace(/Hay que tener presente que en circunstancias desfavorables, con aludes de tama[ñn]o 1 \(alud peque[ñn]o o colada\) y tama[ñn]o 2 \(alud mediano\), se pueden sufrir severos da[ñn]os personales\./gi, t('forecasts.snow_phrases.note_small_medium_avalanches'));
    // Regiones frecuentes (ejemplo Pirineo Catalán)
    s = s.replace(/Pirineo Catal[aá]n/gi, t('forecasts.snow_regions.pirineo_catalan'));

    // Localizar fechas en formato español "19 de mayo de 2025"
    const meses = {
      'enero': 0, 'febrero': 1, 'marzo': 2, 'abril': 3, 'mayo': 4, 'junio': 5,
      'julio': 6, 'agosto': 7, 'septiembre': 8, 'setiembre': 8, 'octubre': 9, 'noviembre': 10, 'diciembre': 11
    };
    s = s.replace(/\b(\d{1,2})\s+de\s+([a-záéíóúñ]+)\s+de\s+(\d{4})\b/gi, (_, d, mes, y) => {
      const m = meses[(mes || '').toLowerCase()] ?? null;
      if (m == null) return `${d} de ${mes} de ${y}`;
      const date = new Date(Number(y), m, Number(d));
      try {
        return new Intl.DateTimeFormat(undefined, { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
      } catch { return `${d} de ${mes} de ${y}`; }
    });
    return s;
  } catch { return txt; }
}
</script>

<style scoped>
/* Título blanco como en Horaria/Diaria */
.page-title {
  color: #ffffff !important;
}

/* Efecto Glass consistente con Horaria/Diaria/Avisos */
.frost-card {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 4%, transparent),
      color-mix(in srgb, var(--color-primary) 4%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 16%, transparent),
      color-mix(in srgb, var(--color-bg) 16%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

/* Velo adicional en tema claro */
@media (prefers-color-scheme: light) {
  .frost-card {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 22%, transparent),
        color-mix(in srgb, white 22%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 4%, transparent),
        color-mix(in srgb, var(--color-primary) 4%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-bg) 16%, transparent),
        color-mix(in srgb, var(--color-bg) 16%, transparent));
  }
}

/* Forzar texto blanco dentro de paneles para máxima legibilidad */
:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
  color: #ffffff !important;
}

/* Spinner temático (igual que Horaria/Diaria) */
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
  letter-spacing: 0.2px;
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
<style scoped>
/* Oscurecer sutilmente el box interior dentro del frost-card */
.frost-inner {
  background-image:
    linear-gradient(to bottom, rgba(0, 0, 0, 0.54), rgba(0, 0, 0, 0.54)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 12%, transparent),
      color-mix(in srgb, var(--color-primary) 12%, transparent));
  background-blend-mode: normal, normal;
}
</style>
