<template>
  <div
    class="absolute inset-0 w-full h-screen bg-center bg-cover"
    style="background-image: url('/img/menu.jpg')"
  >
    <div class="absolute inset-0 bg-black/40"></div>

    <div
      :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full h-screen p-4 transition-opacity duration-300 md:p-8"
    >
      <!-- Título -->
      <div class="flex items-end justify-center w-3/4 h-2/10">
        <h1
          class="text-3xl font-bold leading-snug text-center text-white md:text-4xl"
        >
          Live Ambience <br />Weather & Traffic
        </h1>
      </div>

      <div
        class="flex flex-col items-center justify-center w-2/3 mt-10 space-y-6 h-1/10"
      >
        <p class="mt-2 text-xl text-center text-gray-200 md:text-2xl">
          Cambiar contraseña
        </p>
      </div>

      <!-- Formulario -->
      <div class="flex items-start justify-center w-10/12 mt-4 h-3/10">
        <form
          @submit.prevent="submitPassword"
          class="flex flex-col w-full max-w-sm space-y-4"
        >
          <!-- Nueva contraseña -->
          <div class="relative w-full">
            <font-awesome-icon
              icon="fa-solid fa-lock"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none"
            />
            <input
              type="password"
              v-model="password"
              placeholder="Nueva contraseña"
              class="w-full h-12 pl-10 pr-10 text-gray-200 placeholder-gray-300 border border-gray-400 rounded-md bg-white/10 focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-400"
              required
            />
            <span class="absolute text-xl -translate-y-1/2 right-3 top-1/2">
              <span v-if="passwordConfirm === ''"></span>
              <span
                v-else-if="password === passwordConfirm"
                class="text-green-400"
                >✓</span
              >
              <span v-else class="text-red-500">✗</span>
            </span>
          </div>

          <!-- Confirmar contraseña -->
          <div class="relative w-full">
            <font-awesome-icon
              icon="fa-solid fa-lock"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none"
            />
            <input
              type="password"
              v-model="passwordConfirm"
              placeholder="Repite la contraseña"
              class="w-full h-12 pl-10 pr-10 text-gray-200 placeholder-gray-300 border border-gray-400 rounded-md bg-white/10 focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-400"
              required
            />
            <span class="absolute text-xl -translate-y-1/2 right-3 top-1/2">
              <span v-if="passwordConfirm === ''"></span>
              <span
                v-else-if="password === passwordConfirm"
                class="text-green-400"
                >✓</span
              >
              <span v-else class="text-red-500">✗</span>
            </span>
            <!-- Mensaje de error en tiempo real -->
            <p
              v-if="passwordConfirm !== '' && password !== passwordConfirm"
              class="mt-1 text-sm text-red-500"
            >
              Las contraseñas no coinciden
            </p>
          </div>

          <!-- Botón -->
          <div class="relative w-full">
            <font-awesome-icon
              icon="fa-solid fa-key"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none"
            />
            <button
              type="submit"
              :disabled="!canSubmit"
              :class="[
                'w-full py-2 font-bold rounded-md border focus:outline-none focus:ring-2 transition-colors',
                canSubmit
                  ? 'bg-white/10 text-gray-200 border-gray-400 hover:bg-gray-400 hover:text-white'
                  : 'bg-gray-700/40 text-gray-400 border-gray-500 cursor-not-allowed',
              ]"
            >
              Cambiar contraseña
            </button>
          </div>

          <!-- Enlace a login -->
          <div class="flex justify-center w-full !mt-20">
            <NuxtLink
              to="/login"
              class="w-full py-2 text-sm italic text-center text-gray-200 transition-colors border border-gray-400 rounded-md bg-gray-500/40 hover:bg-gray-400 hover:text-white"
            >
              Volver al inicio de sesión
            </NuxtLink>
          </div>

          <!-- Mensajes backend -->
          <p v-if="message" class="font-semibold text-center text-green-400">
            {{ message }}
          </p>
          <p v-if="error" class="font-semibold text-center text-red-500">
            {{ error }}
          </p>
        </form>
      </div>

      <div class="flex-grow w-full"></div>

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
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { axiosClient } from "~/axiosConfig.js";

const mounted = ref(false);
const route = useRoute();
const token = route.params.token;

const password = ref("");
const passwordConfirm = ref("");
const message = ref("");
const error = ref("");

// Computada para habilitar el botón solo si las contraseñas coinciden
const canSubmit = computed(
  () => password.value !== "" && password.value === passwordConfirm.value
);

const submitPassword = async () => {
  message.value = "";
  error.value = "";

  try {
    const response = await axiosClient.post("cambiar-passwd", {
      token: token,
      password: password.value,
      password_confirmation: passwordConfirm.value,
    });

    if (response.data.success) {
      message.value = response.data.message;
      password.value = "";
      passwordConfirm.value = "";
    } else {
      error.value = response.data.message || "Error desconocido";
    }
  } catch (err) {
    error.value =
      err.response?.data?.message || "Error al conectar con el servidor";
  }
};

onMounted(() => {
  mounted.value = true;
});
</script>

<style scoped>
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-box-shadow: 0 0 0px 1000px transparent inset !important;
  -webkit-text-fill-color: #e5e7eb !important;
  transition: background-color 5000s ease-in-out 0s !important;
}
</style>
