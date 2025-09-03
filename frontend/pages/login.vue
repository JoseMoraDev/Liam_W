<template>
  <div class="login">
    <h1>Iniciar sesión</h1>
    <form @submit.prevent="submitLogin">
      <div>
        <label for="email">Correo</label>
        <input id="email" v-model="email" type="email" required />
      </div>

      <div>
        <label for="password">Contraseña</label>
        <input id="password" v-model="password" type="password" required />
      </div>

      <button type="submit">Entrar</button>
    </form>

    <p class="text-sm text-gray-300">
      He olvidado mi contraseña.
      <NuxtLink to="/forgotpassword" class="text-blue-400 hover:underline">
        Recuperar contraseña
      </NuxtLink>
    </p>

    <p>
      ¿No tienes cuenta?
      <NuxtLink to="/register">Crear cuenta</NuxtLink>
    </p>
  </div>
</template>

<script setup>
import { ref } from "vue";

const email = ref("");
const password = ref("");

function submitLogin() {
  console.log("Login con:", email.value, password.value);
  // Aquí harías tu llamada a la API de backend con fetch/axios
}
</script>
