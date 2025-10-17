<script setup>
import { ref } from "vue";
const comunidad = ref(null);
</script>

<template>
  <div class="p-6">
    <MapaEspana
      v-model="comunidad"
      class="mt-10"
      @change="(id) => console.log('Elegida:', id)"
    />
  </div>
</template>
