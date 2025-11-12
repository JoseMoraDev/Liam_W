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
    <div class="relative w-full min-h-screen px-8 pt-10 mt-4 bg-center bg-cover"
        style="background-image: url('/img/menu.jpg'); background-attachment: fixed;">
        <div class="absolute inset-0 bg-black/40"></div>

        <div class="relative z-10 min-h-screen p-4 text-[color:var(--color-text)]">
            <h1 class="mb-6 text-3xl font-bold tracking-tight text-center page-title">Pronóstico
                horario</h1>

            <div v-if="!dias.length" class="flex items-center justify-center min-h-[50vh]">
                <div class="flex flex-col items-center gap-3 loader">
                    <div class="spinner" aria-label="Cargando"></div>
                    <div class="loader-text">Cargando datos...</div>
                </div>
            </div>

            <div v-else class="space-y-8">
                <div v-for="(dia, index) in dias" :key="index"
                    class="p-4 overflow-x-auto border frost-card border-white/15 rounded-2xl">
                    <h2 class="mb-3 text-lg font-semibold">📅 {{ dia.fecha.split("T")[0] }}</h2>
                    <table class="min-w-full border-collapse table-fixed">
                        <thead>
                            <tr class="glass-header text-[color:var(--color-text-muted)]">
                                <th class="w-20 p-2 text-left">Hora</th>
                                <th class="w-20 p-2 text-center">Temp</th>
                                <th class="p-2 text-center w-28">Sens. térmica</th>
                                <th class="p-2 text-center w-28">Viento</th>
                                <th class="w-24 p-2 text-center">Humedad</th>
                                <th class="w-32 p-2 text-center">Lluvia</th>
                                <th class="w-32 p-2 text-center">Nieve</th>
                            </tr>
                        </thead>
                        <tbody class="glass-body">
                            <tr v-for="h in dia.periodos" :key="h"
                                class="border-b theme-border hover:bg-[color:var(--color-overlay-weak)]">
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
    </div>
</template>

<style scoped>
/* Glass muy sutil para paneles por día */
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

/* Encabezado con leve velo para contraste */
.glass-header {
    background-image: linear-gradient(to bottom, color-mix(in srgb, var(--color-bg) 20%, transparent), color-mix(in srgb, var(--color-bg) 20%, transparent));
}

/* Cuerpo: usaremos blanco forzado más abajo para todos los temas */
.glass-body td {
    color: inherit;
}

/* En temas claros, añadir un velo blanquecino extra para máxima legibilidad */
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

    /* No fijamos color en claro; el forzado global a blanco se aplica abajo */
}

/* Forzar texto blanco en todos los temas dentro del panel para máxima legibilidad */
:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
    color: #ffffff !important;
}

/* Título de página siempre blanco */
.page-title {
    color: #ffffff !important;
}

/* Porcentajes en azul visible incluso con override global blanco */
:deep(.frost-card .text-blue-400),
:deep(.frost-card .text-cyan-300),
:deep(.frost-card .prob-porcentaje) {
    color: #60a5fa !important;
    /* Tailwind sky-400 aprox */
}

/* Loader pastel usando el color primario del tema */
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
    letter-spacing: 0.2px;
    color: color-mix(in srgb, white 85%, var(--color-primary) 15%);
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
