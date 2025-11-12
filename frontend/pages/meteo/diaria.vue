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
  <div class="relative w-full min-h-screen px-4 bg-center bg-cover"
    style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 min-h-screen p-4 text-[color:var(--color-text)] pt-16 mx-4">
      <h1 class="mb-6 text-3xl font-bold tracking-tight text-center page-title">
        Pronóstico diario</h1>

      <div v-if="!datos" class="flex items-center justify-center min-h-[40vh]">
        <div class="flex flex-col items-center gap-3 loader">
          <div class="spinner" aria-label="Cargando"></div>
          <div class="loader-text">Cargando datos...</div>
        </div>
      </div>

      <div v-else class="p-4 overflow-x-auto border frost-card border-white/15 rounded-2xl">
        <table class="min-w-full border-collapse">
          <thead>
            <tr class="glass-header text-[color:var(--color-text-muted)]">
              <th class="p-2 text-left">📆 Fecha</th>
              <th class="p-2">🌡️ Temp. máx</th>
              <th class="p-2">🌡️ Temp. mín</th>
              <th class="p-2">🌥️ Estado del cielo</th>
              <th class="p-2">🌧️ Prob. lluvia</th>
              <th class="p-2">📊 Evolución temp.</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="dia in datos" :key="dia.fecha"
              class="border-b theme-border hover:bg-[color:var(--color-overlay-weak)]">
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
                  <div v-for="t in dia.temperatura.dato" :key="t.hora" class="text-xs">
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
  </div>
</template>

<style scoped>
.page-title {
  color: #ffffff !important;
}

.spinner {
  width: 48px;
  height: 48px;
  border-radius: 9999px;
  border: 4px solid color-mix(in srgb, white 75%, var(--color-primary) 25%);
  border-top-color: color-mix(in srgb, white 30%, var(--color-primary) 70%);
  animation: spin 1s linear infinite;
}

.loader-text {
  font-weight: 600;
  color: color-mix(in srgb, white 85%, var(--color-primary) 15%);
}

@keyframes spin {
  from {
    transform: rotate(0)
  }

  to {
    transform: rotate(360deg)
  }
}

/* Glass muy sutil como en horaria */
.frost-card {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

.glass-header {
  background-image: linear-gradient(to bottom, color-mix(in srgb, var(--color-bg) 20%, transparent), color-mix(in srgb, var(--color-bg) 20%, transparent));
}

/* Velo blanquecino en tema claro */
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

  .glass-header {
    background-image: linear-gradient(to bottom,
        color-mix(in srgb, white 28%, transparent),
        color-mix(in srgb, white 28%, transparent));
  }
}

/* Texto blanco dentro del panel para todos los temas */
:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
  color: #ffffff !important;
}
</style>
