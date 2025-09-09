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
      <!-- Titulo -->
      <div class="flex items-end justify-center w-3/4 h-2/10">
        <h1
          class="text-3xl font-bold leading-snug text-center text-white md:text-4xl"
        >
          Live Ambience <br />Weather & Traffic
        </h1>
      </div>

      <!-- sección -->
      <div
        class="flex flex-col items-center justify-center w-2/3 mt-10 space-y-6 h-2/10"
      >
        <p class="mt-2 text-xl text-center text-gray-200 md:text-2xl">
          Iniciar sesión
        </p>
      </div>

      <!-- formulario -->
      <div class="flex items-start justify-center w-10/12 mt-4 h-3/10">
        <form
          @submit.prevent="submitLogin"
          class="flex flex-col w-full max-w-sm space-y-4"
        >
          <!-- div correo -->
          <div class="relative w-full">
            <!-- Icono -->
            <font-awesome-icon
              icon="fa-solid fa-envelope"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none"
            />

            <!-- Input -->
            <input
              id="email"
              v-model="email"
              type="email"
              required
              placeholder="Correo"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border border-gray-400 rounded-md bg-white/10 focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-400"
            />
          </div>

          <!-- div contraseña -->
          <div class="relative w-full">
            <!-- Icono -->
            <font-awesome-icon
              icon="fa-solid fa-key"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none"
            />

            <!-- Input -->
            <input
              id="password"
              v-model="password"
              type="password"
              required
              placeholder="Contraseña"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border border-gray-400 rounded-md bg-white/10 focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-400"
            />
          </div>

          <div class="flex justify-center !mt-8">
            <button
              type="submit"
              class="w-1/2 py-2 font-bold text-gray-200 transition-colors border border-gray-400 rounded-md bg-white/30 hover:bg-gray-400 hover:text-white"
            >
              Entrar
            </button>
          </div>
        </form>
      </div>

      <div class="flex justify-between w-full space-x-3">
        <!-- Botón 1 -->
        <NuxtLink
          to="/password"
          class="w-1/2 py-2 text-sm italic text-center text-gray-200 transition-colors border border-gray-400 rounded-md bg-gray-500/40 hover:bg-gray-400 hover:text-white"
        >
          He olvidado la contraseña
        </NuxtLink>

        <!-- Botón 2 -->
        <NuxtLink
          to="/register"
          class="w-1/2 py-2 text-sm italic text-center text-gray-200 transition-colors border border-gray-400 rounded-md bg-gray-500/40 hover:bg-gray-400 hover:text-white"
        >
          Aún no tengo una cuenta
        </NuxtLink>
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
const email = ref("");
const password = ref("");

function submitLogin() {
  console.log("Login con:", email.value, password.value);
  // Aquí harías tu llamada a la API de backend con fetch/axios
}

onMounted(() => {
  mounted.value = true;
});
</script>

<style scoped>
/* Forzar inputs autofill a mantener fondo transparente */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
textarea:-webkit-autofill,
textarea:-webkit-autofill:hover,
textarea:-webkit-autofill:focus {
  -webkit-box-shadow: 0 0 0px 1000px transparent inset !important;
  -webkit-text-fill-color: #e5e7eb !important; /* Gris claro como el texto */
  transition: background-color 5000s ease-in-out 0s !important;
}
</style>
