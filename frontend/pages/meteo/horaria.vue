<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

// Estado reactivo
const datos = ref(null);
const horas = Array.from({ length: 24 }, (_, i) =>
  i.toString().padStart(2, "0")
);

// Función para expandir los rangos de probabilidad a horas
function expandirProbabilidades(array) {
  const resultado = [];
  array.forEach((item) => {
    const periodo = item.periodo;
    if (periodo.length === 4) {
      // ejemplo: "0814"
      const start = parseInt(periodo.substring(0, 2));
      const end = parseInt(periodo.substring(2, 4));
      for (let h = start; h <= end; h++) {
        resultado.push({
          periodo: h.toString().padStart(2, "0"),
          value: item.value,
        });
      }
    }
  });
  return resultado;
}

// Cargar datos desde API
onMounted(async () => {
  try {
    const res = await axios.get(
      "http://localhost:8000/api/prediccion/horaria/03065"
    );

    // Nos quedamos solo con el primer día
    const dia = res.data[0];

    datos.value = {
      ...dia,
      probPrecipitacion: expandirProbabilidades(dia.probPrecipitacion || []),
      probTormenta: expandirProbabilidades(dia.probTormenta || []),
      probNieve: expandirProbabilidades(dia.probNieve || []),
    };
  } catch (err) {
    console.error("Error al cargar predicción:", err);
  }
});
</script>

<template>
  <div class="min-h-screen p-4 text-gray-200 bg-gray-900">
    <h1 class="mb-4 text-xl font-bold">🌦️ Pronóstico por horas</h1>

    <div v-if="!datos">Cargando datos...</div>

    <div v-else class="overflow-x-auto">
      <table class="min-w-full border-collapse">
        <thead>
          <tr class="bg-gray-800 text-slate-300">
            <th class="p-2 text-left">Hora</th>
            <th class="p-2">🌡️ Temp</th>
            <th class="p-2">🤔 Sens. térmica</th>
            <th class="p-2">💨 Viento</th>
            <th class="p-2">💧 Humedad</th>
            <th class="p-2">🌧️ Lluvia</th>
            <th class="p-2">❄️ Nieve</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="h in horas"
            :key="h"
            class="border-b border-gray-700 hover:bg-gray-800/50"
          >
            <!-- Hora -->
            <td class="p-2 font-semibold">{{ h }}:00</td>

            <!-- Temp -->
            <td class="p-2 text-center">
              {{
                datos.temperatura.find((t) => t.periodo === h)?.value || "—"
              }}°C
            </td>

            <!-- Sensación térmica -->
            <td class="p-2 text-center">
              {{
                datos.sensTermica.find((s) => s.periodo === h)?.value || "—"
              }}°C
            </td>

            <!-- Viento -->
            <td class="p-2 text-center">
              {{
                datos.vientoAndRachaMax.find((v) => v.periodo === h)
                  ?.direccion?.[0] || ""
              }}
              {{
                datos.vientoAndRachaMax.find((v) => v.periodo === h)
                  ?.velocidad?.[0] || "—"
              }}
              km/h
            </td>

            <!-- Humedad -->
            <td class="p-2 text-center">
              {{
                datos.humedadRelativa.find((hu) => hu.periodo === h)?.value ||
                "—"
              }}%
            </td>

            <!-- Lluvia -->
            <td class="p-2 text-center">
              <div class="relative inline-block">
                {{
                  datos.precipitacion.find((p) => p.periodo === h)?.value || "—"
                }}
                mm
                <span class="absolute right-0 text-xs text-blue-400 -bottom-3">
                  {{
                    datos.probPrecipitacion.find((pp) => pp.periodo === h)
                      ?.value || "—"
                  }}%
                </span>
              </div>
            </td>

            <!-- Nieve -->
            <td class="p-2 text-center">
              <div class="relative inline-block">
                {{ datos.nieve.find((n) => n.periodo === h)?.value || "—" }}
                mm
                <span class="absolute right-0 text-xs text-cyan-300 -bottom-3">
                  {{
                    datos.probNieve.find((pn) => pn.periodo === h)?.value ||
                    "—"
                  }}%
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
