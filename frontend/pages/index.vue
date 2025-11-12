<template>
  <div
    class="absolute inset-0 w-full h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg')"
  >
    <!-- Capa oscura -->
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Contenido principal -->
    <div
      :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full h-screen p-4 transition-opacity duration-300 md:p-8"
    >
      <div class="mt-20"></div>
      <!-- Título con vidrio sutil y compacto -->
      <div
        class="px-6 py-3 mt-16 shadow-inner rounded-2xl bg-white/5 backdrop-blur-sm"
      >
        <h1
          class="text-3xl font-bold leading-snug text-center text-white md:text-4xl"
        >
          Live Ambience <br />Weather & Traffic
        </h1>
      </div>

      <!-- Slogan con vidrio sutil y compacto -->
      <div
        class="px-6 py-3 space-y-4 text-center shadow-inner mt-28 rounded-2xl bg-white/5 backdrop-blur-sm"
      >
        <p class="text-lg text-gray-200 md:text-base">
          {{ t('home.slogan_line1') }} <br />
          {{ t('home.slogan_line2') }}
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

      <!-- Pregunta + Botones en vidrio igual que los otros -->
      <div
        class="px-6 py-4 space-y-4 text-center shadow-inner mt-28 rounded-2xl bg-white/5 backdrop-blur-sm"
      >
        <p class="font-medium text-white">{{ t('home.have_account') }}</p>

        <div class="flex justify-center space-x-6">
          <!-- Botón SI -->
          <NuxtLink
            to="/login"
            class="flex flex-col items-center justify-center w-24 h-20 font-bold text-gray-200 transition duration-300 rounded-xl bg-white/5 hover:bg-white/10 hover:text-white"
          >
            <span class="text-lg">{{ t('home.yes') }}</span>
            <span class="pb-1 text-xs font-thin text-gray-300">{{ t('nav.login') }}</span>
          </NuxtLink>

          <!-- Botón NO -->
          <NuxtLink
            to="/register"
            class="flex flex-col items-center justify-center w-24 h-20 font-bold text-gray-200 transition duration-300 rounded-xl bg-white/5 hover:bg-white/10 hover:text-white"
          >
            <span class="text-lg">{{ t('home.no') }}</span>
            <span class="pb-1 text-xs font-thin text-gray-300">{{ t('home.signup') }}</span>
          </NuxtLink>
        </div>
      </div>

      <!-- Spacer flexible -->
      <div class="flex-grow w-full"></div>

      <!-- Footer -->
      <footer
        class="absolute w-full text-xs text-center text-gray-600 bottom-2"
      >
        {{ t('home.photo_credit') }}
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
import { useI18n } from 'vue-i18n'

const mounted = ref(false);
const { t } = useI18n()


onMounted(() => {
  // Si no hubo redirección, mostramos la home normalmente
  mounted.value = true;
});
</script>
