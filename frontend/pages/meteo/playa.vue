<script setup>
import { ref, onMounted } from "vue";
import { axiosClient } from "~/axiosConfig";
import { userData } from "~/store/auth";
import PlayaPickerModal from "~/components/PlayaPickerModal.vue";

const datos = ref([]);
const nombrePlaya = ref("");
const openPicker = ref(false);
const municipioId = ref(null);
const municipioName = ref("");
const cpro = ref(null);
const codigoPlaya = ref(null);
const loading = ref(true);
const savingSelection = ref(false);
const error = ref(null);

// Función para formatear fecha estilo "lun 02"
function formatearFechaYYYYMMDD(fechaNum) {
  if (!fechaNum) return "—";
  const str = fechaNum.toString();
  const year = str.substring(0, 4);
  const month = str.substring(4, 6);
  const day = str.substring(6, 8);
  const fecha = new Date(`${year}-${month}-${day}`);
  const opciones = { weekday: "short", day: "2-digit" };
  return new Intl.DateTimeFormat("es-ES", opciones)
    .format(fecha)
    .replace(".", "");
}

async function cargarPrediccion(idPlaya){
  try{
    loading.value = true; error.value = null;
    const { data } = await axiosClient.get(`/aemet/playa/${idPlaya}`, { params:{ t: Date.now() } });
    const playa = Array.isArray(data) ? data[0] : data;
    nombrePlaya.value = playa?.nombre || "";
    datos.value = playa?.prediccion?.dia || [];
  }catch(e){
    error.value = e?.message || 'Error cargando predicción de playa';
  }finally{
    loading.value = false;
  }
}

async function guardarSeleccion(pl){
  savingSelection.value = true;
  try{
    const uid = userData()?.value?.id;
    await axiosClient.post('/user/location-pref', { user_id: uid, codigo_playa: pl.id_playa });
    codigoPlaya.value = pl.id_playa;
    nombrePlaya.value = pl.nombre_playa;
    await cargarPrediccion(pl.id_playa);
  }catch(e){
    console.error(e);
  }finally{
    savingSelection.value = false;
    openPicker.value = false;
  }
}

onMounted(async () => {
  try{
    const uid = userData()?.value?.id;
    const { data } = await axiosClient.get('/user/location-pref', { params: uid ? { user_id: uid } : {} });
    municipioId.value = data?.municipio_id != null ? String(data.municipio_id) : null;
    municipioName.value = data?.municipio_name || '';
    cpro.value = data?.cpro ? String(data.cpro).padStart(2,'0') : null;
    codigoPlaya.value = data?.codigo_playa || null;
    if(codigoPlaya.value){
      await cargarPrediccion(codigoPlaya.value);
    }else{
      openPicker.value = true;
    }
  }catch(e){
    error.value = e?.message || 'Error cargando preferencias de usuario';
  }finally{
    loading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen p-4 text-gray-200 bg-gray-900">
    <h1 class="mb-2 text-xl font-bold">🏖️ Previsión en la playa</h1>
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg text-slate-300">{{ nombrePlaya || 'Selecciona una playa' }}</h2>
      <button @click="openPicker = true" class="px-3 py-1 text-sm rounded-md bg-white/10 border border-white/20 hover:bg-white/20">
        {{ nombrePlaya ? 'Cambiar' : 'Elegir playa' }}
      </button>
    </div>

    <div v-if="loading">Cargando datos...</div>
    <div v-else-if="error" class="text-red-400">{{ error }}</div>
    <div v-else-if="!codigoPlaya">Elige una playa para ver la predicción.</div>

    <div v-else-if="datos && datos.length" class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-gray-800 text-slate-300">
            <th class="p-2 text-left">📆 Fecha</th>
            <th class="p-2">🌥️ Cielo</th>
            <th class="p-2">💨 Viento</th>
            <th class="p-2">🌊 Oleaje</th>
            <th class="p-2">🌡️ Tª máx</th>
            <th class="p-2">🤔 Sens. térmica</th>
            <th class="p-2">🌊 Tª agua</th>
            <th class="p-2">☀️ UV máx</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="dia in datos"
            :key="dia.fecha"
            class="border-b border-gray-700 hover:bg-gray-800/50"
          >
            <!-- Fecha -->
            <td class="p-2 font-semibold">
              {{ formatearFechaYYYYMMDD(dia.fecha) }}
            </td>

            <!-- Estado del cielo -->
            <td class="p-2 text-center">
              {{ dia.estadoCielo?.descripcion1 || "—" }}
            </td>

            <!-- Viento -->
            <td class="p-2 text-center">
              {{ dia.viento?.descripcion1 || "—" }}
            </td>

            <!-- Oleaje -->
            <td class="p-2 text-center">
              {{ dia.oleaje?.descripcion1 || "—" }}
            </td>

            <!-- Temperatura máxima -->
            <td class="p-2 text-center">
              {{ dia.tMaxima?.valor1 || dia.tmaxima?.valor1 || "—" }}°C
            </td>

            <!-- Sensación térmica -->
            <td class="p-2 text-center">
              {{
                dia.sTermica?.descripcion1 || dia.stermica?.descripcion1 || "—"
              }}
            </td>

            <!-- Temperatura del agua -->
            <td class="p-2 text-center">
              {{ dia.tAgua?.valor1 || dia.tagua?.valor1 || "—" }}°C
            </td>

            <!-- Índice UV máximo -->
            <td class="p-2 text-center">
              {{ dia.uvMax?.valor1 || "—" }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <PlayaPickerModal
      :open="openPicker"
      :municipio-id="municipioId"
      :cpro="cpro"
      :municipio-name="municipioName"
      :saving="savingSelection"
      @close="openPicker = false"
      @selected="guardarSeleccion"
    />
  </div>
</template>
