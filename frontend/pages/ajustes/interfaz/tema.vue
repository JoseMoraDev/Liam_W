<template>
  <div class="min-h-screen p-6 bg-[color:var(--color-bg)] text-[color:var(--color-text)]">
    <h1 class="mb-4 text-2xl font-bold">Tema de colores</h1>

    <div class="grid gap-4 md:grid-cols-2">
      <div class="p-4 rounded-xl theme-surface theme-border border">
        <h2 class="mb-2 font-semibold">Seleccionar tema</h2>
        <ul class="space-y-2">
          <li v-for="t in list" :key="t.id" class="flex items-center justify-between p-2 rounded-lg theme-surface-weak">
            <div>
              <div class="font-medium">{{ t.name }}</div>
              <div class="text-xs theme-text-muted">{{ t.id }}</div>
            </div>
            <button class="px-3 py-1 text-sm rounded-lg bg-[color:var(--color-primary)] text-white" @click="activate(t.id)">Activar</button>
          </li>
        </ul>
      </div>

      <div class="p-4 rounded-xl theme-surface theme-border border">
        <h2 class="mb-2 font-semibold">Vista previa</h2>
        <div class="grid grid-cols-2 gap-3">
          <div class="p-3 rounded-lg theme-surface-weak border theme-border">Texto normal</div>
          <div class="p-3 rounded-lg theme-surface-weak border theme-border theme-text-muted">Texto tenue</div>
          <div class="p-3 rounded-lg border theme-border text-white" :style="{ backgroundColor: css('--color-primary') }">Primario</div>
          <div class="p-3 rounded-lg border theme-border text-white" :style="{ backgroundColor: css('--color-danger') }">Peligro</div>
          <div class="p-3 rounded-lg border theme-border text-white" :style="{ backgroundColor: css('--color-warning') }">Aviso</div>
          <div class="p-3 rounded-lg border theme-border text-white" :style="{ backgroundColor: css('--color-success') }">Éxito</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { themes, setActiveTheme } from '~/store/theme'

const list = computed(() => {
  const arr = Array.isArray(themes().value) ? [...themes().value] : []
  return arr.sort((a,b) => String(a.name||'').localeCompare(String(b.name||''), 'es', { sensitivity: 'base' }))
})

function activate(id){
  setActiveTheme(id)
}

function css(name){
  if (process.server) return ''
  return getComputedStyle(document.documentElement).getPropertyValue(name).trim()
}
</script>
