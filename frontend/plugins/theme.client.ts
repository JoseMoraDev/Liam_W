import { watch } from 'vue'
import { themes, activeThemeId, getActiveTheme, previewThemeId, getThemeById } from '~/store/theme'

export default defineNuxtPlugin(() => {
  if (process.server) return

  // Hydrate from localStorage
  try {
    const storedThemes = localStorage.getItem('themes')
    if (storedThemes) {
      // Merge: keep defaults and overlay user-stored by id (excluding 'one-dark')
      const defaults = Array.isArray(themes().value) ? [...themes().value] : []
      const storedRaw = JSON.parse(storedThemes)
      const stored = Array.isArray(storedRaw) ? storedRaw.filter((t:any) => t && t.id !== 'one-dark') : []
      const byId = new Map<string, any>(defaults.map((t:any) => [t.id, t]))
      for (const t of stored) {
        if (t && t.id && t.id !== 'one-dark') byId.set(t.id, t)
      }
      themes().value = Array.from(byId.values())
      // Persist merged, filtered list to avoid reintroducing 'one-dark'
      localStorage.setItem('themes', JSON.stringify(themes().value))
    }
    const storedActive = localStorage.getItem('activeThemeId')
    if (storedActive && storedActive !== 'one-dark') {
      activeThemeId().value = storedActive
    } else if (storedActive === 'one-dark') {
      activeThemeId().value = 'dark-magenta'
      localStorage.setItem('activeThemeId', 'dark-magenta')
    }
    // Clear preview if it points to 'one-dark'
    if (previewThemeId().value === 'one-dark') {
      previewThemeId().value = ''
    }
  } catch {}

  const applyVars = (vars: Record<string, string>) => {
    const root = document.documentElement
    Object.entries(vars).forEach(([k, v]) => {
      root.style.setProperty(k, v)
    })
  }

  // Apply once on load (preview > active)
  const previewId = previewThemeId().value === 'one-dark' ? '' : previewThemeId().value
  const initial = previewId ? getThemeById(previewId) : getActiveTheme()
  if (initial) applyVars(initial.vars)

  // Watch changes
  watch([themes(), activeThemeId(), previewThemeId()], () => {
    const pId = previewThemeId().value
    const theme = pId ? getThemeById(pId) : getActiveTheme()
    if (theme) applyVars(theme.vars)
  }, { deep: true })
})
