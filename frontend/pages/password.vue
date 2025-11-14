<template>
  <div class="absolute inset-0 w-full h-screen bg-center bg-cover" style="background-image: url('/img/menu.jpg')">
    <div class="absolute inset-0 bg-black/40"></div>

    <!-- Contenido principal -->
    <div :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full h-screen p-4 transition-opacity duration-300 md:p-8">
      <!-- Título -->
      <div class="flex items-end justify-center w-3/4 h-2/10">
        <h1 class="text-3xl font-bold leading-snug text-center text-white md:text-4xl">
          Live Ambience <br />Weather & Traffic
        </h1>
      </div>

      <!-- sección (subtítulo dentro de box glass) -->
      <div class="flex justify-center w-full mt-10">
        <div class="w-full max-w-sm px-6 py-5 border border-white/15 rounded-2xl frost-card">
          <p class="mb-0 text-xl text-left text-gray-200 md:text-2xl">
            {{ t('password.subtitle') }}
          </p>
        </div>
      </div>

      <!-- formulario -->
      <div class="flex justify-center w-full mt-6">
        <div class="w-full max-w-sm px-6 py-4 border border-white/15 rounded-2xl frost-card">
          <form @submit.prevent="sendResetEmail" class="flex flex-col w-full space-y-4">
            <!-- Correo -->
            <div class="relative w-full">
              <font-awesome-icon icon="fa-regular fa-envelope"
                class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
              <input id="email" type="email" v-model="email" required :placeholder="t('password.placeholder_email')"
                class="w-full h-12 pl-10 pr-3 text-gray-200 placeholder-gray-300 bg-transparent border rounded-md focus:outline-none focus:ring-2 frost-field" />
            </div>

            <!-- Botón principal -->
            <div class="relative w-full">
              <font-awesome-icon icon="fa-solid fa-paper-plane"
                class="absolute left-3 top-1/2 -translate-y-[6px] text-gray-200 pointer-events-none" />
              <button type="submit"
                class="w-full py-2 pl-10 font-bold text-left text-gray-200 border rounded-md frost-field border-white/40 hover:brightness-110">
                {{ t('password.submit') }}
              </button>
            </div>
            <div class="mb-12"></div>
            <!-- Enlace a registro -->
            <div class="flex justify-center w-full mt-12">
              <div class="w-full max-w-sm px-6 py-4 border border-white/15 rounded-2xl frost-card">
                <NuxtLink to="/register"
                  class="flex items-center justify-center w-full px-4 py-2 text-sm italic text-center text-gray-200 transition-colors hover:text-white">
                  {{ t('password.no_account') }} {{ t('password.create_account') }}
                </NuxtLink>
              </div>
            </div>

            <!-- Mensajes -->
            <p v-if="message" class="font-semibold text-center text-green-400">
              {{ message }}
            </p>
            <p v-if="error" class="font-semibold text-center text-red-500">
              {{ error }}
            </p>
          </form>
        </div>
      </div>

      <!-- Spacer flexible -->
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
import { axiosClient } from "~/axiosConfig";
import { useI18n } from 'vue-i18n'

const mounted = ref(false);
const email = ref("");
const message = ref("");
const error = ref("");
const { t } = useI18n()

const sendResetEmail = async () => {
  message.value = "";
  error.value = "";

  if (!email.value.includes("@")) {
    error.value = t('password.invalid_email');
    return;
  }

  try {
    const response = await axiosClient.post(
      "/recuperar-passwd",
      { email: email.value }
    );

    if (response.data.success) {
      message.value = t('password.sent', { email: email.value });
    } else {
      error.value = t('password.error_send');
    }
  } catch (err) {
    error.value = t('password.error_server');
  }
};

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
