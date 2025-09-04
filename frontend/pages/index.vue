<template>
  <div
    class="relative w-full h-screen"
    style="
      background-image: url('/img/menu.jpg');
      background-size: cover;
      background-position: center;
    "
  >
    <div class="absolute inset-0 bg-black/40"></div>

    <div
      class="relative z-10 flex flex-col items-center w-full h-full p-4 md:p-8"
    >
      <!-- Contenido principal -->
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

        <p
          class="flex items-center justify-center p-2 pt-1 space-x-3 text-xl text-gray-200 rounded-xl md:text-base bg-slate-700/80 hover:bg-slate-900"
        >
          <span
            class="flex items-center justify-center transition-transform duration-300 cursor-pointer ease-bounce hover:scale-150"
          >
            🌞
          </span>
          <span
            class="flex items-center justify-center transition-transform duration-300 cursor-pointer ease-bounce hover:scale-150"
          >
            ⛱️
          </span>
          <span
            class="flex items-center justify-center transition-transform duration-300 cursor-pointer ease-bounce hover:scale-150"
          >
            🚗
          </span>
          <span
            class="flex items-center justify-center transition-transform duration-300 cursor-pointer ease-bounce hover:scale-150"
          >
            ❄️
          </span>
          <span
            class="flex items-center justify-center transition-transform duration-300 cursor-pointer ease-bounce hover:scale-150"
          >
            🌾
          </span>
          <span
            class="flex items-center justify-center transition-transform duration-300 cursor-pointer ease-bounce hover:scale-150"
          >
            🐝
          </span>
        </p>
      </div>

      <div class="flex items-end justify-center w-2/3 h-3/10">
        <p class="font-medium text-white text-end">¿Ya tienes cuenta?</p>
      </div>

      <div class="flex items-start justify-center w-full mt-8 space-x-10 h-1/5">
        <div class="flex flex-col items-center">
          <NuxtLink
            to="/login"
            class="flex flex-col items-center justify-center w-20 text-base font-bold text-white transition-transform duration-300 transform rounded-full h-14 bg-slate-700 hover:bg-slate-900 hover:scale-125"
          >
            <span class="text-lg">SI</span>
            <span class="pb-1 text-xs font-thin text-gray-200">Login</span>
          </NuxtLink>
        </div>

        <div class="flex flex-col items-center">
          <NuxtLink
            to="/register"
            class="flex flex-col items-center justify-center w-20 text-base font-bold text-white transition-transform duration-300 transform rounded-full h-14 bg-slate-700 hover:bg-slate-900 hover:scale-125"
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
