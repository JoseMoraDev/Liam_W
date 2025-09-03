<template>
  <div class="max-w-md p-6 mx-auto mt-10 space-y-4 bg-gray-800 rounded-2xl">
    <h2 class="text-2xl font-bold text-white">Recuperar Contraseña</h2>
    <p class="text-gray-300">
      Introduce tu correo electrónico y recibirás un enlace para cambiar tu
      contraseña.
    </p>

    <form @submit.prevent="sendResetEmail" class="space-y-4">
      <div>
        <label class="block mb-1 text-gray-200" for="email"
          >Correo electrónico</label
        >
        <input
          id="email"
          type="email"
          v-model="email"
          required
          class="w-full p-3 text-white bg-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <button
        type="submit"
        class="w-full p-3 font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl"
      >
        Enviar enlace de recuperación
      </button>
    </form>

    <p class="text-sm text-gray-300">
      ¿No tienes cuenta?
      <NuxtLink to="/register" class="text-blue-400 hover:underline"
        >Crear cuenta</NuxtLink
      >
    </p>

    <!-- Mensajes simulados -->
    <p v-if="message" class="font-semibold text-green-400">{{ message }}</p>
    <p v-if="error" class="font-semibold text-red-500">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from "vue";

const email = ref("");
const message = ref("");
const error = ref("");

const sendResetEmail = () => {
  message.value = "";
  error.value = "";

  // Simulación de envío de correo
  if (email.value.includes("@")) {
    message.value = `Simulación: se ha enviado un enlace a ${email.value}.`;
  } else {
    error.value = "Por favor, introduce un correo válido.";
  }
};
</script>
