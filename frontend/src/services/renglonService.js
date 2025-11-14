import apiClient from './api'

export default {
  /**
   * Obtener todos los renglones activos
   */
  getAll() {
    return apiClient.get('/renglones')
  },

  /**
   * Obtener un renglón específico
   */
  getById(id) {
    return apiClient.get(`/renglones/${id}`)
  },

  /**
   * Crear nuevo renglón
   */
  create(data) {
    return apiClient.post('/renglones', data)
  },

  /**
   * Actualizar renglón
   */
  update(id, data) {
    return apiClient.put(`/renglones/${id}`, data)
  },

  /**
   * Eliminar renglón (soft delete)
   */
  delete(id) {
    return apiClient.delete(`/renglones/${id}`)
  },

  /**
   * Restaurar renglón eliminado
   */
  restore(id) {
    return apiClient.patch(`/renglones/${id}/restore`)
  }
}
