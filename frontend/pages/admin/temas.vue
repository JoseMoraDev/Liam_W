<template>
  <div class="min-h-screen p-6 bg-[color:var(--color-bg)] text-[color:var(--color-text)]">
    <h1 class="mb-4 text-2xl font-bold">Temas de colores</h1>

    <div class="grid gap-4 md:grid-cols-2">
      <div class="p-4 rounded-xl theme-surface theme-border border">
        <h2 class="mb-3 font-semibold">Temas disponibles</h2>
        <ul class="space-y-2">
          <li v-for="t in list" :key="t.id" class="flex items-center justify-between p-2 rounded-lg theme-surface-weak">
            <div>
              <div class="font-medium">{{ t.name }}</div>
              <div class="text-sm theme-text-muted">{{ t.id }}</div>
            </div>
            <div class="flex items-center gap-2">
              <button class="px-3 py-1 text-sm rounded-lg border theme-border hover:theme-surface" @click="edit(t)">Editar</button>
              <button class="px-3 py-1 text-sm rounded-lg bg-[color:var(--color-danger)] text-white" @click="remove(t.id)">Eliminar</button>
              <button class="px-3 py-1 text-sm rounded-lg bg-[color:var(--color-primary)] text-white" @click="activate(t.id)">Activar</button>
            </div>
          </li>
        </ul>
      </div>

      <div class="p-4 rounded-xl theme-surface theme-border border">
        <h2 class="mb-3 font-semibold">{{ formMode === 'create' ? 'Crear tema' : 'Editar tema' }}</h2>
        <div class="grid gap-3">
          <label class="grid gap-1">
            <span class="text-sm theme-text-muted">ID</span>
            <input v-model="form.id" class="px-3 py-2 rounded-lg border theme-border bg-transparent" placeholder="kebab-case" />
          </label>
          <label class="grid gap-1">
            <span class="text-sm theme-text-muted">Nombre</span>
            <input v-model="form.name" class="px-3 py-2 rounded-lg border theme-border bg-transparent" />
          </label>

          <details class="mt-2" open>
            <summary class="mb-2 font-medium">Colores</summary>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div v-for="(v,k) in form.vars" :key="k" class="grid gap-1">
                <label class="text-xs theme-text-muted">{{ k }}</label>
                <input v-model="form.vars[k]" class="px-3 py-2 rounded-lg border theme-border bg-transparent" />
              </div>
            </div>
          </details>

          <div class="flex gap-2 mt-2">
            <button class="px-3 py-2 rounded-lg bg-[color:var(--color-primary)] text-white" @click="save">Guardar</button>
            <button class="px-3 py-2 rounded-lg border theme-border" @click="resetForm">Limpiar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { themes, activeThemeId, upsertTheme, deleteTheme as del, setActiveTheme } from '~/store/theme'

const list = computed(() => themes().value)
const formMode = computed(() => editing.value ? 'edit' : 'create')
const editing = reactive({ value: false })

const form = reactive({
  id: '',
  name: '',
  vars: {
    '--color-bg': '#0b0f19',
    '--color-surface': '#111827',
    '--color-surface-weak': 'rgba(17,24,39,0.8)',
    '--color-text': '#e5e7eb',
    '--color-text-muted': '#9ca3af',
    '--color-border': '#374151',
    '--color-primary': '#3b82f6',
    '--color-secondary': '#a855f7',
    '--color-success': '#22c55e',
    '--color-warning': '#f59e0b',
    '--color-danger': '#ef4444',
    '--color-info': '#06b6d4',
    '--color-overlay-weak': 'rgba(255,255,255,0.05)',
    '--color-overlay-strong': 'rgba(255,255,255,0.1)'
  }
})

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
  const normVars = { ...form.vars }
  Object.keys(normVars).forEach(k => {
    normVars[k] = normalizeHex(normVars[k])
  })
  upsertTheme({ id: form.id, name: form.name, vars: normVars })
  editing.value = false
}

function remove(id){
  del(id)
  if (id === activeThemeId().value) setActiveTheme(list.value[0]?.id)
}

function activate(id){
  setActiveTheme(id)
}

function normalizeHex(val){
  const s = String(val || '').trim().replace(/^#/, '')
  const hex = s.replace(/[^0-9a-fA-F]/g, '').slice(0, 6)
  if (!hex) return '#000000'
  if (hex.length === 3) return '#' + hex.split('').map(c => c + c).join('')
  return '#' + hex.padEnd(6, hex[hex.length-1] || '0')
}
</script>
