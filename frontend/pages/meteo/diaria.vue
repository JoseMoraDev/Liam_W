<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const datos = ref(null);

// Función para formatear fecha a "lun 02"
function formatearFecha(fechaISO) {
  const fecha = new Date(fechaISO);
  const opciones = { weekday: "short", day: "2-digit" };
  return new Intl.DateTimeFormat("es-ES", opciones)
    .format(fecha)
    .replace(".", ""); // quitar el punto de abreviatura (lun. → lun)
}

// Cargar datos desde API diaria
onMounted(async () => {
  try {
    const res = await axios.get(
      "http://localhost:8000/api/prediccion/diaria/03065"
    );
    datos.value = res.data || [];
  } catch (err) {
    console.error("Error al cargar predicción diaria:", err);
  }
});
</script>

<template>
  <div class="min-h-screen p-4 text-gray-200 bg-gray-900">
    <h1 class="mb-4 text-xl font-bold">📅 Pronóstico diario</h1>

    <div v-if="!datos">Cargando datos...</div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-gray-800 text-slate-300">
            <th class="p-2 text-left">📆 Fecha</th>
            <th class="p-2">🌡️ Temp. máx</th>
            <th class="p-2">🌡️ Temp. mín</th>
            <th class="p-2">🌥️ Estado del cielo</th>
            <th class="p-2">🌧️ Prob. lluvia</th>
            <th class="p-2">📊 Evolución temp.</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="dia in datos"
            :key="dia.fecha"
            class="border-b border-gray-700 hover:bg-gray-800/50"
          >
            <!-- Fecha -->
            <td class="p-2 font-semibold">{{ formatearFecha(dia.fecha) }}</td>

            <!-- Temperatura máxima -->
            <td class="p-2 text-center">
              {{ dia.temperatura?.maxima || "—" }}°C
            </td>

            <!-- Temperatura mínima -->
            <td class="p-2 text-center">
              {{ dia.temperatura?.minima || "—" }}°C
            </td>

            <!-- Estado del cielo (primer valor del día) -->
            <td class="p-2 text-center">
              {{ dia.estadoCielo?.[0]?.descripcion || "—" }}
            </td>

            <!-- Probabilidad de lluvia (primer valor del día) -->
            <td class="p-2 text-center">
              {{ dia.probPrecipitacion?.[0]?.value ?? "—" }}%
            </td>

            <!-- Evolución de temperaturas -->
            <td class="p-2 text-center">
              <div v-if="dia.temperatura?.dato?.length">
                <div
                  v-for="t in dia.temperatura.dato"
                  :key="t.hora"
                  class="text-xs"
                >
                  {{ t.hora }}h → {{ t.value }}°C
                </div>
              </div>
              <div v-else>—</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
