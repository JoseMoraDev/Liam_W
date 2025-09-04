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

      <div class="flex items-start justify-center w-2/3 mt-10 h-3/10">
        <p class="text-xl text-center text-gray-200 md:text-base">
          Haz tu día más claro y tus viajes más fáciles 🌍
        </p>
      </div>

      <div class="flex items-end justify-center w-2/3 h-2/10">
        <p class="font-medium text-white text-end">¿Ya tienes cuenta?</p>
      </div>

      <div class="flex items-start justify-center w-full mt-8 space-x-10 h-1/5">
        <div class="flex flex-col items-center">
          <button
            class="flex flex-col items-center justify-center w-20 text-base font-bold text-white rounded-full bg-amber-600 h-14 hover:bg-green-800"
          >
            <span class="text-lg">SI</span>
            <span class="pb-1 text-xs font-thin text-gray-200">Login</span>
          </button>
        </div>

        <div class="flex flex-col items-center">
          <button
            class="flex flex-col items-center justify-center w-20 text-base font-bold text-white rounded-full bg-amber-600 h-14 hover:bg-green-800"
          >
            <span class="text-lg">NO</span>
            <span class="pb-1 text-xs font-thin text-gray-200">Sign up</span>
          </button>
        </div>
      </div>

      <div class="flex-grow w-full"></div>
    </div>
  </div>
</template>
