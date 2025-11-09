import { watch } from 'vue'
import { themes, activeThemeId, getActiveTheme } from '~/store/theme'

export default defineNuxtPlugin(() => {
  if (process.server) return

  // Hydrate from localStorage
  try {
    const storedThemes = localStorage.getItem('themes')
    if (storedThemes) {
      themes().value = JSON.parse(storedThemes)
    }
    const storedActive = localStorage.getItem('activeThemeId')
    if (storedActive) {
      activeThemeId().value = storedActive
    }
  } catch {}

  const applyVars = (vars: Record<string, string>) => {
    const root = document.documentElement
    Object.entries(vars).forEach(([k, v]) => {
      root.style.setProperty(k, v)
    })
  }

  // Apply once on load
  const initial = getActiveTheme()
  if (initial) applyVars(initial.vars)

  // Watch changes
  watch([themes(), activeThemeId()], () => {
    const theme = getActiveTheme()
    if (theme) applyVars(theme.vars)
  }, { deep: true })
})
