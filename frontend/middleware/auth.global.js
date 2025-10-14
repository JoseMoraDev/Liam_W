// middleware/auth.global.js
export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('token').value

  const rutasProtegidas = ['/previsiones','/ajustes','/perfil']

  const requiereAuth =
    rutasProtegidas.includes(to.path)
    || to.path.startsWith('/previsiones/')
    || to.path.startsWith('/meteo/')
    || to.path.startsWith('/aire/')
    || to.path.startsWith('/trafico/')
    || to.path.startsWith('/ajustes/')
    || to.path.startsWith('/avanzado/')

  if (!token && requiereAuth) return navigateTo('/login')
  if (token && (to.path === '/' || to.path === '/login')) return navigateTo('/previsiones')
})
