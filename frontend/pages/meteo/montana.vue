<template>
  <div class="max-w-5xl p-6 mx-auto space-y-10">
    <!-- Título -->
    <div class="text-center">
      <h1
        class="text-3xl font-bold tracking-wide text-gray-800 dark:text-gray-100"
      >
        Predicción AEMET Montaña
      </h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" v-if="zona">
        Zona: {{ zona }} · Día: {{ dia }}
      </p>
      <p v-if="loading" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Cargando…</p>
      <p v-if="error" class="mt-2 text-sm text-red-500">{{ error }}</p>
      <div class="flex items-center justify-center gap-3 mt-3" v-if="!error">
        <span class="text-sm text-gray-600 dark:text-gray-300">Mostrar</span>
        <select v-model="selectedDay" class="px-3 py-1 text-sm rounded-md bg-white/70 dark:bg-black/30 border border-white/40 dark:border-gray-700/40">
          <option v-for="opt in dayOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>
    </div>

    <!-- Boletín como texto plano -->
    <section v-if="!loading && !error && isTexto" class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">Boletín</h2>
      <pre class="p-4 overflow-auto text-sm whitespace-pre-wrap rounded-2xl bg-white/20 dark:bg-black/20 border border-white/30 dark:border-gray-700/40">{{ boletin }}</pre>
    </section>

    <!-- Boletín estructurado -->
    <section v-if="!loading && !error && !isTexto && (boletin?.seccion?.[0]?.apartado?.length)" class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
        Estado general
      </h2>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="apartado in boletin.seccion[0].apartado"
          :key="apartado.nombre"
          class="flex flex-col p-6 transition border shadow-md rounded-2xl bg-white/20 dark:bg-black/20 backdrop-blur-xl border-white/30 dark:border-gray-700/40 hover:shadow-lg"
        >
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
    <section v-if="!loading && !error && !isTexto && (boletin?.seccion?.[1]?.apartado?.length)" class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
        Atmósfera libre
      </h2>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div
          v-for="apartado in boletin.seccion[1].apartado"
          :key="apartado.nombre"
          class="flex flex-col p-6 transition border shadow-md rounded-2xl bg-white/20 dark:bg-black/20 backdrop-blur-xl border-white/30 dark:border-gray-700/40 hover:shadow-lg"
        >
          <h3
            class="mb-2 font-semibold text-gray-800 text-md dark:text-gray-100"
          >
            {{ apartado.cabecera }}
          </h3>
          <p class="leading-relaxed text-gray-600 dark:text-gray-300">
            {{ apartado.texto }}
          </p>
        </div>
      </div>
    </section>

    <!-- Sensación térmica -->
    <section v-if="!loading && !error && !isTexto && (boletin?.seccion?.[2]?.lugar?.length)" class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
        Sensación térmica
      </h2>
      <div class="grid gap-6 sm:grid-cols-2">
        <div
          v-for="lugar in boletin.seccion[2].lugar"
          :key="lugar.nombre"
          class="flex flex-col p-6 transition border shadow-md rounded-2xl bg-white/20 dark:bg-black/20 backdrop-blur-xl border-white/30 dark:border-gray-700/40 hover:shadow-lg"
        >
          <h3
            class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-100"
          >
            {{ lugar.nombre }} ({{ lugar.altitud }})
          </h3>
          <div class="space-y-1 text-gray-600 dark:text-gray-300">
            <p>
              Mínima:
              <span class="font-bold">{{ lugar.minima }}ºC</span> (sensación
              {{ lugar.stminima }}ºC)
            </p>
            <p>
              Máxima:
              <span class="font-bold">{{ lugar.maxima }}ºC</span> (sensación
              {{ lugar.stmaxima }}ºC)
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Fuente -->
    <footer
      class="pt-6 text-sm text-center text-gray-500 border-t dark:text-gray-400 border-white/30 dark:border-gray-700/40"
    >
      Datos: Agencia Estatal de Meteorología -
      <a
        :href="sourceLink"
        target="_blank"
        class="underline hover:text-gray-700 dark:hover:text-gray-200"
        >AEMET</a
      >
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";

const loading = ref(true);
const error = ref(null);
const zona = ref('');
const dia = ref('');
const boletin = ref(null);
const isTexto = computed(() => typeof boletin.value === 'string');

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
    const label = d.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'short' });
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
      throw new Error('No hay area_code guardado en preferencias. Guarda tu ubicación de montaña.');
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
    error.value = e?.message || 'Error cargando predicción de montaña';
  } finally {
    loading.value = false;
  }
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
