// Estado global de temas
export const themes = () =>
  useState('themes', () => {
    const initial = [
    {
      id: 'dark-magenta',
      name: 'Oscuro Magenta',
      vars: {
        '--color-bg': '#100414',
        '--color-surface': '#16061b',
        '--color-surface-weak': '#16061bCC',
        '--color-text': '#f5f3f7',
        '--color-text-muted': '#e9d5ff',
        '--color-border': '#3c0f45',
        '--color-primary': '#c026d3',
        '--color-secondary': '#e11d48',
        '--color-success': '#22c55e',
        '--color-warning': '#f59e0b',
        '--color-danger': '#ef4444',
        '--color-info': '#a21caf',
        '--color-overlay-weak': '#FFFFFF0D',
        '--color-overlay-strong': '#FFFFFF1A'
      }
    },
    {
      id: 'dark-navy',
      name: 'Oscuro Navy',
      vars: {
        '--color-bg': '#0b1220',
        '--color-surface': '#0f172a',
        '--color-surface-weak': '#0f172aCC',
        '--color-text': '#e5e7eb',
        '--color-text-muted': '#94a3b8',
        '--color-border': '#1e293b',
        '--color-primary': '#1e3a8a',
        '--color-secondary': '#7c3aed',
        '--color-success': '#22c55e',
        '--color-warning': '#f59e0b',
        '--color-danger': '#ef4444',
        '--color-info': '#0ea5e9',
        '--color-overlay-weak': '#FFFFFF0D',
        '--color-overlay-strong': '#FFFFFF1A'
      }
    },
    {
      id: 'light',
      name: 'Claro',
      vars: {
        '--color-bg': '#fafafa',
        '--color-surface': '#ffffff',
        '--color-surface-weak': '#FFFFFFE6',
        '--color-text': '#111827',
        '--color-text-muted': '#6b7280',
        '--color-border': '#e5e7eb',
        '--color-primary': '#2563eb',
        '--color-secondary': '#7c3aed',
        '--color-success': '#16a34a',
        '--color-warning': '#d97706',
        '--color-danger': '#dc2626',
        '--color-info': '#0891b2',
        '--color-overlay-weak': '#0000000A',
        '--color-overlay-strong': '#00000014'
      }
    },
    {
      id: 'light-green',
      name: 'Claro Verde',
      vars: {
        '--color-bg': '#f3f8f4',
        '--color-surface': '#ffffff',
        '--color-surface-weak': '#FFFFFFE6',
        '--color-text': '#1f2937',
        '--color-text-muted': '#6b7280',
        '--color-border': '#d1e3d6',
        '--color-primary': '#16a34a',
        '--color-secondary': '#10b981',
        '--color-success': '#16a34a',
        '--color-warning': '#eab308',
        '--color-danger': '#dc2626',
        '--color-info': '#0ea5e9',
        '--color-overlay-weak': '#0000000A',
        '--color-overlay-strong': '#00000014'
      }
    },
    {
      id: 'light-calm',
      name: 'Claro Calmado',
      vars: {
        '--color-bg': '#f7f7fb',
        '--color-surface': '#ffffff',
        '--color-surface-weak': '#FFFFFFE6',
        '--color-text': '#1f2937',
        '--color-text-muted': '#7f8a9a',
        '--color-border': '#dfe3ea',
        '--color-primary': '#6366f1',
        '--color-secondary': '#14b8a6',
        '--color-success': '#22c55e',
        '--color-warning': '#f59e0b',
        '--color-danger': '#ef4444',
        '--color-info': '#06b6d4',
        '--color-overlay-weak': '#0000000A',
        '--color-overlay-strong': '#00000014'
      }
    },
    {
      id: 'dark-green',
      name: 'Oscuro Verde',
      vars: {
        '--color-bg': '#0a0f0a',
        '--color-surface': '#0f1a12',
        '--color-surface-weak': '#0f1a12CC',
        '--color-text': '#e6f4ea',
        '--color-text-muted': '#9fb3a7',
        '--color-border': '#1f3a2a',
        '--color-primary': '#22c55e',
        '--color-secondary': '#10b981',
        '--color-success': '#22c55e',
        '--color-warning': '#f59e0b',
        '--color-danger': '#ef4444',
        '--color-info': '#06b6d4',
        '--color-overlay-weak': '#FFFFFF0D',
        '--color-overlay-strong': '#FFFFFF1A'
      }
    }
    ]
    // Intentar cargar desde localStorage y filtrar 'one-dark'
    if (process.client) {
      try {
        const saved = JSON.parse(localStorage.getItem('themes') || '[]')
        const filtered = Array.isArray(saved) && saved.length
          ? saved.filter(t => t && t.id !== 'one-dark')
          : null
        if (filtered) {
          localStorage.setItem('themes', JSON.stringify(filtered))
          return filtered
        }
      } catch { }
    }
    return initial
  });

export const activeThemeId = () => useState('activeThemeId', () => 'dark-magenta');
export const previewThemeId = () => useState('previewThemeId', () => '');

export function setActiveTheme(id) {
  // Bloquear 'one-dark'
  if (id === 'one-dark') id = 'dark-magenta'
  activeThemeId().value = id;
  if (process.client) localStorage.setItem('activeThemeId', id);
  // Aplicación inmediata de variables (fallback al watcher del plugin)
  if (process.client) {
    try {
      const t = getThemeById(id)
      if (t && t.vars) {
        const root = document.documentElement
        Object.entries(t.vars).forEach(([k, v]) => root.style.setProperty(k, v))
      }
    } catch { }
  }
}

export function setPreviewTheme(id) {
  previewThemeId().value = id || '';
}

export function clearPreviewTheme() {
  previewThemeId().value = '';
}

export function upsertTheme(theme) {
  // Never allow 'one-dark' to be (re)added
  if (!theme || theme.id === 'one-dark') {
    // Also purge if present
    themes().value = themes().value.filter(t => t.id !== 'one-dark')
    if (process.client) localStorage.setItem('themes', JSON.stringify(themes().value));
    return;
  }
  const list = themes().value;
  const index = list.findIndex(t => t.id === theme.id);
  if (index >= 0) list[index] = theme; else list.push(theme);
  if (process.client) localStorage.setItem('themes', JSON.stringify(list));
}

export function deleteTheme(id) {
  const list = themes().value.filter(t => t.id !== id);
  themes().value = list;
  if (process.client) localStorage.setItem('themes', JSON.stringify(list));
}

export function getActiveTheme() {
  const id = activeThemeId().value;
  return themes().value.find(t => t.id === id) || themes().value[0];
}

export function getThemeById(id) {
  if (id === 'one-dark') return undefined;
  return themes().value.find(t => t.id === id);
}
