<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const dias = ref([]);

function findVal(arr, h) {
  if (!arr) return null;
  const it = arr.find((x) => x.periodo === h);
  return it ? it.value ?? null : null;
}

onMounted(async () => {
  try {
    const res = await axios.get(
      "http://localhost:8000/api/prediccion/horaria/03065"
    );

    const horaActual = new Date().getHours();
    const hoyStr = new Date().toISOString().split("T")[0];

    dias.value = res.data.map((dia) => {
      const periodos = [];

      // Recorrer todas las horas de los datos del backend
      for (let h = 0; h < 24; h++) {
        const hStr = h.toString().padStart(2, "0");

        // Si hay datos en cualquier campo para esa hora
        const tieneDatos = [
          "temperatura",
          "sensTermica",
          "vientoAndRachaMax",
          "humedadRelativa",
          "precipitacion",
          "nieve",
        ].some((c) => dia[c]?.some((v) => v.periodo === hStr));

        if (!tieneDatos) continue;

        // Si es el día actual, solo mostrar horas >= hora actual
        if (dia.fecha.split("T")[0] === hoyStr && h < horaActual) continue;

        periodos.push(hStr);
      }

      return { ...dia, periodos };
    });
  } catch (err) {
    console.error("Error al cargar predicción:", err);
  }
});
</script>

<template>
  <div class="min-h-screen p-4 text-gray-200 bg-gray-900">
    <h1 class="mb-4 text-xl font-bold">🌦️ Pronóstico horario</h1>

    <div v-if="!dias.length">Cargando datos...</div>

    <div v-else class="space-y-8">
      <div v-for="(dia, index) in dias" :key="index" class="overflow-x-auto">
        <h2 class="mb-2 font-semibold">📅 {{ dia.fecha.split("T")[0] }}</h2>
        <table class="min-w-full border-collapse table-fixed">
          <thead>
            <tr class="bg-gray-800 text-slate-300">
              <th class="w-20 p-2 text-left">Hora</th>
              <th class="w-20 p-2 text-center">Temp</th>
              <th class="p-2 text-center w-28">Sens. térmica</th>
              <th class="p-2 text-center w-28">Viento</th>
              <th class="w-24 p-2 text-center">Humedad</th>
              <th class="w-32 p-2 text-center">Lluvia</th>
              <th class="w-32 p-2 text-center">Nieve</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="h in dia.periodos"
              :key="h"
              class="border-b border-gray-700 hover:bg-gray-800/50"
            >
              <td class="p-2 font-semibold">{{ h }}:00</td>
              <td class="p-2 text-center">
                {{ findVal(dia.temperatura, h) ?? "—" }}°C
              </td>
              <td class="p-2 text-center">
                {{ findVal(dia.sensTermica, h) ?? "—" }}°C
              </td>
              <td class="p-2 text-center">
                {{ findVal(dia.vientoAndRachaMax, h) ?? "—" }} km/h
              </td>
              <td class="p-2 text-center">
                {{ findVal(dia.humedadRelativa, h) ?? "—" }}%
              </td>
              <td class="p-2 text-center">
                <div class="flex flex-col items-center">
                  <div>{{ findVal(dia.precipitacion, h) ?? "—" }} mm</div>
                  <div class="mt-1 text-xs text-blue-400">
                    {{ findVal(dia.probPrecipitacion, h) ?? "—" }}%
                  </div>
                </div>
              </td>
              <td class="p-2 text-center">
                <div class="flex flex-col items-center">
                  <div>{{ findVal(dia.nieve, h) ?? "—" }} mm</div>
                  <div class="mt-1 text-xs text-cyan-300">
                    {{ findVal(dia.probNieve, h) ?? "—" }}%
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
