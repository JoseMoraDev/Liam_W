<template></template>

<script>
const data = {
  status: "ok",
  data: {
    aqi: 15,
    idx: 6631,
    attributions: [
      {
        url: "https://agroambient.gva.es/va/web/calidad-ambiental/informacion-general",
        name: "Conselleria de Infraestructuras, Territorio y Medio Ambiente (Calidad del aire - CALIDAD AMBIENTAL)",
        logo: "valencia-generalitat-valenciana.png",
      },
      {
        url: "http://www.eea.europa.eu/themes/air/",
        name: "European Environment Agency",
        logo: "Europe-EEA.png",
      },
      {
        url: "https://waqi.info/",
        name: "World Air Quality Index Project",
      },
    ],
    city: {
      geo: [38.454722220103, -0.80333332970477],
      name: "Elda, Valencia, Spain",
      url: "https://aqicn.org/city/spain/valencia/elda",
      location: "",
    },
    dominentpol: "o3",
    iaqi: {
      h: {
        v: 68.5,
      },
      no2: {
        v: 1.9,
      },
      o3: {
        v: 14.7,
      },
      p: {
        v: 1015.5,
      },
      pm10: {
        v: 7,
      },
      pm25: {
        v: 21,
      },
      so2: {
        v: 2.6,
      },
      t: {
        v: 21.1,
      },
      w: {
        v: 0.5,
      },
      wg: {
        v: 3.5,
      },
    },
    time: {
      s: "2025-09-23 02:00:00",
      tz: "+02:00",
      v: 1758592800,
      iso: "2025-09-23T02:00:00+02:00",
    },
    forecast: {
      daily: {
        o3: [
          {
            avg: 13,
            day: "2025-09-23",
            max: 18,
            min: 10,
          },
          {
            avg: 13,
            day: "2025-09-24",
            max: 16,
            min: 9,
          },
          {
            avg: 13,
            day: "2025-09-25",
            max: 18,
            min: 9,
          },
          {
            avg: 12,
            day: "2025-09-26",
            max: 21,
            min: 9,
          },
          {
            avg: 13,
            day: "2025-09-27",
            max: 22,
            min: 9,
          },
          {
            avg: 14,
            day: "2025-09-28",
            max: 16,
            min: 14,
          },
        ],
        pm10: [
          {
            avg: 6,
            day: "2025-09-23",
            max: 7,
            min: 4,
          },
          {
            avg: 8,
            day: "2025-09-24",
            max: 9,
            min: 6,
          },
          {
            avg: 8,
            day: "2025-09-25",
            max: 9,
            min: 7,
          },
          {
            avg: 12,
            day: "2025-09-26",
            max: 14,
            min: 9,
          },
          {
            avg: 17,
            day: "2025-09-27",
            max: 23,
            min: 7,
          },
          {
            avg: 22,
            day: "2025-09-28",
            max: 22,
            min: 17,
          },
        ],
        pm25: [
          {
            avg: 12,
            day: "2025-09-23",
            max: 18,
            min: 8,
          },
          {
            avg: 13,
            day: "2025-09-24",
            max: 15,
            min: 11,
          },
          {
            avg: 16,
            day: "2025-09-25",
            max: 20,
            min: 13,
          },
          {
            avg: 24,
            day: "2025-09-26",
            max: 31,
            min: 20,
          },
          {
            avg: 32,
            day: "2025-09-27",
            max: 50,
            min: 18,
          },
          {
            avg: 42,
            day: "2025-09-28",
            max: 42,
            min: 38,
          },
        ],
        uvi: [
          {
            avg: 0,
            day: "2025-09-22",
            max: 6,
            min: 0,
          },
          {
            avg: 0,
            day: "2025-09-23",
            max: 5,
            min: 0,
          },
          {
            avg: 0,
            day: "2025-09-24",
            max: 4,
            min: 0,
          },
          {
            avg: 0,
            day: "2025-09-25",
            max: 6,
            min: 0,
          },
          {
            avg: 0,
            day: "2025-09-26",
            max: 5,
            min: 0,
          },
          {
            avg: 1,
            day: "2025-09-27",
            max: 6,
            min: 0,
          },
        ],
      },
    },
    debug: {
      sync: "2025-09-23T13:17:25+09:00",
    },
  },
};
</script>
