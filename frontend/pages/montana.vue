<template></template>

<script>
const data = {
  zona: "Pirineo Navarro",
  dia: "0",
  boletin: [
    {
      origen: {
        productor:
          "Agencia Estatal de Meteorología - AEMET - Gobierno de España",
        web: "http://www.aemet.es",
        tipo: "Predicción de montaña",
        language: "es",
        copyright:
          "© AEMET. Autorizado el uso de la información y su reproducción citando a AEMET como autora de la misma.",
        notaLegal: "http://www.aemet.es/es/nota_legal",
      },
      seccion: [
        {
          apartado: [
            {
              cabecera: "Estado del cielo",
              texto:
                "Nuboso o muy nuboso, pudiendo abrir grandes claros por la mañana. Predominio de nubosidad baja, así como algunas nubes medias en horas centrales y por la tarde.",
              nombre: "nubosidad",
            },
            {
              cabecera: "Precipitaciones",
              texto:
                "Lluvias débiles y ocasionales, preferentemente de madrugada y por la tarde, que podrían ser en forma de nieve por encima de unos 1500-1700 metros.",
              nombre: "pcp",
            },
            {
              cabecera: "Tormentas",
              texto: "No se esperan.",
              nombre: "tormentas",
            },
            {
              cabecera: "Temperaturas",
              texto:
                "Mínimas sin cambios y máximas en ligero descenso, localmente moderado en cotas altas, donde podrá haber alguna helada débil.",
              nombre: "temperatura",
            },
            {
              cabecera: "Viento",
              texto:
                "Soplarán vientos moderados de componente norte, con intervalos de intensidad fuerte.",
              nombre: "viento",
            },
          ],
          lugar: [],
          parrafo: [],
          nombre: "prediccion",
        },
        {
          apartado: [
            {
              cabecera: "Altitud de la isoterma de 0 ºC en la atmósfera libre",
              texto: "2200 m",
              nombre: "isocero",
            },
            {
              cabecera:
                "Altitud de la isoterma de -10 ºC en la atmósfera libre",
              texto: "4000 m",
              nombre: "iso10",
            },
            {
              cabecera: "Viento en atmósfera libre a 1500 metros",
              texto: "NE 30 km/h",
              nombre: "v1500",
            },
            {
              cabecera: "Viento en atmósfera libre a 3000 metros",
              texto: "NW 40 km/h",
              nombre: "v3000",
            },
          ],
          lugar: [],
          parrafo: [],
          nombre: "atmosferalibre",
        },
        {
          apartado: [],
          lugar: [
            {
              minima: 4,
              stminima: 3,
              maxima: 8,
              stmaxima: 6,
              nombre: "Roncesvalles",
              altitud: "940 m",
            },
            {
              minima: 6,
              stminima: 4,
              maxima: 11,
              stmaxima: 11,
              nombre: "Isaba",
              altitud: "820 m",
            },
            {
              minima: -2,
              stminima: -11,
              maxima: 1,
              stmaxima: -6,
              nombre: "Pico Ori",
              altitud: "2021 m",
            },
            {
              minima: -5,
              stminima: -14,
              maxima: -1,
              stmaxima: -8,
              nombre: "Mesa de los Tres Reyes",
              altitud: "2428 m",
            },
          ],
          parrafo: [],
          nombre: "sensacion_termica",
        },
      ],
      id: "nav1",
      nombre: "Predicción",
    },
  ],
};
</script>
