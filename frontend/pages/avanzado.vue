<template>
  <div class="w-full min-h-screen bg-center bg-cover" style="background-image: url('/img/menu.jpg')">
    <!-- Capa translúcida -->
    <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>

    <!-- Contenido -->
    <div :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 overflow-y-auto transition-opacity duration-300 md:p-8">
      <!-- Título principal -->
      <div class="w-full max-w-3xl p-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md">
        <h1 class="text-2xl font-bold text-center text-gray-900/90">Ajustes</h1>
      </div>

      <!-- Ubicaciones -->
      <div class="w-full max-w-3xl p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md">
        <h2 class="text-xl font-bold text-center text-gray-900/90">Ubicaciones</h2>
        <div class="grid grid-cols-2 gap-4">
          <NuxtLink to="/ajustes/ubicaciones/predeterminadas" class="sub-card">Predeterminadas</NuxtLink>
          <NuxtLink to="/ajustes/ubicaciones/favoritas" class="sub-card">Favoritas</NuxtLink>
        </div>
      </div>

      <!-- Alertas -->
      <div class="w-full max-w-3xl p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md">
        <h2 class="text-xl font-bold text-center text-gray-900/90">Alertas</h2>
        <div class="grid grid-cols-1 gap-4">
          <NuxtLink to="/ajustes/alertas" class="sub-card">Configurar alertas personalizadas</NuxtLink>
        </div>
      </div>

      <!-- Interfaz -->
      <div class="w-full max-w-3xl p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md">
        <h2 class="text-xl font-bold text-center text-gray-900/90">Interfaz</h2>
        <div class="grid grid-cols-1 gap-4">
          <NuxtLink to="/ajustes/interfaz/tema" class="sub-card">Tema de colores</NuxtLink>
        </div>
      </div>

      <!-- Administración (solo admin) -->
      <template v-if="isAdmin">
        <div class="w-full max-w-3xl p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md">
          <h2 class="text-xl font-bold text-center text-gray-900/90">Administración de usuarios</h2>
          <div class="grid grid-cols-1 gap-4">
            <button @click="adminOpen = true" class="sub-card">CRUD de usuarios con bloqueos, roles, y cupos de
              llamadas</button>
          </div>
        </div>

        <!-- Modal de gestión de usuarios -->
        <transition name="fade">
          <div v-if="adminOpen" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50"
            @click.self="adminOpen = false">
            <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto p-4 bg-white rounded-2xl shadow-xl">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-xl font-bold text-gray-900/90">{{ t('admin.users.title') }}</h3>
                <button @click="adminOpen = false"
                  class="px-3 py-1 text-gray-700 bg-gray-100 border rounded-md">{{ t('admin.users.close') }}</button>
              </div>
              <!-- Gestión de usuarios -->
              <div class="w-full p-0 space-y-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                  <h2 class="text-lg font-semibold text-gray-900/90">{{ t('admin.users.title') }}</h2>
                  <div class="flex flex-col w-full gap-2 sm:flex-row sm:w-auto sm:items-center">
                    <input v-model="search" @input="onSearchInput" type="text" :placeholder="t('admin.users.search_placeholder')"
                      class="w-full h-10 px-3 text-gray-800 placeholder-gray-500 border rounded-lg sm:w-72 border-gray-300/60 bg-white/70 focus:outline-none focus:ring-2 focus:ring-gray-300/50" />
                    <div class="flex gap-2">
                      <button @click="clearSearch"
                        class="h-10 px-3 font-semibold text-gray-800 border rounded-lg border-gray-300/60 bg-white/70 hover:bg-white">{{ t('admin.users.clear') }}</button>
                      <button @click="openCreate()"
                        class="h-10 px-3 font-semibold text-gray-800 border rounded-lg border-gray-300/60 bg-white/70 hover:bg-white">{{ t('admin.users.create') }}</button>
                    </div>
                  </div>
                </div>

                <!-- Tabla desktop -->
                <div class="hidden overflow-x-auto bg-white shadow md:block rounded-xl">
                  <table class="w-full text-sm">
                    <thead class="text-left text-gray-600 bg-gray-50">
                      <tr>
                        <th class="px-3 py-2">{{ t('admin.users.id') }}</th>
                        <th class="px-3 py-2">{{ t('admin.users.name') }}</th>
                        <th class="px-3 py-2">{{ t('admin.users.email') }}</th>
                        <th class="px-3 py-2">{{ t('admin.users.role') }}</th>
                        <th class="px-3 py-2">{{ t('admin.users.quota') }}</th>
                        <th class="px-3 py-2">{{ t('admin.users.blocked') }}</th>
                        <th class="px-3 py-2 text-right">{{ t('admin.users.actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="u in filteredUsers" :key="u.id" class="border-t">
                        <td class="px-3 py-2">{{ u.id }}</td>
                        <td class="px-3 py-2">
                          <input v-model="u.name" @change="saveUser(u)"
                            class="w-full max-w-[220px] px-2 py-1 border rounded-md border-gray-300/60" />
                        </td>
                        <td class="px-3 py-2">
                          <input v-model="u.email" disabled
                            class="w-full max-w-[260px] px-2 py-1 border rounded-md border-gray-300/60 opacity-75 cursor-not-allowed" />
                        </td>
                        <td class="px-3 py-2">
                          <select v-model="u.role" @change="setRole(u)"
                            class="px-2 py-1 border rounded-md border-gray-300/60">
                            <option value="free">{{ t('admin.users.role_free') }}</option>
                            <option value="premium">{{ t('admin.users.role_premium') }}</option>
                            <option value="admin">{{ t('admin.users.role_admin') }}</option>
                          </select>
                        </td>
                        <td class="px-3 py-2">
                          <input type="number" min="0" v-model.number="u.free_daily_quota" :disabled="u.role !== 'free'"
                            class="w-24 px-2 py-1 border rounded-md border-gray-300/60 disabled:opacity-50" />
                        </td>
                        <td class="px-3 py-2">
                          <label class="inline-flex items-center gap-2">
                            <input type="checkbox" v-model="u.is_blocked" @change="toggleBlock(u)" />
                            <span>{{ u.is_blocked ? t('admin.users.yes') : t('admin.users.no') }}</span>
                          </label>
                        </td>
                        <td class="px-3 py-2 text-right">
                          <button @click="saveUser(u)"
                            class="px-2 py-1 mr-2 text-gray-800 bg-gray-100 border rounded-md hover:bg-gray-200">{{ t('admin.users.save') }}</button>
                          <button @click="removeUser(u)"
                            class="px-2 py-1 text-red-700 bg-red-100 border border-red-200 rounded-md hover:bg-red-200">{{ t('admin.users.delete') }}</button>
                        </td>
                      </tr>
                      <tr v-if="!filteredUsers.length">
                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">{{ t('admin.users.no_results') }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Tarjetas móvil -->
                <div class="grid gap-3 md:hidden">
                  <div v-for="u in filteredUsers" :key="u.id" class="p-3 bg-white shadow rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                      <div class="text-xs text-gray-500">{{ t('admin.users.id') }} {{ u.id }}</div>
                      <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" v-model="u.is_blocked" @change="toggleBlock(u)" />
                        <span>{{ u.is_blocked ? t('admin.users.blocked') : t('admin.users.active') }}</span>
                      </label>
                    </div>
                    <div class="flex flex-col gap-2">
                      <input v-model="u.name" @change="saveUser(u)"
                        class="w-full h-10 px-3 border rounded-lg border-gray-300/60" :placeholder="t('admin.users.name')" />
                      <input v-model="u.email" disabled
                        class="w-full h-10 px-3 border rounded-lg opacity-75 cursor-not-allowed border-gray-300/60"
                        :placeholder="t('admin.users.email')" />
                      <div class="flex items-center gap-2">
                        <select v-model="u.role" @change="setRole(u)"
                          class="flex-1 h-10 px-3 border rounded-lg border-gray-300/60">
                          <option value="free">{{ t('admin.users.role_free') }}</option>
                          <option value="premium">{{ t('admin.users.role_premium') }}</option>
                          <option value="admin">{{ t('admin.users.role_admin') }}</option>
                        </select>
                        <input type="number" min="0" v-model.number="u.free_daily_quota" :disabled="u.role !== 'free'"
                          class="h-10 px-2 border rounded-lg w-28 border-gray-300/60 disabled:opacity-50"
                          :placeholder="t('admin.users.quota')" />
                        <button @click="removeUser(u)"
                          class="px-3 py-2 text-red-700 bg-red-100 border border-red-200 rounded-md">{{ t('admin.users.delete') }}</button>
                      </div>
                    </div>
                  </div>
                  <div v-if="!filteredUsers.length" class="p-3 text-center text-gray-500">{{ t('admin.users.no_results') }}</div>
                </div>
              </div>

              <!-- Modal crear/editar -->
              <transition name="fade">
                <div v-if="dialog.open" class="fixed inset-0 z-[11000] flex items-center justify-center bg-black/50"
                  @click.self="closeDialog">
                  <div class="w-full max-w-md p-4 bg-white shadow-xl rounded-2xl">
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">{{ dialog.mode === 'create' ? t('admin.users.modal_create') :
                      t('admin.users.modal_edit') }}</h3>
                    <form @submit.prevent="submitDialog" class="space-y-3">
                      <input v-model="dialog.form.name" type="text" :placeholder="t('admin.users.name')"
                        class="w-full h-10 px-3 border rounded-lg border-gray-300/60" required />
                      <input v-model="dialog.form.email" type="email" :placeholder="t('admin.users.email')"
                        class="w-full h-10 px-3 border rounded-lg border-gray-300/60" :disabled="dialog.mode === 'edit'"
                        :required="dialog.mode === 'create'" />
                      <input v-if="dialog.mode === 'create' || dialog.form.password !== undefined"
                        v-model="dialog.form.password" type="password" :placeholder="t('admin.users.password')"
                        class="w-full h-10 px-3 border rounded-lg border-gray-300/60"
                        :required="dialog.mode === 'create'" minlength="8" />
                      <div class="flex gap-2">
                        <select v-model="dialog.form.role"
                          class="flex-1 h-10 px-3 border rounded-lg border-gray-300/60">
                          <option value="free">{{ t('admin.users.role_free') }}</option>
                          <option value="premium">{{ t('admin.users.role_premium') }}</option>
                          <option value="admin">{{ t('admin.users.role_admin') }}</option>
                        </select>
                        <label class="flex items-center gap-2">
                          <input type="checkbox" v-model="dialog.form.is_blocked" />
                          <span>{{ t('admin.users.blocked') }}</span>
                        </label>
                      </div>
                      <div class="flex justify-end gap-2">
                        <button type="button" @click="closeDialog"
                          class="px-3 py-2 text-gray-800 bg-gray-100 border rounded-md">{{ t('admin.users.cancel') }}</button>
                        <button type="submit"
                          class="px-3 py-2 text-gray-100 bg-gray-800 border rounded-md">{{ t('admin.users.save') }}</button>
                      </div>
                    </form>
                  </div>
                </div>
              </transition>
            </div>
          </div>
        </transition>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { navigateTo } from '#app'
import { axiosClient } from '~/axiosConfig'
import { useI18n } from 'vue-i18n'

const mounted = ref(false)
const isAdmin = ref(false)
const adminOpen = ref(false)
const allUsers = ref([])
const search = ref('')
const { t } = useI18n()
const filteredUsers = computed(() => {
  const q = (search.value || '').toLowerCase().trim()
  if (!q) return allUsers.value
  return allUsers.value.filter(u =>
    String(u.name || '').toLowerCase().includes(q) ||
    String(u.email || '').toLowerCase().includes(q)
  )
})

const dialog = ref({ open: false, mode: 'create', form: { name: '', email: '', password: '', role: 'free', is_blocked: false } })

function openCreate() {
  dialog.value = { open: true, mode: 'create', form: { name: '', email: '', password: '', role: 'free', is_blocked: false } }
}
function openEdit(u) {
  dialog.value = { open: true, mode: 'edit', form: { id: u.id, name: u.name, email: u.email, password: '', role: u.role, is_blocked: !!u.is_blocked } }
}
function closeDialog() { dialog.value.open = false }

async function submitDialog() {
  try {
    if (dialog.value.mode === 'create') {
      const { data } = await axiosClient.post('/admin/users', dialog.value.form)
      allUsers.value.unshift(data)
    } else {
      const form = { ...dialog.value.form }
      if (!form.password) delete form.password
      const { data } = await axiosClient.put(`/admin/users/${form.id}`, form)
      const idx = allUsers.value.findIndex(x => x.id === data.id)
      if (idx >= 0) allUsers.value[idx] = data
    }
    closeDialog()
  } catch (e) { console.error(e) }
}

async function fetchUsers() {
  try {
    const { data } = await axiosClient.get('/admin/users', { params: { per_page: 1000 } })
    // Soportar respuesta paginada
    allUsers.value = (data.data && Array.isArray(data.data)) ? data.data : (Array.isArray(data) ? data : [])
  } catch (e) { console.error(e) }
}

// Búsqueda en vivo (filtrado local)
function onSearchInput() { /* filtrado reactivo vía computed */ }
function clearSearch() {
  search.value = ''
}

async function saveUser(u) {
  try {
    const payload = {}
    const name = (u.name || '').trim()
    const email = (u.email || '').trim()
    if (name.length > 0) payload.name = name
    if (email.length > 0 && email.includes('@')) payload.email = email
    if (u.role === 'free' && typeof u.free_daily_quota === 'number' && u.free_daily_quota >= 0) {
      payload.free_daily_quota = Math.floor(u.free_daily_quota)
    }
    if (Object.keys(payload).length === 0) return
    const { data } = await axiosClient.put(`/admin/users/${u.id}`, payload)
    Object.assign(u, data)
  } catch (e) { console.error(e) }
}
async function setRole(u) {
  try {
    const { data } = await axiosClient.post(`/admin/users/${u.id}/role`, { role: u.role })
    Object.assign(u, data)
  } catch (e) { console.error(e) }
}
async function toggleBlock(u) {
  try {
    const { data } = await axiosClient.post(`/admin/users/${u.id}/block`, { blocked: !!u.is_blocked })
    Object.assign(u, data)
  } catch (e) { console.error(e) }
}
async function removeUser(u) {
  try {
    await axiosClient.delete(`/admin/users/${u.id}`)
    allUsers.value = allUsers.value.filter(x => x.id !== u.id)
  } catch (e) { console.error(e) }
}

onMounted(async () => {
  try {
    const me = await axiosClient.get('/me')
    isAdmin.value = (me.data?.role || me.data?.user?.role) === 'admin'
    if (!isAdmin.value) {
      // Si no es admin, redirigir a inicio
      return navigateTo('/')
    }
  } catch {
    return navigateTo('/login')
  }
  await fetchUsers()
  mounted.value = true
})
</script>

<style scoped>
.sub-card {
  @apply flex items-center justify-center p-4 text-sm font-semibold text-center text-gray-900/90 transition-colors rounded-xl bg-gray-100/20 backdrop-blur-md hover:bg-gray-200/30 shadow-inner;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity .18s ease
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0
}
</style>
