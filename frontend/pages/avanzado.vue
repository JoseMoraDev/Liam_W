<template>
  <div class="absolute inset-0 w-full h-screen bg-center bg-cover mt-14" style="background-image: url('/img/menu.jpg')">
    <!-- Capa oscura -->
    <div class="absolute inset-0 bg-black/10"></div>

    <!-- Contenido -->
    <div :class="mounted ? 'opacity-100' : 'opacity-0'"
      class="relative z-10 flex flex-col items-center w-full min-h-screen p-6 mt-5 space-y-8 overflow-y-auto transition-opacity duration-300 md:p-8">
      <!-- Título principal -->
      <div class="w-full max-w-md p-4 border rounded-2xl frost-panel border-white/15">
        <h1 class="text-2xl font-bold text-center text-[color:var(--color-text)]">Opciones avanzadas</h1>
      </div>

      <!-- Administración (solo admin) -->
      <template v-if="isAdmin">
        <div class="w-full max-w-md p-4 space-y-4 border rounded-2xl frost-panel border-white/15">
          <h2 class="text-xl font-bold text-center text-[color:var(--color-text)]">Administración de usuarios</h2>
          <div class="w-full max-w-md mx-auto">
            <div class="grid grid-cols-1 gap-4">
              <button @click="openAdminUsers" class="frost-card border border-white/15">CRUD de usuarios con bloqueos, roles, y cupos de llamadas</button>
            </div>
          </div>
        </div>

        <!-- Modal de respaldo inline (sin Teleport) -->
        <div v-show="themesOpen"
             class="fixed inset-0 flex items-center justify-center bg-black/50"
             style="position:fixed; inset:0; z-index:2147483646;"
             @click.self="themesOpen = false">
          <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto p-4 rounded-2xl shadow-xl theme-surface theme-border border">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-xl font-bold">Temas de colores</h3>
              <button @click="themesOpen = false" class="px-3 py-1 rounded-md border theme-border">Cerrar</button>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
              <div class="p-4 rounded-xl theme-surface theme-border border">
                <h4 class="mb-3 font-semibold">Temas disponibles</h4>
                <ul class="space-y-2">
                  <li v-for="t in themeList" :key="t.id"
                      class="flex items-center justify-between p-2 border rounded-lg theme-border cursor-pointer hover:bg-[color:var(--color-overlay-weak)]"
                      :class="{ 'bg-[color:var(--color-overlay-weak)] border-[color:var(--color-primary)]': t.id === activeId }"
                      @click="activate(t.id)">
                    <div>
                      <div class="font-medium">{{ t.name }}</div>
                      <div class="text-xs theme-text-muted">{{ t.id }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                      <template v-if="t.id === activeId">
                        <span class="inline-flex items-center justify-center h-8 min-w-[88px] px-3 py-1 text-sm rounded-lg text-white bg-[color:var(--color-success)]">Activo</span>
                      </template>
                      <button v-else class="inline-flex items-center justify-center h-8 min-w-[88px] px-3 py-1 text-sm rounded-lg text-white bg-[color:var(--color-primary)]" @click.stop="activate(t.id)">Activar</button>
                      <button class="px-3 py-1 text-sm border rounded-lg theme-border" @click="edit(t)">Editar</button>
                      <button class="px-3 py-1 text-sm text-white rounded-lg bg-[color:var(--color-danger)]" :class="{ 'opacity-50 cursor-not-allowed': t.id === activeId }" :disabled="t.id === activeId" @click.stop="remove(t.id)">Eliminar</button>
                    </div>
                  </li>
                  <li v-if="!themeList.length" class="p-3 text-center text-gray-500">No hay temas.</li>
                </ul>
              </div>

              <div class="p-4 rounded-xl theme-surface theme-border border">
                <h4 class="mb-3 font-semibold">{{ formMode === 'create' ? 'Crear tema' : 'Editar tema' }}</h4>
                <div class="grid gap-3">
                  <label class="grid gap-1">
                    <span class="text-sm theme-text-muted">ID</span>
                    <input v-model="form.id" class="px-3 py-2 border rounded-lg theme-border bg-transparent" placeholder="kebab-case" />
                  </label>
                  <label class="grid gap-1">
                    <span class="text-sm theme-text-muted">Nombre</span>
                    <input v-model="form.name" class="px-3 py-2 border rounded-lg theme-border bg-transparent" />
                  </label>
                  <details class="mt-2" open>
                    <summary class="mb-2 font-medium">Colores</summary>
                    <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-2 gap-4">
                      <div v-for="(v,k) in form.vars" :key="k" class="grid gap-1">
                        <label class="text-xs theme-text-muted">{{ k }}</label>
                        <div class="flex items-center gap-2 w-full">
                          <!-- Preview externo (siempre oculto ahora) -->
                          <span class="hidden w-6 h-6 rounded-md border theme-border shrink-0" :style="{ backgroundColor: '#' + stripHash(form.vars[k] || '') }"></span>
                          <!-- Contenedor relativo para preview/botón internos en todas las resoluciones -->
                          <div class="relative flex-1 min-w-0">
                            <!-- Preview interno (todas las resoluciones) -->
                            <span class="pointer-events-none absolute left-2 top-1/2 -translate-y-1/2 w-5 h-5 rounded border theme-border" :style="{ backgroundColor: '#' + stripHash(form.vars[k] || '') }"></span>
                            <input :value="stripHash(form.vars[k])"
                                   @input="onInputColor(k, $event)"
                                   class="w-full px-3 py-2 border rounded-lg theme-border bg-transparent pl-10 pr-24"
                                   placeholder="e.g. 0b0f19" />
                            <!-- Botón interno (todas las resoluciones) -->
                            <button type="button"
                                    class="inline-flex items-center justify-center absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-sm rounded-md border theme-border whitespace-nowrap"
                                    @click="openPicker($event)">Cambiar</button>
                          </div>
                          <!-- Botón externo (siempre oculto ahora) -->
                          <button type="button" class="hidden px-3 py-1 text-sm rounded-md border theme-border whitespace-nowrap shrink-0" @click="openPicker($event)">Cambiar</button>
                          <input type="color" class="hidden" :value="normalizeHex(form.vars[k])" @input="onPickColor(k, $event)" />
                        </div>
                      </div>
                    </div>
                  </details>
                  <div class="flex gap-2 mt-2">
                    <button class="px-3 py-2 text-white rounded-lg bg-[color:var(--color-primary)]" @click="save">Guardar</button>
                    <button class="px-3 py-2 border rounded-lg theme-border" @click="resetForm">Limpiar</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="w-full max-w-md p-4 space-y-4 border rounded-2xl frost-panel border-white/15">
          <h2 class="text-xl font-bold text-center text-[color:var(--color-text)]">Temas de colores</h2>
          <div class="w-full max-w-md mx-auto">
            <div class="grid grid-cols-1 gap-4">
              <button @click.prevent="openThemes" class="frost-card border border-white/15">CRUD de temas de colores</button>
            </div>
          </div>
        </div>

        <!-- Métricas y logs -->
        <div class="w-full max-w-md p-4 space-y-4 border rounded-2xl frost-panel border-white/15">
          <h2 class="text-xl font-bold text-center text-[color:var(--color-text)]">{{ t('admin.metrics.section_title') }}</h2>
          <div class="w-full max-w-md mx-auto">
            <div class="grid grid-cols-1 gap-3">
              <button @click="openLogins" class="w-full frost-card border border-white/15">{{ t('admin.metrics.open_logins') }}</button>
              <button @click="openErrors" class="w-full frost-card border border-white/15">{{ t('admin.metrics.open_errors') }}</button>
              <button @click="openStats" class="w-full frost-card border border-white/15">{{ t('admin.metrics.open_stats') }}</button>
            </div>
          </div>
        </div>

        <!-- Modal de gestión de usuarios -->
        <transition name="fade">
          <div v-if="adminOpen" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50"
            @click.self="adminOpen = false">
            <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto p-4 rounded-2xl shadow-xl theme-surface theme-border border">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-xl font-bold text-[color:var(--color-text)]">{{ t('admin.users.title') }}</h3>
                <button @click="adminOpen = false" class="px-3 py-1 text-gray-700 bg-gray-100 border rounded-md">{{
                  t('admin.users.close') }}</button>
              </div>
              <!-- Gestión de usuarios -->
              <div class="w-full p-0 space-y-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
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
                <div class="hidden overflow-x-auto md:block rounded-xl theme-surface theme-border border">
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
                            class="w-full max-w-[220px] px-2 py-1 border rounded-md theme-border bg-transparent" />
                        </td>
                        <td class="px-3 py-2">
                          <input v-model="u.email" disabled
                            class="w-full max-w-[260px] px-2 py-1 border rounded-md theme-border bg-transparent opacity-75 cursor-not-allowed" />
                        </td>
                        <td class="px-3 py-2">
                          <select v-model="u.role" @change="setRole(u)"
                            class="px-2 py-1 border rounded-md theme-border bg-transparent">
                            <option value="free">{{ t('admin.users.role_free') }}</option>
                            <option value="premium">{{ t('admin.users.role_premium') }}</option>
                            <option value="admin">{{ t('admin.users.role_admin') }}</option>
                          </select>
                        </td>
                        <td class="px-3 py-2">
                          <input type="number" min="0" v-model.number="u.free_daily_quota" :disabled="u.role !== 'free'"
                            class="w-24 px-2 py-1 border rounded-md theme-border bg-transparent disabled:opacity-50" />
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
                            class="px-2 py-1 rounded-md border text-[color:var(--color-danger)] border-[color:var(--color-danger)]/40 bg-[color:var(--color-overlay-weak)] hover:bg-[color:var(--color-danger)]/10">{{
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
                  <div v-for="u in filteredUsers" :key="u.id" class="p-3 rounded-xl theme-surface theme-border border">
                    <div class="flex items-center justify-between mb-2">
                      <div class="text-xs text-gray-500">{{ t('admin.users.id') }} {{ u.id }}</div>
                      <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" v-model="u.is_blocked" @change="toggleBlock(u)" />
                        <span>{{ u.is_blocked ? t('admin.users.blocked') : t('admin.users.active') }}</span>
                      </label>
                    </div>
                    <div class="flex flex-col gap-2">
                      <input v-model="u.name" @change="saveUser(u)"
                        class="w-full h-10 px-3 border rounded-lg theme-border bg-transparent"
                        :placeholder="t('admin.users.name')" />
                      <input v-model="u.email" disabled
                        class="w-full h-10 px-3 border rounded-lg opacity-75 cursor-not-allowed theme-border bg-transparent"
                        :placeholder="t('admin.users.email')" />
                      <div class="flex items-center gap-2">
                        <select v-model="u.role" @change="setRole(u)"
                          class="flex-1 h-10 px-3 border rounded-lg theme-border bg-transparent">
                          <option value="free">{{ t('admin.users.role_free') }}</option>
                          <option value="premium">{{ t('admin.users.role_premium') }}</option>
                          <option value="admin">{{ t('admin.users.role_admin') }}</option>
                        </select>
                        <input type="number" min="0" v-model.number="u.free_daily_quota" :disabled="u.role !== 'free'"
                          class="h-10 px-2 border rounded-lg w-28 theme-border bg-transparent disabled:opacity-50"
                          :placeholder="t('admin.users.quota')" />
                        <button @click="removeUser(u)"
                          class="px-3 py-2 rounded-md border text-[color:var(--color-danger)] border-[color:var(--color-danger)]/40 bg-[color:var(--color-overlay-weak)] hover:bg-[color:var(--color-danger)]/10">{{
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
                  <div class="w-full max-w-md p-4 rounded-2xl shadow-xl theme-surface theme-border border">
                    <h3 class="mb-3 text-lg font-semibold text-gray-800">{{ dialog.mode === 'create' ?
                      t('admin.users.modal_create') :
                      t('admin.users.modal_edit') }}</h3>
                    <form @submit.prevent="submitDialog" class="space-y-3">
                      <input v-model="dialog.form.name" type="text" :placeholder="t('admin.users.name')"
                        class="w-full h-10 px-3 border rounded-lg theme-border bg-transparent" required />
                      <input v-model="dialog.form.email" type="email" :placeholder="t('admin.users.email')"
                        class="w-full h-10 px-3 border rounded-lg theme-border bg-transparent" :disabled="dialog.mode === 'edit'"
                        :required="dialog.mode === 'create'" />
                      <input v-if="dialog.mode === 'create' || dialog.form.password !== undefined"
                        v-model="dialog.form.password" type="password" :placeholder="t('admin.users.password')"
                        class="w-full h-10 px-3 border rounded-lg theme-border bg-transparent"
                        :required="dialog.mode === 'create'" minlength="8" />
                      <div class="flex gap-2">
                        <select v-model="dialog.form.role"
                          class="flex-1 h-10 px-3 border rounded-lg theme-border bg-transparent">
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

        <!-- Modal: CRUD de temas de colores (Teleport) -->
        <client-only>
        <Teleport to="body">
            <div v-show="themesOpen"
                 class="fixed inset-0 flex items-center justify-center bg-black/50"
                 style="position:fixed; inset:0; z-index:2147483647; opacity:1; visibility:visible; pointer-events:auto;"
                 @click.self="themesOpen = false" data-testid="themes-modal-overlay">
              <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto p-4 rounded-2xl shadow-xl theme-surface theme-border border"
                       style="opacity:1; visibility:visible; pointer-events:auto; z-index:2147483647;"
                       data-testid="themes-modal">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-xl font-bold">Temas de colores</h3>
                  <button @click="themesOpen = false" class="px-3 py-1 rounded-md border theme-border">Cerrar</button>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                  <!-- Lista de temas -->
                  <div class="p-4 rounded-xl theme-surface theme-border border">
                    <h4 class="mb-3 font-semibold">Temas disponibles</h4>
                    <ul class="space-y-2">
                      <li v-for="t in themeList" :key="t.id"
                          class="flex items-center justify-between p-2 border rounded-lg theme-border cursor-pointer hover:bg-[color:var(--color-overlay-weak)]"
                          :class="{ 'bg-[color:var(--color-overlay-weak)] border-[color:var(--color-primary)]': t.id === activeId }"
                          @click="activate(t.id)">
                        <div>
                          <div class="font-medium">{{ t.name }}</div>
                          <div class="text-xs theme-text-muted">{{ t.id }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                          <template v-if="t.id === activeId">
                            <span class="inline-flex items-center justify-center h-8 min-w-[88px] px-3 py-1 text-sm rounded-lg text-white bg-[color:var(--color-success)]">Activo</span>
                          </template>
                          <button v-else class="inline-flex items-center justify-center h-8 min-w-[88px] px-3 py-1 text-sm rounded-lg text-white bg-[color:var(--color-primary)]" @click.stop="activate(t.id)">Activar</button>
                          <button class="px-3 py-1 text-sm border rounded-lg theme-border" @click.stop="edit(t)">Editar</button>
                          <button class="px-3 py-1 text-sm text-white rounded-lg bg-[color:var(--color-danger)]" :class="{ 'opacity-50 cursor-not-allowed': t.id === activeId }" :disabled="t.id === activeId" @click.stop="remove(t.id)">Eliminar</button>
                        </div>
                      </li>
                      <li v-if="!themeList.length" class="p-3 text-center text-gray-500">No hay temas.</li>
                    </ul>
                  </div>

                  <!-- Formulario -->
                  <div class="p-4 rounded-xl theme-surface theme-border border">
                    <h4 class="mb-3 font-semibold">{{ formMode === 'create' ? 'Crear tema' : 'Editar tema' }}</h4>
                    <div class="grid gap-3">
                      <label class="grid gap-1">
                        <span class="text-sm theme-text-muted">ID</span>
                        <input v-model="form.id" class="px-3 py-2 border rounded-lg theme-border bg-transparent" placeholder="kebab-case" />
                      </label>
                      <label class="grid gap-1">
                        <span class="text-sm theme-text-muted">Nombre</span>
                        <input v-model="form.name" class="px-3 py-2 border rounded-lg theme-border bg-transparent" />
                      </label>

                      <details class="mt-2" open>
                        <summary class="mb-2 font-medium">Colores</summary>
                        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-2 gap-4">
                          <div v-for="(v,k) in form.vars" :key="k" class="grid gap-1">
                            <label class="text-xs theme-text-muted">{{ k }}</label>
                            <div class="flex items-center gap-2 w-full">
                              <!-- Preview externo (siempre oculto ahora) -->
                              <span class="hidden w-6 h-6 rounded-md border theme-border shrink-0" :style="{ backgroundColor: '#' + stripHash(form.vars[k] || '') }"></span>
                              <!-- Contenedor relativo para preview/botón internos en todas las resoluciones -->
                              <div class="relative flex-1 min-w-0">
                                <!-- Preview interno (todas las resoluciones) -->
                                <span class="pointer-events-none absolute left-2 top-1/2 -translate-y-1/2 w-5 h-5 rounded border theme-border" :style="{ backgroundColor: '#' + stripHash(form.vars[k] || '') }"></span>
                                <input :value="stripHash(form.vars[k])"
                                       @input="onInputColor(k, $event)"
                                       class="w-full px-3 py-2 border rounded-lg theme-border bg-transparent pl-10 pr-24"
                                       placeholder="e.g. 0b0f19" />
                                <!-- Botón interno (todas las resoluciones) -->
                                <button type="button"
                                        class="inline-flex items-center justify-center absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1 text-sm rounded-md border theme-border whitespace-nowrap"
                                        @click="openPicker($event)">Cambiar</button>
                              </div>
                              <!-- Botón externo (siempre oculto ahora) -->
                              <button type="button" class="hidden px-3 py-1 text-sm rounded-md border theme-border whitespace-nowrap shrink-0" @click="openPicker($event)">Cambiar</button>
                              <input type="color" class="hidden" :value="normalizeHex(form.vars[k])" @input="onPickColor(k, $event)" />
                            </div>
                          </div>
                        </div>
                      </details>

                      <div class="flex gap-2 mt-2">
                        <button class="px-3 py-2 text-white rounded-lg bg-[color:var(--color-primary)]" @click="save">Guardar</button>
                        <button class="px-3 py-2 border rounded-lg theme-border" @click="resetForm">Limpiar</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </Teleport>
        </client-only>

            </div>
          </div>
        </transition>

        <!-- Modal: Historial de logins -->
        <transition name="fade">
          <div v-if="showLogins" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50" @click.self="showLogins = false">
            <div class="w-full max-w-3xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded-2xl shadow-xl">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-xl font-bold text-[color:var(--color-text)]">{{ t('admin.metrics.logins_title') }}</h3>
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
                      <h3 class="text-xl font-bold text-[color:var(--color-text)]">{{ t('admin.metrics.errors_title') }}</h3>
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
                      <h3 class="text-xl font-bold text-[color:var(--color-text)]">{{ t('admin.metrics.stats_title') }}</h3>
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
import { ref, onMounted, computed, reactive, nextTick } from 'vue'
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

// Temas de colores (CRUD modal)
import { themes, activeThemeId, upsertTheme as upsertThemeStore, deleteTheme as deleteThemeStore, setActiveTheme as setActiveThemeStore } from '~/store/theme'
const themesOpen = ref(false)
const themeList = computed(() => {
  const list = Array.isArray(themes().value) ? [...themes().value] : []
  return list.sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''), 'es', { sensitivity: 'base' }))
})
const activeId = activeThemeId()
const form = reactive({
  id: '',
  name: '',
  vars: {
    '--color-bg': '#0b0f19',
    '--color-surface': '#111827',
    '--color-surface-weak': '#111827CC',
    '--color-text': '#e5e7eb',
    '--color-text-muted': '#9ca3af',
    '--color-border': '#374151',
    '--color-primary': '#3b82f6',
    '--color-secondary': '#a855f7',
    '--color-success': '#22c55e',
    '--color-warning': '#f59e0b',
    '--color-danger': '#ef4444',
    '--color-info': '#06b6d4',
    '--color-overlay-weak': '#FFFFFF0D',
    '--color-overlay-strong': '#FFFFFF1A'
  }
})
const editing = ref(false)
const formMode = computed(() => editing.value ? 'edit' : 'create')
function edit(t){
  form.id = t.id
  form.name = t.name
  form.vars = { ...t.vars }
  editing.value = true
}
function resetForm(){
  form.id = ''
  form.name = ''
  editing.value = false
}
function save(){
  if (!form.id || !form.name) return
  // Normalizar todos los colores a HEX con '#'
  const normVars = { ...form.vars }
  Object.keys(normVars).forEach(k => {
    normVars[k] = normalizeHex(normVars[k])
  })
  upsertThemeStore({ id: form.id, name: form.name, vars: normVars })
  editing.value = false
}
function remove(id){
  deleteThemeStore(id)
  if (id === activeThemeId().value) setActiveThemeStore(themeList.value[0]?.id)
}
function activate(id){
  setActiveThemeStore(id)
}

async function openThemes(){
  // Cerrar otros modales para evitar superposición
  adminOpen.value = false
  showLogins.value = false
  showErrors.value = false
  showStats.value = false
  themesOpen.value = true
  console.log('[Avanzado] themesOpen ->', themesOpen.value)
  await nextTick()
  // Sin navegación de fallback: el modal debe abrirse localmente
}

function openAdminUsers(){
  themesOpen.value = false
  showLogins.value = false
  showErrors.value = false
  showStats.value = false
  adminOpen.value = true
}

function normalizeHex(val){
  const raw = String(val ?? '').trim()
  // rgba(r,g,b,a)
  const mRgba = raw.match(/^rgba?\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*(\d*\.?\d+))?\s*\)$/i)
  if (mRgba) {
    const r = Math.max(0, Math.min(255, parseInt(mRgba[1], 10)))
    const g = Math.max(0, Math.min(255, parseInt(mRgba[2], 10)))
    const b = Math.max(0, Math.min(255, parseInt(mRgba[3], 10)))
    const aFloat = mRgba[4] !== undefined ? Math.max(0, Math.min(1, parseFloat(mRgba[4]))) : undefined
    const to2 = (n)=> n.toString(16).padStart(2, '0')
    if (aFloat === undefined) return `#${to2(r)}${to2(g)}${to2(b)}`
    const a = Math.round(aFloat * 255)
    return `#${to2(r)}${to2(g)}${to2(b)}${to2(a)}`
  }
  // #RGB, #RRGGBB, #RGBA, #RRGGBBAA
  if (raw.startsWith('#')) {
    const s = raw.slice(1)
    if (s.length === 3 || s.length === 4) {
      const r = s[0], g = s[1], b = s[2], a = s[3]
      return `#${r}${r}${g}${g}${b}${b}${a ? a + a : ''}`
    }
    if (s.length === 6 || s.length === 8) {
      return `#${s}`
    }
    // Fallback: limpiar y recortar a 6
    const hex = s.replace(/[^0-9a-fA-F]/g, '').slice(0, 6)
    return `#${hex.padEnd(6, hex[hex.length-1] || '0')}`
  }
  // Valores sin # (p.ej. '0b0f19')
  const cleaned = raw.replace(/[^0-9a-fA-F]/g, '')
  if (!cleaned) return '#000000'
  if (cleaned.length === 3 || cleaned.length === 4) {
    const r = cleaned[0], g = cleaned[1], b = cleaned[2], a = cleaned[3]
    return `#${r}${r}${g}${g}${b}${b}${a ? a + a : ''}`
  }
  if (cleaned.length === 6 || cleaned.length === 8) return `#${cleaned}`
  const hex6 = cleaned.slice(0, 6)
  return `#${hex6.padEnd(6, hex6[hex6.length-1] || '0')}`
}

function stripHash(val){
  return String(val || '').replace(/^#/, '')
}
function onInputColor(key, e){
  form.vars[key] = String(e?.target?.value || '').replace(/^#/, '')
}

function openPicker(e){
  try {
    const root = e?.target?.closest('div')
    const el = root ? root.querySelector('input[type="color"]') : null
    if (el) el.click()
  } catch {}
}
function onPickColor(key, e){
  const hex = String(e?.target?.value || '').replace(/^#/, '')
  form.vars[key] = hex
}

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
/* Ultra frosted glass (consistente con Ajustes) */
.frost-panel {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 3%, transparent),
      color-mix(in srgb, var(--color-primary) 3%, transparent)),
    linear-gradient(
      to bottom,
      color-mix(in srgb, var(--color-bg) 10%, transparent),
      color-mix(in srgb, var(--color-bg) 10%, transparent)
    );
  background-blend-mode: normal, normal;
  background-color: transparent;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.frost-card {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 0.75rem; /* py-2 px-3 */
  font-size: 0.875rem; /* text-sm */
  line-height: 1.25rem;
  font-weight: 600; /* font-semibold */
  border-radius: 0.75rem; /* rounded-xl */
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-duration: 150ms; /* transition-colors */
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.06); /* shadow-inner */
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 5%, transparent),
      color-mix(in srgb, var(--color-primary) 5%, transparent)),
    linear-gradient(
      to bottom,
      color-mix(in srgb, var(--color-bg) 12%, transparent),
      color-mix(in srgb, var(--color-bg) 12%, transparent)
    );
  background-blend-mode: normal, normal;
  background-color: transparent;
}

.frost-card:hover {
  background-image:
    linear-gradient(to bottom,
      color-mix(in srgb, var(--color-primary) 7%, transparent),
      color-mix(in srgb, var(--color-primary) 7%, transparent)),
    linear-gradient(
      to bottom,
      color-mix(in srgb, var(--color-bg) 14%, transparent),
      color-mix(in srgb, var(--color-bg) 14%, transparent)
    );
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
