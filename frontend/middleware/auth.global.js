// middleware/auth.global.js
export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('token').value
  const logged = (typeof window !== 'undefined') ? (useState('userLoggedIn').value || false) : false

  const rutasProtegidas = ['/previsiones', '/ajustes', '/perfil']

  const requiereAuth =
    rutasProtegidas.includes(to.path)
    || to.path.startsWith('/previsiones/')
    || to.path.startsWith('/meteo/')
    || to.path.startsWith('/aire/')
    || to.path.startsWith('/trafico/')
    || to.path.startsWith('/ajustes/')
    || to.path.startsWith('/avanzado/')
    || to.path.startsWith('/ubicacion')

  if (!(token || logged) && requiereAuth) return navigateTo('/login')
  if ((token || logged) && (to.path === '/' || to.path === '/login')) return navigateTo('/previsiones')
})
