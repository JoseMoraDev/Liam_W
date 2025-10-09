<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const datos = ref(null);
const nombrePlaya = ref("");

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

// Cargar datos desde API playa
onMounted(async () => {
  try {
    const res = await axios.get(
      "http://localhost:8000/api/aemet/playa/0301101"
    );

    const playa = res.data[0];
    nombrePlaya.value = playa.nombre;
    datos.value = playa.prediccion.dia || [];
  } catch (err) {
    console.error("Error al cargar predicción de playa:", err);
  }
});
</script>

<template>
  <div class="min-h-screen p-4 text-gray-200 bg-gray-900">
    <h1 class="mb-2 text-xl font-bold">🏖️ Previsión en la playa</h1>
    <h2 class="mb-4 text-lg text-slate-300">{{ nombrePlaya }}</h2>

    <div v-if="!datos">Cargando datos...</div>

    <div v-else class="overflow-x-auto">
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
  </div>
</template>
