<template>
  <div class="absolute inset-0 w-full h-screen bg-center bg-cover" style="background-image: url('/img/menu.jpg')">
    <div class="absolute inset-0 bg-black/40"></div>

    <div :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full h-screen p-4 transition-opacity duration-300 md:p-8">
      <!-- Título -->
      <div class="flex items-end justify-center w-3/4 h-2/10">
        <h1 class="text-3xl font-bold leading-snug text-center text-white md:text-4xl">
          Live Ambience <br />Weather & Traffic
        </h1>
      </div>

      <!-- Subtítulo -->
      <div class="flex flex-col items-center justify-center w-2/3 mt-10 space-y-6 h-1/10">
        <p class="mt-2 text-xl text-center text-gray-200 md:text-2xl">{{ t('register.subtitle') }}</p>
      </div>

      <!-- Formulario -->
      <div class="flex items-start justify-center w-10/12 mt-4 h-4/10">
        <form @submit.prevent="submitRegister" class="flex flex-col w-full max-w-sm space-y-4">
          <!-- Nombre -->
          <div class="relative w-full">
            <font-awesome-icon icon="fa-solid fa-user"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
            <input id="name" v-model="name" type="text" required :placeholder="t('register.name')"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border border-gray-400 rounded-md bg-white/10 focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-400" />
          </div>

          <!-- Correo -->
          <div class="relative w-full">
            <font-awesome-icon icon="fa-solid fa-envelope"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
            <input id="email" v-model="email" type="email" required :placeholder="t('register.email')"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border border-gray-400 rounded-md bg-white/10 focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-400" />
          </div>

          <!-- Contraseña -->
          <div class="relative w-full">
            <font-awesome-icon icon="fa-solid fa-lock"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
            <input id="password" v-model="password" type="password" required :placeholder="t('register.password')"
              autocomplete="new-password"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border border-gray-400 rounded-md bg-white/10 focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-400" />
          </div>

          <!-- Repetir contraseña -->
          <div class="relative w-full">
            <font-awesome-icon icon="fa-solid fa-unlock"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
            <input id="confirmPassword" v-model="confirmPassword" type="password" required
              :placeholder="t('register.confirm_password')" autocomplete="new-password"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border border-gray-400 rounded-md bg-white/10 focus:outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-400" />
          </div>

          <!-- Error -->
          <p v-if="error" class="text-sm text-red-400">{{ error }}</p>

          <!-- Botón principal -->
          <div class="flex justify-center !mt-8">
            <button type="submit"
              class="w-1/2 py-2 font-bold text-gray-200 transition-colors border border-gray-400 rounded-md bg-white/40 hover:bg-gray-400 hover:text-white">
              {{ t('register.submit') }}
            </button>
          </div>
        </form>
      </div>

      <!-- Botón de acceso a login -->
      <div class="flex justify-center w-full !mt-20">
        <NuxtLink to="/login"
          class="w-full py-2 text-sm italic text-center text-gray-200 transition-colors border border-gray-400 rounded-md bg-gray-500/40 hover:bg-gray-400 hover:text-white">
          {{ t('register.have_account') }} <br />{{ t('register.signin_here') }}
        </NuxtLink>
      </div>

      <div class="flex-grow w-full"></div>

      <!-- Footer -->
      <footer class="absolute w-full text-xs text-center text-gray-600 bottom-2">
        {{ t('home.photo_credit') }}
        <a href="https://www.freepik.com/author/danieljschwarz" target="_blank"
          class="underline hover:text-white">www.freepik.com</a>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { axiosClient } from "~/axiosConfig";
import { useI18n } from 'vue-i18n'

const mounted = ref(false);
const router = useRouter();

const name = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");
const error = ref("");
const { t } = useI18n()

async function submitRegister() {
  error.value = "";

  if (password.value !== confirmPassword.value) {
    error.value = t('register.mismatch_error');
    return;
  }

  try {
    const res = await axiosClient.post("/register", {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
    });

    // console.log("✅ Registro exitoso:", res.data); 

    // Redirigir al login después del registro
    router.push("/login");
  } catch (err) {
    console.error("❌ Error en el registro:", err);
    error.value =
      err.response?.data?.message || t('register.generic_error');
  }
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
  -webkit-text-fill-color: #e5e7eb !important;
  transition: background-color 5000s ease-in-out 0s !important;
}
</style>