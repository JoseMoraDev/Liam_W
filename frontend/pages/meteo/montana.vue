<template>
  <div class="max-w-5xl p-6 mx-auto space-y-10">
    <!-- Título -->
    <div class="text-center">
      <h1
        class="text-3xl font-bold tracking-wide text-gray-800 dark:text-gray-100"
      >
        Predicción AEMET
      </h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        {{ prediccion.origen.tipo }}
      </p>
    </div>

    <!-- Estado general -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
        Estado general
      </h2>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="apartado in prediccion.seccion[0].apartado"
          :key="apartado.nombre"
          class="flex flex-col p-6 transition border shadow-md rounded-2xl bg-white/20 dark:bg-black/20 backdrop-blur-xl border-white/30 dark:border-gray-700/40 hover:shadow-lg"
        >
          <h3
            class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-100"
          >
            {{ apartado.cabecera }}
          </h3>
          <p class="leading-relaxed text-gray-600 dark:text-gray-300">
            {{ apartado.texto }}
          </p>
        </div>
      </div>
    </section>

    <!-- Atmósfera libre -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
        Atmósfera libre
      </h2>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div
          v-for="apartado in prediccion.seccion[1].apartado"
          :key="apartado.nombre"
          class="flex flex-col p-6 transition border shadow-md rounded-2xl bg-white/20 dark:bg-black/20 backdrop-blur-xl border-white/30 dark:border-gray-700/40 hover:shadow-lg"
        >
          <h3
            class="mb-2 font-semibold text-gray-800 text-md dark:text-gray-100"
          >
            {{ apartado.cabecera }}
          </h3>
          <p class="leading-relaxed text-gray-600 dark:text-gray-300">
            {{ apartado.texto }}
          </p>
        </div>
      </div>
    </section>

    <!-- Sensación térmica -->
    <section class="space-y-4">
      <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">
        Sensación térmica
      </h2>
      <div class="grid gap-6 sm:grid-cols-2">
        <div
          v-for="lugar in prediccion.seccion[2].lugar"
          :key="lugar.nombre"
          class="flex flex-col p-6 transition border shadow-md rounded-2xl bg-white/20 dark:bg-black/20 backdrop-blur-xl border-white/30 dark:border-gray-700/40 hover:shadow-lg"
        >
          <h3
            class="mb-2 text-lg font-semibold text-gray-800 dark:text-gray-100"
          >
            {{ lugar.nombre }} ({{ lugar.altitud }})
          </h3>
          <div class="space-y-1 text-gray-600 dark:text-gray-300">
            <p>
              Mínima:
              <span class="font-bold">{{ lugar.minima }}ºC</span> (sensación
              {{ lugar.stminima }}ºC)
            </p>
            <p>
              Máxima:
              <span class="font-bold">{{ lugar.maxima }}ºC</span> (sensación
              {{ lugar.stmaxima }}ºC)
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Fuente -->
    <footer
      class="pt-6 text-sm text-center text-gray-500 border-t dark:text-gray-400 border-white/30 dark:border-gray-700/40"
    >
      Datos: {{ prediccion.origen.productor }} -
      <a
        :href="prediccion.origen.web"
        target="_blank"
        class="underline hover:text-gray-700 dark:hover:text-gray-200"
        >AEMET</a
      >
    </footer>
  </div>
</template>

<script setup>
// Simulación de datos que devolvería la API de AEMET
const prediccion = {
  origen: {
    productor: "Agencia Estatal de Meteorología - AEMET - Gobierno de España",
    web: "http://www.aemet.es",
    tipo: "Predicción de montaña",
    language: "es",
    copyright: "© AEMET. Autorizado el uso de la información citando a AEMET.",
    notaLegal: "http://www.aemet.es/es/nota_legal",
  },
  seccion: [
    {
      apartado: [
        {
          cabecera: "Estado del cielo",
          texto: "Intervalos nubosos...",
          nombre: "nubosidad",
        },
        {
          cabecera: "Precipitaciones",
          texto: "Se esperan chubascos ocasionales...",
          nombre: "pcp",
        },
        {
          cabecera: "Tormentas",
          texto: "Podrán acompañar a los chubascos.",
          nombre: "tormentas",
        },
        {
          cabecera: "Temperaturas",
          texto: "En moderado descenso...",
          nombre: "temperatura",
        },
        {
          cabecera: "Viento",
          texto: "Soplarán vientos flojos o moderados...",
          nombre: "viento",
        },
      ],
      nombre: "prediccion",
    },
    {
      apartado: [
        {
          cabecera: "Altitud isoterma 0ºC",
          texto: "4.000 m",
          nombre: "isocero",
        },
        {
          cabecera: "Altitud isoterma -10ºC",
          texto: "5.600 m",
          nombre: "iso10",
        },
        { cabecera: "Viento a 1500 m", texto: "SW 15 km/h", nombre: "v1500" },
        { cabecera: "Viento a 3000 m", texto: "W 30 km/h", nombre: "v3000" },
      ],
      nombre: "atmosferalibre",
    },
    {
      lugar: [
        {
          minima: 11,
          stminima: 11,
          maxima: 16,
          stmaxima: 16,
          nombre: "Pradollano",
          altitud: "2165 m",
        },
        {
          minima: 7,
          stminima: 4,
          maxima: 12,
          stmaxima: 12,
          nombre: "Borreguiles",
          altitud: "2665 m",
        },
      ],
      nombre: "sensacion_termica",
    },
  ],
};
</script>

<style scoped>
body {
  background: #f9fafb;
}
</style>
