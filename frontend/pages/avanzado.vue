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

      <!-- Administración (solo admin) -->
      <template v-if="isAdmin">
        <div class="w-full max-w-3xl p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md">
          <h2 class="text-xl font-bold text-center text-gray-900/90">Administración de usuarios</h2>
          <div class="grid grid-cols-1 gap-4">
            <button @click="adminOpen = true" class="sub-card">CRUD de usuarios con bloqueos, roles, y cupos de
              llamadas</button>
          </div>
        </div>

        <!-- Métricas y logs -->
        <div class="w-full max-w-3xl p-4 space-y-4 shadow-inner rounded-2xl bg-gray-200/20 backdrop-blur-md">
          <h2 class="text-xl font-bold text-center text-gray-900/90">{{ t('admin.metrics.section_title') }}</h2>
          <div class="grid grid-cols-1 gap-3">
            <button @click="openLogins" class="w-full sub-card">{{ t('admin.metrics.open_logins') }}</button>
            <button @click="openErrors" class="w-full sub-card">{{ t('admin.metrics.open_errors') }}</button>
            <button @click="openStats" class="w-full sub-card">{{ t('admin.metrics.open_stats') }}</button>
          </div>
        </div>

        <!-- Modal de gestión de usuarios -->
        <transition name="fade">
          <div v-if="adminOpen" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50"
            @click.self="adminOpen = false">
            <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto p-4 bg-white rounded-2xl shadow-xl">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-xl font-bold text-gray-900/90">{{ t('admin.users.title') }}</h3>
                <button @click="adminOpen = false" class="px-3 py-1 text-gray-700 bg-gray-100 border rounded-md">{{
                  t('admin.users.close') }}</button>
              </div>
              <!-- Gestión de usuarios -->
              <div class="w-full p-0 space-y-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                  <h2 class="text-lg font-semibold text-gray-900/90">{{ t('admin.users.title') }}</h2>
                  <div class="flex flex-col w-full gap-2 sm:flex-row sm:w-auto sm:items-center">
                    <input v-model="search" @input="onSearchInput" type="text"
                      :placeholder="t('admin.users.search_placeholder')"
                      class="w-full h-10 px-3 text-gray-800 placeholder-gray-500 border rounded-lg sm:w-72 border-gray-300/60 bg-white/70 focus:outline-none focus:ring-2 focus:ring-gray-300/50" />
                    <div class="flex gap-2">
                      <button @click="clearSearch"
                        class="h-10 px-3 font-semibold text-gray-800 border rounded-lg border-gray-300/60 bg-white/70 hover:bg-white">{{
                          t('admin.users.clear') }}</button>
                      <button @click="openCreate()"
                        class="h-10 px-3 font-semibold text-gray-800 border rounded-lg border-gray-300/60 bg-white/70 hover:bg-white">{{
                          t('admin.users.create') }}</button>
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
                            class="px-2 py-1 mr-2 text-gray-800 bg-gray-100 border rounded-md hover:bg-gray-200">{{
                              t('admin.users.save') }}</button>
                          <button @click="removeUser(u)"
                            class="px-2 py-1 text-red-700 bg-red-100 border border-red-200 rounded-md hover:bg-red-200">{{
                              t('admin.users.delete') }}</button>
                        </td>
                      </tr>
                      <tr v-if="!filteredUsers.length">
                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">{{ t('admin.users.no_results') }}
                        </td>
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
                        class="w-full h-10 px-3 border rounded-lg border-gray-300/60"
                        :placeholder="t('admin.users.name')" />
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
                          class="px-3 py-2 text-red-700 bg-red-100 border border-red-200 rounded-md">{{
                            t('admin.users.delete') }}</button>
                      </div>
                    </div>
                  </div>
                  <div v-if="!filteredUsers.length" class="p-3 text-center text-gray-500">{{ t('admin.users.no_results')
                  }}</div>
                </div>
              </div>

              <!-- Modal crear/editar -->
              <transition name="fade">
                <div v-if="dialog.open" class="fixed inset-0 z-[11000] flex items-center justify-center bg-black/50"
                  @click.self="closeDialog">
                  <div class="w-full max-w-md p-4 bg-white shadow-xl rounded-2xl">
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">{{ dialog.mode === 'create' ?
                      t('admin.users.modal_create') :
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
                          class="px-3 py-2 text-gray-800 bg-gray-100 border rounded-md">{{ t('admin.users.cancel')
                          }}</button>
                        <button type="submit" class="px-3 py-2 text-gray-100 bg-gray-800 border rounded-md">{{
                          t('admin.users.save') }}</button>
                      </div>
                    </form>
                  </div>
                </div>
              </transition>

            </div>
          </div>
        </transition>

        <!-- Modal: Historial de logins -->
        <transition name="fade">
          <div v-if="showLogins" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50" @click.self="showLogins = false">
            <div class="w-full max-w-3xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded-2xl shadow-xl">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-xl font-bold text-gray-900/90">{{ t('admin.metrics.logins_title') }}</h3>
                <button @click="showLogins = false" class="px-3 py-1 text-gray-700 bg-gray-100 border rounded-md">{{ t('admin.metrics.close') }}</button>
              </div>
              <ul class="space-y-2 max-h-[70vh] overflow-y-auto pr-1">
                <li v-for="(item, idx) in loginHistory" :key="idx" class="p-2 border rounded-md border-gray-200/60">
                  <div class="grid items-center grid-cols-3 gap-2">
                    <span class="text-xs text-gray-500 whitespace-nowrap">{{ formatLoginTime(item.time || item.at || item.date) }}</span>
                    <span class="text-sm font-medium text-gray-800 truncate">{{ item.email || item.user || '—' }}</span>
                    <span class="text-xs text-gray-600 truncate">{{ item.user_name || '' }}</span>
                  </div>
                </li>
                <li v-if="!loginHistory.length" class="p-4 text-center text-gray-500">{{ t('admin.metrics.no_data') }}</li>
              </ul>
            </div>
          </div>
        </transition>

              <!-- Modal: Logs de error -->
              <transition name="fade">
                <div v-if="showErrors" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50"
                  @click.self="showErrors = false">
                  <div class="w-full max-w-5xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between mb-3">
                      <h3 class="text-xl font-bold text-gray-900/90">{{ t('admin.metrics.errors_title') }}</h3>
                      <button @click="showErrors = false"
                        class="px-3 py-1 text-gray-700 bg-gray-100 border rounded-md">{{
                          t('admin.metrics.close') }}</button>
                    </div>
                    <pre
                      class="w-full p-3 overflow-x-auto text-xs leading-5 text-left text-gray-800 border bg-gray-50 rounded-xl border-gray-200/60">{{ formattedErrors }}</pre>
                    <div v-if="!errorLogs || (Array.isArray(errorLogs) && !errorLogs.length)"
                      class="p-4 text-center text-gray-500">{{ t('admin.metrics.no_data') }}</div>
                  </div>
                </div>
              </transition>

              <!-- Modal: Estadísticas -->
              <transition name="fade">
                <div v-if="showStats" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50"
                  @click.self="showStats = false">
                  <div class="w-full max-w-3xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between mb-3">
                      <h3 class="text-xl font-bold text-gray-900/90">{{ t('admin.metrics.stats_title') }}</h3>
                      <button @click="showStats = false"
                        class="px-3 py-1 text-gray-700 bg-gray-100 border rounded-md">{{
                          t('admin.metrics.close') }}</button>
                    </div>
                    <div class="space-y-4 text-sm text-gray-800">
                      <div class="p-3 border rounded-xl border-gray-200/60">
                        <div class="mb-2 text-sm font-semibold text-gray-700">{{ t('admin.metrics.stats_usage_title') }}</div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                          <div class="p-2 rounded-lg bg-gray-50">
                            <div class="text-xs text-gray-500">{{ t('admin.metrics.stats_calls') }}</div>
                            <div class="font-semibold">{{ stats?.usage_by_role?.total?.calls ??
                              stats?.usage_by_role?.free?.calls ?? 0 }}</div>
                          </div>
                          <div class="p-2 rounded-lg bg-gray-50">
                            <div class="text-xs text-gray-500">{{ t('admin.metrics.stats_users') }}</div>
                            <div class="font-semibold">{{ stats?.usage_by_role?.total?.users ??
                              stats?.usage_by_role?.free?.users ?? 0 }}</div>
                          </div>
                          <div class="p-2 rounded-lg bg-gray-50">
                            <div class="text-xs text-gray-500">{{ t('admin.metrics.stats_avg') }}</div>
                            <div class="font-semibold">{{ stats?.usage_by_role?.total?.avg ??
                              stats?.usage_by_role?.free?.avg ?? 0 }}</div>
                          </div>
                          <div class="p-2 rounded-lg bg-gray-50">
                            <div class="text-xs text-gray-500">{{ t('admin.metrics.stats_max') }}</div>
                            <div class="font-semibold">{{ stats?.usage_by_role?.total?.max ??
                              stats?.usage_by_role?.free?.max ?? 0 }}</div>
                          </div>
                        </div>
                      </div>

                      <!-- Bloque 'Kupo Free' eliminado -->

                      <div v-if="!stats || Object.keys(stats).length === 0" class="p-4 text-center text-gray-500">{{
                        t('admin.metrics.no_data') }}</div>
                    </div>

                    <!-- Top endpoints -->
                    <div class="mt-6">
                      <h4 class="mb-2 text-sm font-semibold text-gray-700">{{ t('admin.metrics.stats_top_endpoints') }}</h4>
                      <div class="overflow-x-auto bg-white border rounded-xl border-gray-200/60">
                        <table class="w-full text-sm">
                          <thead class="text-left text-gray-600 bg-gray-50">
                            <tr>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_endpoint') }}</th>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_hits') }}</th>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_percent') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="row in endpointsTop" :key="row.endpoint_id" class="border-t">
                              <td class="px-3 py-2 whitespace-pre">{{ row.url || row.endpoint_id }}</td>
                              <td class="px-3 py-2">{{ row.hits }}</td>
                              <td class="px-3 py-2">{{ row.percent }}%</td>
                            </tr>
                            <tr v-if="!endpointsTop.length">
                              <td colspan="3" class="px-3 py-4 text-center text-gray-500">{{ t('admin.metrics.no_data')
                                }}
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Top usuarios por endpoint -->
                    <div class="mt-6">
                      <h4 class="mb-2 text-sm font-semibold text-gray-700">{{ t('admin.metrics.stats_top_users_by_endpoint') }}</h4>
                      <div class="overflow-x-auto bg-white border rounded-xl border-gray-200/60">
                        <table class="w-full text-sm">
                          <thead class="text-left text-gray-600 bg-gray-50">
                            <tr>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_user') }}</th>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_email') }}</th>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_endpoint') }}</th>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_hits') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="row in userEndpointsTop" :key="row.user_id + '-' + row.endpoint_id"
                              class="border-t">
                              <td class="px-3 py-2">{{ row.name || '—' }}</td>
                              <td class="px-3 py-2">{{ row.email || '—' }}</td>
                              <td class="px-3 py-2 whitespace-pre">{{ row.endpoint || row.endpoint_id }}</td>
                              <td class="px-3 py-2">{{ row.hits }}</td>
                            </tr>
                            <tr v-if="!userEndpointsTop.length">
                              <td colspan="4" class="px-3 py-4 text-center text-gray-500">{{ t('admin.metrics.no_data')
                                }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Endpoint más usado por usuario -->
                    <div class="mt-6">
                      <h4 class="mb-2 text-sm font-semibold text-gray-700">{{ t('admin.metrics.stats_top_by_user') }}</h4>
                      <div class="overflow-x-auto bg-white border rounded-xl border-gray-200/60">
                        <table class="w-full text-sm">
                          <thead class="text-left text-gray-600 bg-gray-50">
                            <tr>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_user') }}</th>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_email') }}</th>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_endpoint') }}</th>
                              <th class="px-3 py-2">{{ t('admin.metrics.stats_hits') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="row in userTopByUser" :key="'stats-topbyuser-' + row.user_id" class="border-t">
                              <td class="px-3 py-2">{{ row.name || '—' }}</td>
                              <td class="px-3 py-2">{{ row.email || '—' }}</td>
                              <td class="px-3 py-2 whitespace-pre">{{ row.endpoint || row.endpoint_id }}</td>
                              <td class="px-3 py-2">{{ row.hits }}</td>
                            </tr>
                            <tr v-if="!userTopByUser.length">
                              <td colspan="4" class="px-3 py-4 text-center text-gray-500">{{ t('admin.metrics.no_data')
                                }}</td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
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

// Métricas & logs state
const showLogins = ref(false)
const showErrors = ref(false)
const showStats = ref(false)
const loginHistory = ref([])
const errorLogs = ref([])
const stats = ref(null)
const endpointsTop = ref([])
const userEndpointsTop = ref([])
const userTopByUser = ref([])
const formattedErrors = computed(() => {
  try {
    if (!errorLogs.value) return ''
    if (Array.isArray(errorLogs.value)) return errorLogs.value.map(x => typeof x === 'string' ? x : JSON.stringify(x, null, 2)).join('\n')
    if (typeof errorLogs.value === 'string') return errorLogs.value
    return JSON.stringify(errorLogs.value, null, 2)
  } catch { return '' }
})

// Loaders for Métricas & logs
async function openLogins() {
  showLogins.value = true
  try {
    const { data } = await axiosClient.get('/admin/metrics/logins', { params: { order: 'desc', limit: 5000 } })
    loginHistory.value = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : [])
  } catch (e) {
    console.error(e)
    loginHistory.value = []
  }
}

async function openErrors() {
  showErrors.value = true
  try {
    const { data } = await axiosClient.get('/admin/metrics/errors')
    errorLogs.value = data ?? []
  } catch (e) {
    console.error(e)
    errorLogs.value = []
  }
}
async function openStats() {
  showStats.value = true
  try {
    const [{ data: s }, { data: top }, { data: topUsers }, { data: topByUser }] = await Promise.all([
      axiosClient.get('/admin/metrics/stats'),
      axiosClient.get('/admin/metrics/endpoints-top', { params: { limit: 10 } }),
      axiosClient.get('/admin/metrics/user-endpoints-top', { params: { limit: 10 } }),
      axiosClient.get('/admin/metrics/user-top-endpoint'),
    ])
    stats.value = s ?? {}
    endpointsTop.value = Array.isArray(top?.top) ? top.top : []
    userEndpointsTop.value = Array.isArray(topUsers?.top) ? topUsers.top : []
    userTopByUser.value = Array.isArray(topByUser?.top) ? topByUser.top : []
  } catch (e) {
    console.error(e)
    stats.value = {}
    endpointsTop.value = []
    userEndpointsTop.value = []
    userTopByUser.value = []
  }
}


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

// Helpers
function formatLoginTime(raw) {
  if (!raw) return ''
  const d = new Date(raw)
  if (isNaN(d.getTime())) return String(raw)
  const pad = (n) => String(n).padStart(2, '0')
  const hh = pad(d.getHours())
  const mm = pad(d.getMinutes())
  const dd = pad(d.getDate())
  const mo = pad(d.getMonth() + 1)
  const yy = d.getFullYear()
  return `${hh}:${mm} • ${dd}/${mo}/${yy}`
}
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
