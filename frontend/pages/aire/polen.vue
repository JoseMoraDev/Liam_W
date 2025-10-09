<script setup>
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

import { ref, onMounted } from "vue";

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale
);

const datos = ref({
  labels: [],
  abedul: [],
  gramineas: [],
  olivo: [],
});

const opcionesBase = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
  },
  scales: {
    x: { ticks: { color: "#ccc", maxRotation: 0, font: { size: 10 } } },
    y: { ticks: { color: "#ccc" } },
  },
};

const charts = ref({
  abedul: { data: null, options: opcionesBase },
  gramineas: { data: null, options: opcionesBase },
  olivo: { data: null, options: opcionesBase },
});

onMounted(async () => {
  const res = await fetch(
    "https://air-quality-api.open-meteo.com/v1/air-quality?latitude=38.2699&longitude=-0.7126&hourly=birch_pollen,grass_pollen,olive_pollen"
  );
  const data = await res.json();

  const labels = data.hourly.time.map((t) =>
    new Date(t).toLocaleString("es-ES", {
      weekday: "short",
      hour: "2-digit",
    })
  );

  datos.value = {
    labels,
    abedul: data.hourly.birch_pollen,
    gramineas: data.hourly.grass_pollen,
    olivo: data.hourly.olive_pollen,
  };

  charts.value.abedul.data = {
    labels,
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

  charts.value.gramineas.data = {
    labels,
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

  charts.value.olivo.data = {
    labels,
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
});
</script>

<template>
  <div class="min-h-screen p-4 text-gray-100 bg-gray-900">
    <!-- Encabezado -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold">🌿 Niveles de polen</h1>
      <p class="text-gray-400">Ubicación: Elche (Alicante) · Datos horarios</p>
    </div>

    <!-- Lista de tarjetas, mobile-first -->
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
        </div>
        <p class="mt-2 text-sm text-gray-400">Unidades: granos/m³</p>
      </div>
    </div>
  </div>
</template>
