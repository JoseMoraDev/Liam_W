<script setup>
import { ref } from "vue";
const comunidad = ref(null);
</script>

<template>
  <div class="p-6">
    <MapaEspana
      v-model="comunidad"
      @change="(id) => console.log('Elegida:', id)"
    />
    <p class="mt-4">
      Comunidad seleccionada: <strong>{{ comunidad || "—" }}</strong>
    </p>
  </div>
</template>
