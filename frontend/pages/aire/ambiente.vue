<script setup>
import { ref, onMounted } from "vue";
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";

const aire = ref(null);
const loading = ref(true);
const error = ref(null);

// Formatear fecha "jue 02"
function formatearFecha(fechaISO) {
  const fecha = new Date(fechaISO);
  const opciones = { weekday: "short", day: "2-digit" };
  return new Intl.DateTimeFormat("es-ES", opciones)
    .format(fecha)
    .replace(".", "");
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
      error.value = res.data?.data || res.data?.message || 'Error en AQICN';
      aire.value = null;
    }
  } catch (err) {
    console.error("Error al cargar calidad del aire:", err);
    error.value = err?.response?.data?.error || err?.message || 'Error desconocido';
    aire.value = null;
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="relative w-full min-h-screen bg-center bg-cover" style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 min-h-screen p-4 text-[color:var(--color-text)]">
      <h1 class="mb-4 text-xl font-bold">🌍 Calidad del aire</h1>

    <div v-if="loading">Cargando datos...</div>
    <div v-else-if="error" class="text-red-400">⚠️ {{ error }}</div>
    <div v-else-if="!aire">No hay datos disponibles para esta ubicación.</div>
    <div v-else>
      <!-- Info general -->
      <div class="mb-4">
        <p class="text-lg font-semibold">📍 {{ aire.city?.name || '—' }}</p>
        <p class="text-sm text-slate-400">
          Índice AQI actual: <span class="font-bold">{{ aire.aqi ?? '—' }}</span>
          ({{ (aire.dominentpol || '')?.toString()?.toUpperCase() || '—' }})
        </p>
        <p class="text-sm text-slate-400">
          Última actualización: {{ aire.time?.s || '—' }}
        </p>
      </div>

      <!-- Valores actuales -->
      <h2 class="mb-2 text-lg font-bold">🔎 Valores actuales</h2>
      <table class="min-w-full mb-6 border-collapse">
        <thead>
          <tr class="bg-gray-800 text-slate-300">
            <th class="p-2">CO</th>
            <th class="p-2">NO₂</th>
            <th class="p-2">O₃</th>
            <th class="p-2">PM10</th>
            <th class="p-2">PM2.5</th>
            <th class="p-2">🌡️ Temp</th>
            <th class="p-2">💧 Humedad</th>
            <th class="p-2">📈 Presión</th>
            <th class="p-2">🌬️ Viento</th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-b border-gray-700">
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

      <!-- Pronóstico contaminantes -->
      <h2 class="mb-2 text-lg font-bold">📅 Pronóstico contaminantes</h2>
      <div v-if="aire.forecast?.daily?.pm25?.length" class="overflow-x-auto">
        <table class="min-w-full border-collapse">
          <thead>
            <tr class="bg-gray-800 text-slate-300">
              <th class="p-2 text-left">📆 Fecha</th>
              <th class="p-2">PM2.5 (µg/m³)</th>
              <th class="p-2">PM10 (µg/m³)</th>
              <th class="p-2">O₃ (µg/m³)</th>
              <th class="p-2">UV</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(dia, index) in aire.forecast.daily.pm25"
              :key="dia?.day || index"
              class="border-b border-gray-700 hover:bg-gray-800/50"
            >
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
      <div v-else class="text-slate-400">No hay pronóstico disponible.</div>
    </div>
    </div>
  </div>
</template>
