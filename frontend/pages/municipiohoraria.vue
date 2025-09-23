<template></template>

<script>
const data = [
  {
    estadoCielo: [
      {
        value: "43n",
        periodo: "20",
        descripcion: "Intervalos nubosos con lluvia escasa",
      },
      {
        value: "44n",
        periodo: "21",
        descripcion: "Nuboso con lluvia escasa",
      },
      {
        value: "43n",
        periodo: "22",
        descripcion: "Intervalos nubosos con lluvia escasa",
      },
      {
        value: "45n",
        periodo: "23",
        descripcion: "Muy nuboso con lluvia escasa",
      },
    ],
    precipitacion: [
      {
        value: "0.1",
        periodo: "20",
      },
      {
        value: "0.1",
        periodo: "21",
      },
      {
        value: "0.1",
        periodo: "22",
      },
      {
        value: "0.1",
        periodo: "23",
      },
    ],
    probPrecipitacion: [
      {
        value: "65",
        periodo: "2002",
      },
    ],
    probTormenta: [
      {
        value: "65",
        periodo: "2002",
      },
    ],
    nieve: [
      {
        value: "0",
        periodo: "20",
      },
      {
        value: "0",
        periodo: "21",
      },
      {
        value: "0",
        periodo: "22",
      },
      {
        value: "0",
        periodo: "23",
      },
    ],
    probNieve: [
      {
        value: "0",
        periodo: "2002",
      },
    ],
    temperatura: [
      {
        value: "23",
        periodo: "21",
      },
      {
        value: "22",
        periodo: "22",
      },
      {
        value: "22",
        periodo: "23",
      },
    ],
    sensTermica: [
      {
        value: "23",
        periodo: "21",
      },
      {
        value: "22",
        periodo: "22",
      },
      {
        value: "22",
        periodo: "23",
      },
    ],
    humedadRelativa: [
      {
        value: "51",
        periodo: "21",
      },
      {
        value: "52",
        periodo: "22",
      },
      {
        value: "54",
        periodo: "23",
      },
    ],
    vientoAndRachaMax: [
      {
        direccion: ["NE"],
        velocidad: ["12"],
        periodo: "21",
      },
      {
        value: "13",
        periodo: "21",
      },
      {
        direccion: ["NE"],
        velocidad: ["8"],
        periodo: "22",
      },
      {
        value: "13",
        periodo: "22",
      },
      {
        direccion: ["N"],
        velocidad: ["9"],
        periodo: "23",
      },
      {
        value: "17",
        periodo: "23",
      },
    ],
    fecha: "2025-09-22T00:00:00",
    orto: "07:50",
    ocaso: "19:59",
  },
  {
    estadoCielo: [
      {
        value: "43n",
        periodo: "00",
        descripcion: "Intervalos nubosos con lluvia escasa",
      },
      {
        value: "43n",
        periodo: "01",
        descripcion: "Intervalos nubosos con lluvia escasa",
      },
      {
        value: "46n",
        periodo: "02",
        descripcion: "Cubierto con lluvia escasa",
      },
      {
        value: "46n",
        periodo: "03",
        descripcion: "Cubierto con lluvia escasa",
      },
      {
        value: "46n",
        periodo: "04",
        descripcion: "Cubierto con lluvia escasa",
      },
      {
        value: "44n",
        periodo: "05",
        descripcion: "Nuboso con lluvia escasa",
      },
      {
        value: "43n",
        periodo: "06",
        descripcion: "Intervalos nubosos con lluvia escasa",
      },
      {
        value: "45n",
        periodo: "07",
        descripcion: "Muy nuboso con lluvia escasa",
      },
      {
        value: "12",
        periodo: "08",
        descripcion: "Poco nuboso",
      },
      {
        value: "11",
        periodo: "09",
        descripcion: "Despejado",
      },
      {
        value: "15",
        periodo: "10",
        descripcion: "Muy nuboso",
      },
      {
        value: "16",
        periodo: "11",
        descripcion: "Cubierto",
      },
      {
        value: "15",
        periodo: "12",
        descripcion: "Muy nuboso",
      },
      {
        value: "16",
        periodo: "13",
        descripcion: "Cubierto",
      },
      {
        value: "12",
        periodo: "14",
        descripcion: "Poco nuboso",
      },
      {
        value: "16",
        periodo: "15",
        descripcion: "Cubierto",
      },
      {
        value: "15",
        periodo: "16",
        descripcion: "Muy nuboso",
      },
      {
        value: "11",
        periodo: "17",
        descripcion: "Despejado",
      },
      {
        value: "11",
        periodo: "18",
        descripcion: "Despejado",
      },
      {
        value: "11",
        periodo: "19",
        descripcion: "Despejado",
      },
      {
        value: "11n",
        periodo: "20",
        descripcion: "Despejado",
      },
      {
        value: "11n",
        periodo: "21",
        descripcion: "Despejado",
      },
      {
        value: "11n",
        periodo: "22",
        descripcion: "Despejado",
      },
      {
        value: "11n",
        periodo: "23",
        descripcion: "Despejado",
      },
    ],
    precipitacion: [
      {
        value: "0.1",
        periodo: "00",
      },
      {
        value: "0.1",
        periodo: "01",
      },
      {
        value: "0.1",
        periodo: "02",
      },
      {
        value: "0.1",
        periodo: "03",
      },
      {
        value: "0.1",
        periodo: "04",
      },
      {
        value: "0.1",
        periodo: "05",
      },
      {
        value: "0.1",
        periodo: "06",
      },
      {
        value: "0.1",
        periodo: "07",
      },
      {
        value: "0",
        periodo: "08",
      },
      {
        value: "0",
        periodo: "09",
      },
      {
        value: "0",
        periodo: "10",
      },
      {
        value: "0",
        periodo: "11",
      },
      {
        value: "0",
        periodo: "12",
      },
      {
        value: "0",
        periodo: "13",
      },
      {
        value: "0",
        periodo: "14",
      },
      {
        value: "0",
        periodo: "15",
      },
      {
        value: "Ip",
        periodo: "16",
      },
      {
        value: "0",
        periodo: "17",
      },
      {
        value: "0",
        periodo: "18",
      },
      {
        value: "0",
        periodo: "19",
      },
      {
        value: "0",
        periodo: "20",
      },
      {
        value: "0",
        periodo: "21",
      },
      {
        value: "0",
        periodo: "22",
      },
      {
        value: "0",
        periodo: "23",
      },
    ],
    probPrecipitacion: [
      {
        value: "100",
        periodo: "0208",
      },
      {
        value: "35",
        periodo: "0814",
      },
      {
        value: "40",
        periodo: "1420",
      },
      {
        value: "10",
        periodo: "2002",
      },
    ],
    probTormenta: [
      {
        value: "60",
        periodo: "0208",
      },
      {
        value: "35",
        periodo: "0814",
      },
      {
        value: "40",
        periodo: "1420",
      },
      {
        value: "10",
        periodo: "2002",
      },
    ],
    nieve: [
      {
        value: "0",
        periodo: "00",
      },
      {
        value: "0",
        periodo: "01",
      },
      {
        value: "0",
        periodo: "02",
      },
      {
        value: "0",
        periodo: "03",
      },
      {
        value: "0",
        periodo: "04",
      },
      {
        value: "0",
        periodo: "05",
      },
      {
        value: "0",
        periodo: "06",
      },
      {
        value: "0",
        periodo: "07",
      },
      {
        value: "0",
        periodo: "08",
      },
      {
        value: "0",
        periodo: "09",
      },
      {
        value: "0",
        periodo: "10",
      },
      {
        value: "0",
        periodo: "11",
      },
      {
        value: "0",
        periodo: "12",
      },
      {
        value: "0",
        periodo: "13",
      },
      {
        value: "0",
        periodo: "14",
      },
      {
        value: "0",
        periodo: "15",
      },
      {
        value: "0",
        periodo: "16",
      },
      {
        value: "0",
        periodo: "17",
      },
      {
        value: "0",
        periodo: "18",
      },
      {
        value: "0",
        periodo: "19",
      },
      {
        value: "0",
        periodo: "20",
      },
      {
        value: "0",
        periodo: "21",
      },
      {
        value: "0",
        periodo: "22",
      },
      {
        value: "0",
        periodo: "23",
      },
    ],
    probNieve: [
      {
        value: "0",
        periodo: "0208",
      },
      {
        value: "0",
        periodo: "0814",
      },
      {
        value: "0",
        periodo: "1420",
      },
      {
        value: "0",
        periodo: "2002",
      },
    ],
    temperatura: [
      {
        value: "22",
        periodo: "00",
      },
      {
        value: "21",
        periodo: "01",
      },
      {
        value: "21",
        periodo: "02",
      },
      {
        value: "20",
        periodo: "03",
      },
      {
        value: "19",
        periodo: "04",
      },
      {
        value: "19",
        periodo: "05",
      },
      {
        value: "18",
        periodo: "06",
      },
      {
        value: "18",
        periodo: "07",
      },
      {
        value: "19",
        periodo: "08",
      },
      {
        value: "20",
        periodo: "09",
      },
      {
        value: "21",
        periodo: "10",
      },
      {
        value: "23",
        periodo: "11",
      },
      {
        value: "24",
        periodo: "12",
      },
      {
        value: "25",
        periodo: "13",
      },
      {
        value: "26",
        periodo: "14",
      },
      {
        value: "25",
        periodo: "15",
      },
      {
        value: "25",
        periodo: "16",
      },
      {
        value: "24",
        periodo: "17",
      },
      {
        value: "24",
        periodo: "18",
      },
      {
        value: "23",
        periodo: "19",
      },
      {
        value: "22",
        periodo: "20",
      },
      {
        value: "21",
        periodo: "21",
      },
      {
        value: "21",
        periodo: "22",
      },
      {
        value: "20",
        periodo: "23",
      },
    ],
    sensTermica: [
      {
        value: "22",
        periodo: "00",
      },
      {
        value: "21",
        periodo: "01",
      },
      {
        value: "21",
        periodo: "02",
      },
      {
        value: "20",
        periodo: "03",
      },
      {
        value: "19",
        periodo: "04",
      },
      {
        value: "19",
        periodo: "05",
      },
      {
        value: "18",
        periodo: "06",
      },
      {
        value: "18",
        periodo: "07",
      },
      {
        value: "19",
        periodo: "08",
      },
      {
        value: "20",
        periodo: "09",
      },
      {
        value: "21",
        periodo: "10",
      },
      {
        value: "23",
        periodo: "11",
      },
      {
        value: "24",
        periodo: "12",
      },
      {
        value: "25",
        periodo: "13",
      },
      {
        value: "26",
        periodo: "14",
      },
      {
        value: "25",
        periodo: "15",
      },
      {
        value: "25",
        periodo: "16",
      },
      {
        value: "24",
        periodo: "17",
      },
      {
        value: "24",
        periodo: "18",
      },
      {
        value: "23",
        periodo: "19",
      },
      {
        value: "22",
        periodo: "20",
      },
      {
        value: "21",
        periodo: "21",
      },
      {
        value: "21",
        periodo: "22",
      },
      {
        value: "20",
        periodo: "23",
      },
    ],
    humedadRelativa: [
      {
        value: "52",
        periodo: "00",
      },
      {
        value: "52",
        periodo: "01",
      },
      {
        value: "52",
        periodo: "02",
      },
      {
        value: "57",
        periodo: "03",
      },
      {
        value: "65",
        periodo: "04",
      },
      {
        value: "70",
        periodo: "05",
      },
      {
        value: "57",
        periodo: "06",
      },
      {
        value: "57",
        periodo: "07",
      },
      {
        value: "60",
        periodo: "08",
      },
      {
        value: "56",
        periodo: "09",
      },
      {
        value: "50",
        periodo: "10",
      },
      {
        value: "46",
        periodo: "11",
      },
      {
        value: "45",
        periodo: "12",
      },
      {
        value: "44",
        periodo: "13",
      },
      {
        value: "43",
        periodo: "14",
      },
      {
        value: "45",
        periodo: "15",
      },
      {
        value: "51",
        periodo: "16",
      },
      {
        value: "58",
        periodo: "17",
      },
      {
        value: "57",
        periodo: "18",
      },
      {
        value: "58",
        periodo: "19",
      },
      {
        value: "61",
        periodo: "20",
      },
      {
        value: "66",
        periodo: "21",
      },
      {
        value: "67",
        periodo: "22",
      },
      {
        value: "66",
        periodo: "23",
      },
    ],
    vientoAndRachaMax: [
      {
        direccion: ["N"],
        velocidad: ["11"],
        periodo: "00",
      },
      {
        value: "17",
        periodo: "00",
      },
      {
        direccion: ["NE"],
        velocidad: ["11"],
        periodo: "01",
      },
      {
        value: "18",
        periodo: "01",
      },
      {
        direccion: ["NE"],
        velocidad: ["11"],
        periodo: "02",
      },
      {
        value: "21",
        periodo: "02",
      },
      {
        direccion: ["N"],
        velocidad: ["13"],
        periodo: "03",
      },
      {
        value: "19",
        periodo: "03",
      },
      {
        direccion: ["N"],
        velocidad: ["12"],
        periodo: "04",
      },
      {
        value: "22",
        periodo: "04",
      },
      {
        direccion: ["N"],
        velocidad: ["13"],
        periodo: "05",
      },
      {
        value: "36",
        periodo: "05",
      },
      {
        direccion: ["NE"],
        velocidad: ["23"],
        periodo: "06",
      },
      {
        value: "25",
        periodo: "06",
      },
      {
        direccion: ["N"],
        velocidad: ["16"],
        periodo: "07",
      },
      {
        value: "23",
        periodo: "07",
      },
      {
        direccion: ["N"],
        velocidad: ["14"],
        periodo: "08",
      },
      {
        value: "25",
        periodo: "08",
      },
      {
        direccion: ["N"],
        velocidad: ["15"],
        periodo: "09",
      },
      {
        value: "27",
        periodo: "09",
      },
      {
        direccion: ["N"],
        velocidad: ["16"],
        periodo: "10",
      },
      {
        value: "19",
        periodo: "10",
      },
      {
        direccion: ["N"],
        velocidad: ["7"],
        periodo: "11",
      },
      {
        value: "23",
        periodo: "11",
      },
      {
        direccion: ["E"],
        velocidad: ["12"],
        periodo: "12",
      },
      {
        value: "27",
        periodo: "12",
      },
      {
        direccion: ["E"],
        velocidad: ["13"],
        periodo: "13",
      },
      {
        value: "26",
        periodo: "13",
      },
      {
        direccion: ["E"],
        velocidad: ["14"],
        periodo: "14",
      },
      {
        value: "28",
        periodo: "14",
      },
      {
        direccion: ["E"],
        velocidad: ["14"],
        periodo: "15",
      },
      {
        value: "36",
        periodo: "15",
      },
      {
        direccion: ["E"],
        velocidad: ["20"],
        periodo: "16",
      },
      {
        value: "24",
        periodo: "16",
      },
      {
        direccion: ["E"],
        velocidad: ["14"],
        periodo: "17",
      },
      {
        value: "21",
        periodo: "17",
      },
      {
        direccion: ["SE"],
        velocidad: ["12"],
        periodo: "18",
      },
      {
        value: "21",
        periodo: "18",
      },
      {
        direccion: ["E"],
        velocidad: ["13"],
        periodo: "19",
      },
      {
        value: "15",
        periodo: "19",
      },
      {
        direccion: ["E"],
        velocidad: ["10"],
        periodo: "20",
      },
      {
        value: "10",
        periodo: "20",
      },
      {
        direccion: ["NE"],
        velocidad: ["7"],
        periodo: "21",
      },
      {
        value: "9",
        periodo: "21",
      },
      {
        direccion: ["N"],
        velocidad: ["7"],
        periodo: "22",
      },
      {
        value: "11",
        periodo: "22",
      },
      {
        direccion: ["N"],
        velocidad: ["8"],
        periodo: "23",
      },
      {
        value: "13",
        periodo: "23",
      },
    ],
    fecha: "2025-09-23T00:00:00",
    orto: "07:51",
    ocaso: "19:58",
  },
  {
    estadoCielo: [
      {
        value: "16n",
        periodo: "00",
        descripcion: "Cubierto",
      },
      {
        value: "16n",
        periodo: "01",
        descripcion: "Cubierto",
      },
      {
        value: "15n",
        periodo: "02",
        descripcion: "Muy nuboso",
      },
      {
        value: "14n",
        periodo: "03",
        descripcion: "Nuboso",
      },
      {
        value: "15n",
        periodo: "04",
        descripcion: "Muy nuboso",
      },
      {
        value: "11n",
        periodo: "05",
        descripcion: "Despejado",
      },
      {
        value: "12n",
        periodo: "06",
        descripcion: "Poco nuboso",
      },
      {
        value: "11n",
        periodo: "07",
        descripcion: "Despejado",
      },
      {
        value: "11",
        periodo: "08",
        descripcion: "Despejado",
      },
      {
        value: "12",
        periodo: "09",
        descripcion: "Poco nuboso",
      },
      {
        value: "17",
        periodo: "10",
        descripcion: "Nubes altas",
      },
      {
        value: "16",
        periodo: "11",
        descripcion: "Cubierto",
      },
      {
        value: "16",
        periodo: "12",
        descripcion: "Cubierto",
      },
      {
        value: "15",
        periodo: "13",
        descripcion: "Muy nuboso",
      },
      {
        value: "15",
        periodo: "14",
        descripcion: "Muy nuboso",
      },
      {
        value: "14",
        periodo: "15",
        descripcion: "Nuboso",
      },
      {
        value: "12",
        periodo: "16",
        descripcion: "Poco nuboso",
      },
      {
        value: "17",
        periodo: "17",
        descripcion: "Nubes altas",
      },
      {
        value: "17",
        periodo: "18",
        descripcion: "Nubes altas",
      },
      {
        value: "17",
        periodo: "19",
        descripcion: "Nubes altas",
      },
    ],
    precipitacion: [
      {
        value: "0",
        periodo: "00",
      },
      {
        value: "0",
        periodo: "01",
      },
      {
        value: "0",
        periodo: "02",
      },
      {
        value: "0",
        periodo: "03",
      },
      {
        value: "0",
        periodo: "04",
      },
      {
        value: "0",
        periodo: "05",
      },
      {
        value: "0",
        periodo: "06",
      },
      {
        value: "0",
        periodo: "07",
      },
      {
        value: "0",
        periodo: "08",
      },
      {
        value: "0",
        periodo: "09",
      },
      {
        value: "0",
        periodo: "10",
      },
      {
        value: "0",
        periodo: "11",
      },
      {
        value: "0",
        periodo: "12",
      },
      {
        value: "0",
        periodo: "13",
      },
      {
        value: "0",
        periodo: "14",
      },
      {
        value: "0",
        periodo: "15",
      },
      {
        value: "0",
        periodo: "16",
      },
      {
        value: "0",
        periodo: "17",
      },
      {
        value: "0",
        periodo: "18",
      },
      {
        value: "0",
        periodo: "19",
      },
    ],
    probPrecipitacion: [
      {
        value: "0",
        periodo: "0208",
      },
      {
        value: "0",
        periodo: "0814",
      },
      {
        value: "0",
        periodo: "1420",
      },
    ],
    probTormenta: [
      {
        value: "0",
        periodo: "0208",
      },
      {
        value: "0",
        periodo: "0814",
      },
      {
        value: "0",
        periodo: "1420",
      },
    ],
    nieve: [
      {
        value: "0",
        periodo: "00",
      },
      {
        value: "0",
        periodo: "01",
      },
      {
        value: "0",
        periodo: "02",
      },
      {
        value: "0",
        periodo: "03",
      },
      {
        value: "0",
        periodo: "04",
      },
      {
        value: "0",
        periodo: "05",
      },
      {
        value: "0",
        periodo: "06",
      },
      {
        value: "0",
        periodo: "07",
      },
      {
        value: "0",
        periodo: "08",
      },
      {
        value: "0",
        periodo: "09",
      },
      {
        value: "0",
        periodo: "10",
      },
      {
        value: "0",
        periodo: "11",
      },
      {
        value: "0",
        periodo: "12",
      },
      {
        value: "0",
        periodo: "13",
      },
      {
        value: "0",
        periodo: "14",
      },
      {
        value: "0",
        periodo: "15",
      },
      {
        value: "0",
        periodo: "16",
      },
      {
        value: "0",
        periodo: "17",
      },
      {
        value: "0",
        periodo: "18",
      },
      {
        value: "0",
        periodo: "19",
      },
    ],
    probNieve: [
      {
        value: "0",
        periodo: "0208",
      },
      {
        value: "0",
        periodo: "0814",
      },
      {
        value: "0",
        periodo: "1420",
      },
    ],
    temperatura: [
      {
        value: "20",
        periodo: "00",
      },
      {
        value: "19",
        periodo: "01",
      },
      {
        value: "18",
        periodo: "02",
      },
      {
        value: "18",
        periodo: "03",
      },
      {
        value: "17",
        periodo: "04",
      },
      {
        value: "17",
        periodo: "05",
      },
      {
        value: "16",
        periodo: "06",
      },
      {
        value: "16",
        periodo: "07",
      },
      {
        value: "16",
        periodo: "08",
      },
      {
        value: "18",
        periodo: "09",
      },
      {
        value: "21",
        periodo: "10",
      },
      {
        value: "23",
        periodo: "11",
      },
      {
        value: "24",
        periodo: "12",
      },
      {
        value: "25",
        periodo: "13",
      },
      {
        value: "25",
        periodo: "14",
      },
      {
        value: "26",
        periodo: "15",
      },
      {
        value: "25",
        periodo: "16",
      },
      {
        value: "25",
        periodo: "17",
      },
      {
        value: "24",
        periodo: "18",
      },
      {
        value: "23",
        periodo: "19",
      },
    ],
    sensTermica: [
      {
        value: "20",
        periodo: "00",
      },
      {
        value: "19",
        periodo: "01",
      },
      {
        value: "18",
        periodo: "02",
      },
      {
        value: "18",
        periodo: "03",
      },
      {
        value: "17",
        periodo: "04",
      },
      {
        value: "17",
        periodo: "05",
      },
      {
        value: "16",
        periodo: "06",
      },
      {
        value: "16",
        periodo: "07",
      },
      {
        value: "16",
        periodo: "08",
      },
      {
        value: "18",
        periodo: "09",
      },
      {
        value: "21",
        periodo: "10",
      },
      {
        value: "23",
        periodo: "11",
      },
      {
        value: "24",
        periodo: "12",
      },
      {
        value: "25",
        periodo: "13",
      },
      {
        value: "25",
        periodo: "14",
      },
      {
        value: "26",
        periodo: "15",
      },
      {
        value: "25",
        periodo: "16",
      },
      {
        value: "25",
        periodo: "17",
      },
      {
        value: "24",
        periodo: "18",
      },
      {
        value: "23",
        periodo: "19",
      },
    ],
    humedadRelativa: [
      {
        value: "68",
        periodo: "00",
      },
      {
        value: "68",
        periodo: "01",
      },
      {
        value: "65",
        periodo: "02",
      },
      {
        value: "62",
        periodo: "03",
      },
      {
        value: "62",
        periodo: "04",
      },
      {
        value: "63",
        periodo: "05",
      },
      {
        value: "62",
        periodo: "06",
      },
      {
        value: "60",
        periodo: "07",
      },
      {
        value: "62",
        periodo: "08",
      },
      {
        value: "55",
        periodo: "09",
      },
      {
        value: "51",
        periodo: "10",
      },
      {
        value: "50",
        periodo: "11",
      },
      {
        value: "47",
        periodo: "12",
      },
      {
        value: "46",
        periodo: "13",
      },
      {
        value: "45",
        periodo: "14",
      },
      {
        value: "43",
        periodo: "15",
      },
      {
        value: "45",
        periodo: "16",
      },
      {
        value: "46",
        periodo: "17",
      },
      {
        value: "50",
        periodo: "18",
      },
      {
        value: "53",
        periodo: "19",
      },
    ],
    vientoAndRachaMax: [
      {
        direccion: ["N"],
        velocidad: ["9"],
        periodo: "00",
      },
      {
        value: "16",
        periodo: "00",
      },
      {
        direccion: ["N"],
        velocidad: ["11"],
        periodo: "01",
      },
      {
        value: "20",
        periodo: "01",
      },
      {
        direccion: ["NE"],
        velocidad: ["13"],
        periodo: "02",
      },
      {
        value: "21",
        periodo: "02",
      },
      {
        direccion: ["NE"],
        velocidad: ["14"],
        periodo: "03",
      },
      {
        value: "19",
        periodo: "03",
      },
      {
        direccion: ["NE"],
        velocidad: ["12"],
        periodo: "04",
      },
      {
        value: "13",
        periodo: "04",
      },
      {
        direccion: ["N"],
        velocidad: ["7"],
        periodo: "05",
      },
      {
        value: "15",
        periodo: "05",
      },
      {
        direccion: ["NE"],
        velocidad: ["10"],
        periodo: "06",
      },
      {
        value: "20",
        periodo: "06",
      },
      {
        direccion: ["NE"],
        velocidad: ["13"],
        periodo: "07",
      },
      {
        value: "23",
        periodo: "07",
      },
      {
        direccion: ["NE"],
        velocidad: ["15"],
        periodo: "08",
      },
      {
        value: "24",
        periodo: "08",
      },
      {
        direccion: ["NE"],
        velocidad: ["16"],
        periodo: "09",
      },
      {
        value: "29",
        periodo: "09",
      },
      {
        direccion: ["NE"],
        velocidad: ["17"],
        periodo: "10",
      },
      {
        value: "30",
        periodo: "10",
      },
      {
        direccion: ["NE"],
        velocidad: ["17"],
        periodo: "11",
      },
      {
        value: "27",
        periodo: "11",
      },
      {
        direccion: ["E"],
        velocidad: ["14"],
        periodo: "12",
      },
      {
        value: "22",
        periodo: "12",
      },
      {
        direccion: ["E"],
        velocidad: ["12"],
        periodo: "13",
      },
      {
        value: "25",
        periodo: "13",
      },
      {
        direccion: ["E"],
        velocidad: ["13"],
        periodo: "14",
      },
      {
        value: "27",
        periodo: "14",
      },
      {
        direccion: ["E"],
        velocidad: ["14"],
        periodo: "15",
      },
      {
        value: "27",
        periodo: "15",
      },
      {
        direccion: ["SE"],
        velocidad: ["13"],
        periodo: "16",
      },
      {
        value: "27",
        periodo: "16",
      },
      {
        direccion: ["SE"],
        velocidad: ["14"],
        periodo: "17",
      },
      {
        value: "26",
        periodo: "17",
      },
      {
        direccion: ["SE"],
        velocidad: ["14"],
        periodo: "18",
      },
      {
        value: "23",
        periodo: "18",
      },
      {
        direccion: ["SE"],
        velocidad: ["13"],
        periodo: "19",
      },
      {
        value: "21",
        periodo: "19",
      },
    ],
    fecha: "2025-09-24T00:00:00",
    orto: "07:52",
    ocaso: "19:56",
  },
];
</script>
