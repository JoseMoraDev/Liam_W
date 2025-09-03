<template>
  <div class="register">
    <h1>Crear cuenta</h1>
    <form @submit.prevent="submitRegister">
      <div>
        <label for="name">Nombre</label>
        <input id="name" v-model="name" type="text" required />
      </div>

      <div>
        <label for="email">Correo</label>
        <input id="email" v-model="email" type="email" required />
      </div>

      <div>
        <label for="password">Contraseña</label>
        <input id="password" v-model="password" type="password" required />
      </div>

      <button type="submit">Registrarse</button>
    </form>

    <p>
      ¿Ya tienes cuenta?
      <NuxtLink to="/login">Iniciar sesión</NuxtLink>
    </p>
  </div>
</template>

<script setup>
import { ref } from "vue";

const name = ref("");
const email = ref("");
const password = ref("");

function submitRegister() {
  console.log("Registro con:", name.value, email.value, password.value);
  // Aquí harías la llamada a tu API de backend
}
</script>
