import { axiosClient } from '~/axiosConfig'

export const useMunicipios = () => {

  const getByProvincia = async (cpro) => {
    try {
      const res = await axiosClient.get(`/municipios/${cpro}`)
      return res.data
    } catch (error) {
      console.error('Error al cargar municipios:', error)
      return [] // retorna array vacío si hay algún error
    }
  }

  return { getByProvincia }
}
