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

      <!-- Subtítulo en su propio box (como Login y Password) -->
      <div class="w-full flex justify-center mt-10">
        <div class="w-full max-w-sm px-6 py-5 border border-white/15 rounded-2xl frost-card">
          <p class="mb-0 text-xl text-left text-gray-200 md:text-2xl">{{ t('register.subtitle') }}</p>
        </div>
      </div>

      <!-- Formulario -->
      <div class="w-full flex justify-center mt-6">
        <div class="w-full max-w-sm px-6 py-5 border border-white/15 rounded-2xl frost-card">
          <form @submit.prevent="submitRegister" class="flex flex-col w-full space-y-4">
          <!-- Nombre -->
          <div class="relative w-full">
            <font-awesome-icon icon="fa-solid fa-user"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
            <input id="name" v-model="name" type="text" required :placeholder="t('register.name')"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border rounded-md bg-transparent focus:outline-none focus:ring-2 frost-field" />
          </div>

          <!-- Correo -->
          <div class="relative w-full">
            <font-awesome-icon icon="fa-solid fa-envelope"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
            <input id="email" v-model="email" type="email" required :placeholder="t('register.email')"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border rounded-md bg-transparent focus:outline-none focus:ring-2 frost-field" />
          </div>

          <!-- Contraseña -->
          <div class="relative w-full">
            <font-awesome-icon icon="fa-solid fa-lock"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
            <input id="password" v-model="password" type="password" required :placeholder="t('register.password')"
              autocomplete="new-password"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border rounded-md bg-transparent focus:outline-none focus:ring-2 frost-field" />
          </div>

          <!-- Repetir contraseña -->
          <div class="relative w-full">
            <font-awesome-icon icon="fa-solid fa-unlock"
              class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
            <input id="confirmPassword" v-model="confirmPassword" type="password" required
              :placeholder="t('register.confirm_password')" autocomplete="new-password"
              class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 border rounded-md bg-transparent focus:outline-none focus:ring-2 frost-field" />
          </div>

          <!-- Error -->
          <p v-if="error" class="text-sm text-red-400">{{ error }}</p>

          <!-- Botón principal -->
          <div class="flex justify-center !mt-8">
            <button type="submit"
              class="w-1/2 py-2 font-bold text-gray-200 transition-colors rounded-md frost-field border border-white/40 hover:brightness-110">
              {{ t('register.submit') }}
            </button>
          </div>
          </form>
        </div>
      </div>

      <!-- Botón de acceso a login -->
      <div class="w-full flex justify-center !mt-20">
        <div class="w-full max-w-sm px-6 py-4 border border-white/15 rounded-2xl frost-card">
          <NuxtLink to="/login"
            class="w-full px-4 py-2 text-sm italic text-center text-gray-200 transition-colors hover:text-white flex items-center justify-center">
            {{ t('register.have_account') }} {{ t('register.signin_here') }}
          </NuxtLink>
        </div>
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

<style scoped>
.frost-card {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent));
  background-blend-mode: normal, normal;
  background-color: transparent;
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

@media (prefers-color-scheme: light) {
  .frost-card {
    background-image:
      linear-gradient(to bottom,
        color-mix(in srgb, white 18%, transparent),
        color-mix(in srgb, white 18%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-primary) 3%, transparent),
        color-mix(in srgb, var(--color-primary) 3%, transparent)),
      linear-gradient(to bottom,
        color-mix(in srgb, var(--color-bg) 12%, transparent),
        color-mix(in srgb, var(--color-bg) 12%, transparent));
  }
}

:deep(.frost-card),
:deep(.frost-card *),
:deep(.frost-card th),
:deep(.frost-card td) {
  color: #ffffff !important;
}

/* Glass sutil para inputs y botón dentro del box */
.frost-field {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 4%, transparent),
      color-mix(in srgb, var(--color-primary) 4%, transparent));
  background-color: color-mix(in srgb, var(--color-bg) 8%, transparent);
  border-color: color-mix(in srgb, white 35%, transparent);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}
</style>