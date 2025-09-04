<template>
  <div
    class="relative w-full h-screen bg-green-500"
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
      <div class="flex items-end justify-center w-3/4 h-2/10">
        <h1
          class="text-3xl font-bold leading-snug text-center text-white md:text-4xl"
        >
          Live Ambience <br />Weather & Traffic
        </h1>
      </div>

      <div class="flex items-end justify-center w-2/3 h-2/10">
        <p class="text-sm text-center text-gray-200 md:text-base">
          Haz tu día más claro y tus viajes más fáciles 🌍
        </p>
      </div>

      <div class="flex items-end justify-center w-2/3 h-2/10">
        <p class="font-medium text-center text-white">¿Ya tienes cuenta?</p>
      </div>

      <div class="flex items-center justify-center w-full space-x-10 h-1/5">
        <div class="flex flex-col items-center">
          <button
            class="flex items-center justify-center text-base font-bold text-white bg-green-500 rounded-full w-14 h-14 hover:bg-green-600"
          >
            SI
          </button>
          <span class="mt-1 text-xs text-gray-200">Login</span>
        </div>

        <div class="flex flex-col items-center">
          <button
            class="flex items-center justify-center text-base font-bold text-white bg-green-700 rounded-full w-14 h-14 hover:bg-green-800"
          >
            NO
          </button>
          <span class="mt-1 text-xs text-gray-200">Sign up</span>
        </div>
      </div>

      <div class="flex-grow w-full"></div>
    </div>
  </div>
</template>
