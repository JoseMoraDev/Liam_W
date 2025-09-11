<script setup>
import { ref } from "vue";
const comunidad = ref(null);
</script>

<template>
  <div class="p-6">
    <GlassNav />
    <MapaEspana
      v-model="comunidad"
      class="mt-10"
      @change="(id) => console.log('Elegida:', id)"
    />
    <p class="mt-4">
      Comunidad seleccionada: <strong>{{ comunidad || "—" }}</strong>
    </p>
  </div>
</template>
