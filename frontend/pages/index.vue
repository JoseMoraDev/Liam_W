<template>
  <div
    class="absolute inset-0 w-full h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg')"
  >
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Contenido principal -->
    <div
      :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full h-screen p-4 transition-opacity duration-300 md:p-8"
    >
      <div class="flex items-end justify-center w-3/4 h-2/10">
        <h1
          class="text-3xl font-bold leading-snug text-center text-white md:text-4xl"
        >
          Live Ambience <br />Weather & Traffic
        </h1>
      </div>

      <div
        class="flex flex-col items-center justify-center w-2/3 mt-10 space-y-6 h-2/10"
      >
        <p class="text-xl text-center text-gray-200 md:text-base">
          Haz tu día más claro y tus viajes más fáciles
        </p>

        <p class="flex justify-center space-x-3 text-xl text-white">
          <font-awesome-icon icon="fa-solid fa-sun" />
          <font-awesome-icon icon="fa-solid fa-umbrella-beach" />
          <font-awesome-icon icon="fa-solid fa-person-walking" />
          <font-awesome-icon icon="fa-brands fa-pagelines" />
          <font-awesome-icon icon="fa-solid fa-car-rear" />
          <font-awesome-icon icon="fa-solid fa-snowflake" />
        </p>
      </div>

      <div class="flex items-end justify-center w-2/3 h-3/10">
        <p class="font-medium text-white text-end">¿Ya tienes cuenta?</p>
      </div>

      <div class="flex items-start justify-center w-full mt-8 space-x-10 h-1/5">
        <div class="flex flex-col items-center">
          <NuxtLink
            to="/login"
            class="flex flex-col items-center justify-center w-20 text-base font-bold text-gray-200 placeholder-gray-300 transition-colors duration-300 transform border border-gray-400 rounded-full h-14 focus:ring-2 focus:ring-gray-400 focus:border-gray-400 focus:outline-none bg-white/20 hover:bg-gray-500 hover:text-white"
          >
            <span class="text-lg">SI</span>
            <span class="pb-1 text-xs font-thin text-gray-200">Login</span>
          </NuxtLink>
        </div>

        <div class="flex flex-col items-center">
          <NuxtLink
            to="/register"
            class="flex flex-col items-center justify-center w-20 text-base font-bold text-gray-200 placeholder-gray-300 transition-colors duration-300 transform border border-gray-400 rounded-full h-14 focus:ring-2 focus:ring-gray-400 focus:border-gray-400 focus:outline-none bg-white/20 hover:bg-gray-500 hover:text-white"
          >
            <span class="text-lg">NO</span>
            <span class="pb-1 text-xs font-thin text-gray-200">Sign up</span>
          </NuxtLink>
        </div>
      </div>

      <!-- Spacer flexible -->
      <div class="flex-grow w-full"></div>

      <!-- Footer con atribución -->
      <footer
        class="absolute w-full text-xs text-center text-gray-600 bottom-2"
      >
        Foto por Danieljschwarz alojada en
        <a
          href="https://www.freepik.com/author/danieljschwarz"
          target="_blank"
          class="underline hover:text-white"
          >www.freepik.com</a
        >
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const mounted = ref(false);

onMounted(() => {
  mounted.value = true;
});
</script>
