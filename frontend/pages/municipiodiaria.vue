<template></template>

<script>
const data = [
  {
    fecha: "2025-09-23",
    probPrecipitacion: [
      {
        value: 100,
        periodo: "00-24",
      },
      {
        value: 100,
        periodo: "00-12",
      },
      {
        value: 50,
        periodo: "12-24",
      },
      {
        value: 100,
        periodo: "00-06",
      },
      {
        value: 35,
        periodo: "06-12",
      },
      {
        value: 40,
        periodo: "12-18",
      },
      {
        value: 10,
        periodo: "18-24",
      },
    ],
    estadoCielo: [
      {
        value: "25",
        periodo: "00-24",
        descripcion: "Muy nuboso con lluvia",
      },
      {
        value: "25",
        periodo: "00-12",
        descripcion: "Muy nuboso con lluvia",
      },
      {
        value: "24",
        periodo: "12-24",
        descripcion: "Nuboso con lluvia",
      },
      {
        value: "24n",
        periodo: "00-06",
        descripcion: "Nuboso con lluvia",
      },
      {
        value: "15",
        periodo: "06-12",
        descripcion: "Muy nuboso",
      },
      {
        value: "44",
        periodo: "12-18",
        descripcion: "Nuboso con lluvia escasa",
      },
      {
        value: "14",
        periodo: "18-24",
        descripcion: "Nuboso",
      },
    ],
    temperatura: {
      maxima: 26,
      minima: 18,
      dato: [
        {
          value: 19,
          hora: 6,
        },
        {
          value: 26,
          hora: 12,
        },
        {
          value: 22,
          hora: 18,
        },
        {
          value: 18,
          hora: 24,
        },
      ],
    },
  },
  {
    fecha: "2025-09-24",
    probPrecipitacion: [
      {
        value: 0,
        periodo: "00-24",
      },
      {
        value: 0,
        periodo: "00-12",
      },
      {
        value: 0,
        periodo: "12-24",
      },
    ],
    estadoCielo: [
      {
        value: "17",
        periodo: "00-24",
        descripcion: "Nubes altas",
      },
      {
        value: "12",
        periodo: "00-12",
        descripcion: "Poco nuboso",
      },
      {
        value: "17",
        periodo: "12-24",
        descripcion: "Nubes altas",
      },
    ],
    temperatura: {
      maxima: 26,
      minima: 15,
      dato: [],
    },
  },
  {
    fecha: "2025-09-25",
    probPrecipitacion: [
      {
        value: 5,
        periodo: "00-24",
      },
      {
        value: 5,
        periodo: "00-12",
      },
      {
        value: 0,
        periodo: "12-24",
      },
    ],
    estadoCielo: [
      {
        value: "14",
        periodo: "00-24",
        descripcion: "Nuboso",
      },
      {
        value: "14",
        periodo: "00-12",
        descripcion: "Nuboso",
      },
      {
        value: "17",
        periodo: "12-24",
        descripcion: "Nubes altas",
      },
    ],
    temperatura: {
      maxima: 27,
      minima: 16,
      dato: [],
    },
  },
  {
    fecha: "2025-09-26",
    probPrecipitacion: [
      {
        value: 5,
      },
    ],
    estadoCielo: [
      {
        value: "12",
        descripcion: "Poco nuboso",
      },
    ],
    temperatura: {
      maxima: 29,
      minima: 16,
      dato: [],
    },
  },
  {
    fecha: "2025-09-27",
    probPrecipitacion: [
      {
        value: 0,
      },
    ],
    estadoCielo: [
      {
        value: "12",
        descripcion: "Poco nuboso",
      },
    ],
    temperatura: {
      maxima: 29,
      minima: 15,
      dato: [],
    },
  },
  {
    fecha: "2025-09-28",
    probPrecipitacion: [
      {
        value: 10,
      },
    ],
    estadoCielo: [
      {
        value: "13",
        descripcion: "Intervalos nubosos",
      },
    ],
    temperatura: {
      maxima: 28,
      minima: 17,
      dato: [],
    },
  },
];
</script>
