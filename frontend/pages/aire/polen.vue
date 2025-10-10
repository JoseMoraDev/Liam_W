<script setup>
import { ref, onMounted } from "vue";
import { Line } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
} from "chart.js";

// Importa el cliente axios configurado (ya apunta al backend)
import { axiosClient } from "~/axiosConfig";

// Registrar los componentes de Chart.js
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale
);

// Estado reactivo para los datos
const datos = ref({
  labels: [],
  abedul: [],
  gramineas: [],
  olivo: [],
});

// Opciones base para los gráficos
const opcionesBase = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
  },
  scales: {
    x: {
      ticks: { color: "#ccc", maxRotation: 0, font: { size: 10 } },
      grid: { color: "rgba(255,255,255,0.1)" },
    },
    y: {
      ticks: { color: "#ccc" },
      grid: { color: "rgba(255,255,255,0.1)" },
    },
  },
};

// Gráficos reactivos
const charts = ref({
  abedul: { data: null, options: opcionesBase },
  gramineas: { data: null, options: opcionesBase },
  olivo: { data: null, options: opcionesBase },
});

// Cargar datos desde el backend
onMounted(async () => {
  try {
    const res = await axiosClient.get("/polen");
    const data = res.data;

    datos.value = {
      labels: data.labels,
      abedul: data.abedul,
      gramineas: data.gramineas,
      olivo: data.olivo,
    };

    // Gráfico: Abedul 🌳
    charts.value.abedul.data = {
      labels: datos.value.labels,
      datasets: [
        {
          label: "Abedul",
          data: datos.value.abedul,
          borderColor: "#4ade80",
          backgroundColor: "#4ade80",
          tension: 0.3,
        },
      ],
    };

    // Gráfico: Gramíneas 🌱
    charts.value.gramineas.data = {
      labels: datos.value.labels,
      datasets: [
        {
          label: "Gramíneas",
          data: datos.value.gramineas,
          borderColor: "#facc15",
          backgroundColor: "#facc15",
          tension: 0.3,
        },
      ],
    };

    // Gráfico: Olivo 🌿
    charts.value.olivo.data = {
      labels: datos.value.labels,
      datasets: [
        {
          label: "Olivo",
          data: datos.value.olivo,
          borderColor: "#60a5fa",
          backgroundColor: "#60a5fa",
          tension: 0.3,
        },
      ],
    };
  } catch (err) {
    console.error("Error cargando datos de polen:", err);
  }
});
</script>

<template>
  <div class="min-h-screen p-4 text-gray-100 bg-gray-900">
    <!-- Encabezado -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold">🌿 Niveles de polen</h1>
      <p class="text-gray-400">Ubicación: Elche (Alicante) · Datos horarios</p>
    </div>

    <!-- Lista de tarjetas -->
    <div class="flex flex-col gap-6">
      <!-- Abedul -->
      <div class="p-4 bg-gray-800 shadow rounded-xl">
        <h2 class="mb-2 text-lg font-semibold">🌳 Polen de Abedul</h2>
        <div class="h-48">
          <Line
            v-if="charts.abedul.data"
            :data="charts.abedul.data"
            :options="charts.abedul.options"
          />
          <p v-else class="text-sm text-gray-400">Cargando datos...</p>
        </div>
        <p class="mt-2 text-sm text-gray-400">Unidades: granos/m³</p>
      </div>

      <!-- Gramíneas -->
      <div class="p-4 bg-gray-800 shadow rounded-xl">
        <h2 class="mb-2 text-lg font-semibold">🌱 Polen de Gramíneas</h2>
        <div class="h-48">
          <Line
            v-if="charts.gramineas.data"
            :data="charts.gramineas.data"
            :options="charts.gramineas.options"
          />
          <p v-else class="text-sm text-gray-400">Cargando datos...</p>
        </div>
        <p class="mt-2 text-sm text-gray-400">Unidades: granos/m³</p>
      </div>

      <!-- Olivo -->
      <div class="p-4 bg-gray-800 shadow rounded-xl">
        <h2 class="mb-2 text-lg font-semibold">🌿 Polen de Olivo</h2>
        <div class="h-48">
          <Line
            v-if="charts.olivo.data"
            :data="charts.olivo.data"
            :options="charts.olivo.options"
          />
          <p v-else class="text-sm text-gray-400">Cargando datos...</p>
        </div>
        <p class="mt-2 text-sm text-gray-400">Unidades: granos/m³</p>
      </div>
    </div>
  </div>
</template>
