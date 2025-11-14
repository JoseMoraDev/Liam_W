<template>
  <div class="relative w-full min-h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 max-w-5xl p-6 mx-auto space-y-10">
      <!-- Título -->
      <div class="mt-10 text-center">
        <h1 class="text-3xl font-bold tracking-wide text-gray-800 dark:text-gray-100 page-title">
          {{ t('forecasts.mountain_page.title') }}
        </h1>
        <p class="mt-1 text-2xl font-semibold text-gray-200/95 dark:text-gray-200" v-if="zona">
          {{ zona }}
        </p>
        <p v-if="loading" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('forecasts.mountain_page.loading') }}</p>
        <p v-if="error && !needsAreaSetup" class="mt-2 text-sm text-white">{{ error }}</p>
        <div class="flex items-center justify-center gap-3 mt-3" v-if="!error">
          <span class="text-xl font-semibold text-white">{{ t('forecasts.mountain_page.select_day') }}</span>
          <select v-model="selectedDay"
            class="px-3 py-1 text-sm text-white border rounded-md chip-glass border-white/15">
            <option v-for="opt in dayOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
        </div>
      </div>

      <!-- Boletín como texto plano -->
      <section v-if="!loading && !error && isTexto" class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">{{ t('forecasts.mountain_page.bulletin_title') }}</h2>
        <pre
          class="p-4 overflow-auto text-sm whitespace-pre-wrap border rounded-2xl frost-card border-white/15">{{ boletin }}</pre>
      </section>

      <!-- Boletín estructurado -->
      <section v-if="!loading && !error && !isTexto && (boletin?.seccion?.[0]?.apartado?.length)"
        class="space-y-4 estado-general">
        <h2 class="text-xl font-semibold text-white">
          {{ t('forecasts.mountain_page.general_state') }}
        </h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="apartado in boletin.seccion[0].apartado" :key="apartado.nombre"
            class="flex flex-col p-6 transition border rounded-2xl frost-card border-white/15 hover:shadow-lg">
            <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-100">
              {{ apartado.cabecera }}
            </h3>
            <p class="leading-relaxed text-gray-600 dark:text-gray-300">
              {{ apartado.texto }}
            </p>
          </div>
        </div>
      </section>

      <!-- Atmósfera libre -->
      <section v-if="!loading && !error && !isTexto && (boletin?.seccion?.[1]?.apartado?.length)"
        class="space-y-4 atmosfera-libre">
        <h2 class="text-xl font-semibold text-white">
          {{ t('forecasts.mountain_page.free_atmosphere') }}
        </h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div v-for="apartado in boletin.seccion[1].apartado" :key="apartado.nombre"
            class="flex flex-col p-6 transition border rounded-2xl frost-card border-white/15 hover:shadow-lg">
            <h3 class="mb-2 font-semibold text-gray-800 text-md dark:text-gray-100">
              {{ apartado.cabecera }}
            </h3>
            <p class="leading-relaxed text-gray-600 dark:text-gray-300">
              {{ apartado.texto }}
            </p>
          </div>
        </div>
      </section>

      <!-- Sensación térmica -->
      <section v-if="!loading && !error && !isTexto && (boletin?.seccion?.[2]?.lugar?.length)"
        class="space-y-4 sensacion-termica">
        <h2 class="text-xl font-semibold text-white">
          {{ t('forecasts.mountain_page.thermal_sensation') }}
        </h2>
        <div class="grid gap-6 sm:grid-cols-2">
          <div v-for="lugar in boletin.seccion[2].lugar" :key="lugar.nombre"
            class="flex flex-col p-6 transition border rounded-2xl frost-card border-white/15 hover:shadow-lg">
            <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-100">
              {{ lugar.nombre }} ({{ lugar.altitud }})
            </h3>
            <div class="space-y-1 text-gray-600 dark:text-gray-300">
              <p>
                <span class="tint-emph">{{ t('forecasts.mountain_page.min_label') }}</span>
                <span class="font-bold">{{ lugar.minima }}ºC</span> ({{ t('forecasts.mountain_page.thermal_short') }}
                {{ lugar.stminima }}ºC)
              </p>
              <p>
                <span class="tint-emph">{{ t('forecasts.mountain_page.max_label') }}</span>
                <span class="font-bold">{{ lugar.maxima }}ºC</span> ({{ t('forecasts.mountain_page.thermal_short') }}
                {{ lugar.stmaxima }}ºC)
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Fuente -->
      <footer
        class="pt-6 text-sm text-center text-gray-500 border-t dark:text-gray-400 border-white/30 dark:border-gray-700/40">
        {{ t('forecasts.mountain_page.data_provider_prefix') }} {{ t('forecasts.snow_phrases.agency_full') }} -
        <a :href="sourceLink" target="_blank" class="underline hover:text-gray-700 dark:hover:text-gray-200">AEMET</a>
      </footer>
    </div>
  </div>
  <div v-if="needsAreaSetup" class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 w-full max-w-md p-6 border rounded-2xl frost-card border-white/15">
      <h3 class="mb-2 text-xl font-bold text-white">{{ t('forecasts.mountain_page.setup_area_title') }}</h3>
      <p class="mb-4 text-sm leading-relaxed text-white/95">{{ t('forecasts.mountain_page.setup_area_text') }}</p>
      <div class="flex items-center justify-end gap-3">
        <button @click="needsAreaSetup = false" class="px-3 py-2 text-sm text-white border rounded-md border-white/25 hover:border-white/40">{{ t('forecasts.mountain_page.not_now') }}</button>
        <button @click="goToUbicacion" class="px-3 py-2 text-sm font-semibold text-white border rounded-md border-white/25 bg-[color:var(--color-primary)]/40 hover:bg-[color:var(--color-primary)]/55">{{ t('forecasts.mountain_page.go_to_location') }}</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Título blanco para consistencia con otras páginas meteo */
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

/* Chip glass para selects y chips ligeros (mismo tono que los chips) */
.chip-glass {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 8%, transparent),
      color-mix(in srgb, var(--color-primary) 8%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 18%, transparent),
      color-mix(in srgb, var(--color-bg) 18%, transparent));
  background-color: transparent;
  color: #ffffff;
}

/* Forzar color blanco también dentro del control y opciones */
:deep(select.chip-glass) {
  color: #ffffff;
}

:deep(select.chip-glass) option {
  color: #ffffff;
  background-color: rgba(0, 0, 0, 0.2);
}

/* Velo adicional en tema claro */
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

  .chip-glass {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 18%, transparent),
        color-mix(in srgb, white 18%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 6%, transparent),
        color-mix(in srgb, var(--color-primary) 6%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-bg) 14%, transparent),
        color-mix(in srgb, var(--color-bg) 14%, transparent));
    color: #0b1220;
    /* en claro, el chip-glass se usa poco; mantenemos texto del select blanco vía la regla deep si se requiere */
  }
}

/* Forzar texto blanco dentro de paneles para máxima legibilidad */
:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
  color: #ffffff !important;
}

/* Títulos dentro de cards con tinte de énfasis tipo glass (suave) */
:deep(.estado-general .frost-card h3),
:deep(.atmosfera-libre .frost-card h3) {
  color: color-mix(in srgb, var(--color-primary) 70%, white 30%) !important;
}

/* Etiquetas de Mínima/Máxima con el mismo tinte de énfasis suavizado y espacio a la derecha */
:deep(.sensacion-termica .tint-emph) {
  color: color-mix(in srgb, var(--color-primary) 70%, white 30%) !important;
  margin-right: 0.25rem;
  /* espacio antes del valor */
}

/* En tema claro, oscurecer ~30% los títulos con color primario para mejorar contraste */
@media (prefers-color-scheme: light) {

  :deep(.estado-general .frost-card h3),
  :deep(.atmosfera-libre .frost-card h3) {
    color: color-mix(in srgb, var(--color-primary) 70%, black 30%) !important;
  }

  :deep(.sensacion-termica .tint-emph) {
    color: color-mix(in srgb, var(--color-primary) 70%, black 30%) !important;
  }
}

/* En tema oscuro, aclarar un poco más el tinte (más blanco) */
@media (prefers-color-scheme: dark) {

  :deep(.estado-general .frost-card h3),
  :deep(.atmosfera-libre .frost-card h3) {
    color: color-mix(in srgb, var(--color-primary) 60%, white 40%) !important;
  }

  :deep(.sensacion-termica .tint-emph) {
    color: color-mix(in srgb, var(--color-primary) 60%, white 40%) !important;
  }
}
</style>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";
import { useI18n } from 'vue-i18n';

const loading = ref(true);
const error = ref(null);
const zona = ref('');
const dia = ref('');
const boletin = ref(null);
const needsAreaSetup = ref(false);
const missingAreaText = ref('');
const router = useRouter();
const isTexto = computed(() => typeof boletin.value === 'string');
const { t, locale } = useI18n();

const sourceLink = computed(() => {
  if (!isTexto.value && boletin.value?.origen?.web) return boletin.value.origen.web;
  return 'https://www.aemet.es';
});

const selectedDay = ref(0);
const dayOptions = computed(() => {
  const opts = [];
  for (let i = 0; i <= 3; i++) {
    const d = new Date();
    d.setDate(d.getDate() + i);
    const label = new Intl.DateTimeFormat([locale?.value || 'es-ES', 'es-ES'], { weekday: 'long', day: 'numeric', month: 'short' }).format(d);
    opts.push({ value: i, label });
  }
  return opts;
});

async function fetchMontana(day) {
  try {
    const uid = userData()?.value?.id;
    // 1) Obtener preferencias para extraer area_code
    const prefRes = await axiosClient.get('/user/location-pref', { params: uid ? { user_id: uid } : {} });
    const pref = prefRes?.data || {};
    const areaCode = pref?.area_code;
    if (!areaCode) {
      needsAreaSetup.value = true;
      missingAreaText.value = t('forecasts.mountain_page.missing_area');
      return;
    }
    // 2) Llamar backend con el área explícita
    const url = `/aemet/montana/${areaCode}/${day}`;
    console.debug('[Montaña] solicitando', url);
    const { data } = await axiosClient.get(url, { params: { t: Date.now() } });
    zona.value = data?.zona || '';
    dia.value = data?.dia ?? '';
    const b = data?.boletin ?? null;
    boletin.value = Array.isArray(b) ? (b[0] || null) : b;
  } catch (e) {
    if (!needsAreaSetup.value) {
      error.value = e?.message || `${t('forecasts.mountain_page.error_prefix')} ${t('forecasts.mountain_page.error_loading')}`;
    }
  } finally {
    loading.value = false;
  }
}

function goToUbicacion() {
  router.push('/ubicacion');
}

onMounted(async () => {
  loading.value = true;
  await fetchMontana(selectedDay.value);
});

watch(selectedDay, async (nv) => {
  loading.value = true;
  await fetchMontana(nv);
});
</script>

<style scoped>
body {
  background: #f9fafb;
}
</style>
