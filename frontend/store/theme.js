// Estado global de temas
export const themes = () =>
  useState('themes', () => [
    {
      id: 'dark-default',
      name: 'Oscuro',
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
    },
    {
      id: 'one-dark',
      name: 'One Dark',
      vars: {
        '--color-bg': '#282c34',
        '--color-surface': '#21252b',
        '--color-surface-weak': '#21252BCC',
        '--color-text': '#abb2bf',
        '--color-text-muted': '#7f848e',
        '--color-border': '#3a3f4b',
        '--color-primary': '#61afef',
        '--color-secondary': '#c678dd',
        '--color-success': '#98c379',
        '--color-warning': '#e5c07b',
        '--color-danger': '#e06c75',
        '--color-info': '#56b6c2',
        '--color-overlay-weak': '#FFFFFF0D',
        '--color-overlay-strong': '#FFFFFF1A'
      }
    }
  ]);

export const activeThemeId = () => useState('activeThemeId', () => 'dark-default');

export function setActiveTheme(id) {
  activeThemeId().value = id;
  if (process.client) localStorage.setItem('activeThemeId', id);
}

export function upsertTheme(theme) {
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
