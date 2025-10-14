<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

// Estado reactivo
const usuario = ref(null);
const cargando = ref(true);

// Cargar datos del usuario desde el endpoint /me
onMounted(async () => {
  try {
    const token = localStorage.getItem("token"); // asumimos que el token ya está guardado
    const response = await axios.get("http://localhost:8000/api/me", {
      headers: { Authorization: `Bearer ${token}` },
    });
    usuario.value = response.data;
  } catch (error) {
    console.error("Error al cargar datos del usuario:", error);
  } finally {
    cargando.value = false;
  }
});
</script>

<!-- 

API ME LO ESTA TOMANDO COMO UNA WEB - DEBE SER LA RESPUESTA DE LA API PARA VALIDAR EL USER

EL USER ESTA PETANDO POR LA WEB, ALGUNOS SITIOS VA, OTROS NO

NO DEBERIA SER MUY COMPLICADO HACERLO FUNCIONAR EN EL GLASSNAV PORQUE SOLO FALTA ESTO, LO DEMÁS YA ESTÁ YENDO

-->

<template>
  <div class="min-h-screen p-6 text-gray-200 bg-gray-900">
    <h1 class="mb-8 text-3xl font-bold text-center md:text-4xl">
      👤 Perfil de Usuario
    </h1>

    <div v-if="cargando" class="text-lg text-center">
      Cargando información del usuario...
    </div>

    <div v-else-if="!usuario" class="text-lg text-center text-red-400">
      No se pudo cargar la información del usuario.
    </div>

    <div v-else class="flex flex-col items-center space-y-8">
      <!-- Bloque superior con avatar y nombre -->
      <div class="flex flex-col items-center space-y-2">
        <div
          class="flex items-center justify-center text-5xl font-bold text-gray-200 bg-gray-700 rounded-full w-28 h-28 md:w-32 md:h-32 md:text-6xl"
        >
          {{ usuario.name?.charAt(0).toUpperCase() || "U" }}
        </div>
        <h2 class="text-2xl font-bold md:text-3xl">{{ usuario.name }}</h2>
        <p class="text-gray-400">{{ usuario.email }}</p>
      </div>

      <!-- Tabla con todos los campos -->
      <div class="w-full max-w-4xl overflow-x-auto">
        <table class="min-w-full border-collapse">
          <thead>
            <tr class="text-gray-300 bg-gray-800">
              <th class="p-3 text-left">Campo</th>
              <th class="p-3 text-left">Valor</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">ID</td>
              <td class="p-2">{{ usuario.id }}</td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Nombre</td>
              <td class="p-2">{{ usuario.name }}</td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Correo</td>
              <td class="p-2">{{ usuario.email }}</td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Usuario</td>
              <td class="p-2">{{ usuario.username || "—" }}</td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Email verificado</td>
              <td class="p-2">{{ usuario.email_verified_at ? "Sí" : "No" }}</td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Fecha de creación</td>
              <td class="p-2">
                {{ new Date(usuario.created_at).toLocaleString() }}
              </td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Última actualización</td>
              <td class="p-2">
                {{ new Date(usuario.updated_at).toLocaleString() }}
              </td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Font ID</td>
              <td class="p-2">{{ usuario.font_id }}</td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Color ID</td>
              <td class="p-2">{{ usuario.color_id }}</td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Última ubicación (lat,lng)</td>
              <td class="p-2">{{ usuario.last_location_latlon || "—" }}</td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">Última ciudad</td>
              <td class="p-2">{{ usuario.last_location_city || "—" }}</td>
            </tr>
            <tr class="border-b border-gray-700 hover:bg-gray-800/50">
              <td class="p-2 font-semibold">
                Última actualización de ubicación
              </td>
              <td class="p-2">
                {{
                  usuario.last_location_updated_at
                    ? new Date(
                        usuario.last_location_updated_at
                      ).toLocaleString()
                    : "—"
                }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Botón de volver / dashboard -->
      <div class="mt-8">
        <NuxtLink
          to="/"
          class="px-6 py-2 font-semibold text-gray-200 transition-colors border border-gray-400 rounded-md bg-gray-700/40 hover:bg-gray-600 hover:text-white"
        >
          Volver al inicio
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Forzar tabla a ocupar todo el ancho disponible y mantener estilo oscuro */
table {
  border-spacing: 0;
}
</style>
