import apiClient from './api'

export default {
  /**
   * Obtener todas las transferencias (INTRAS)
   */
  getAll() {
    return apiClient.get('/intras')
  },

  /**
   * Obtener una transferencia específica
   */
  getById(id) {
    return apiClient.get(`/intras/${id}`)
  },

  /**
   * Crear nueva transferencia entre renglones
   */
  create(data) {
    return apiClient.post('/intras', data)
  },

  /**
   * Actualizar transferencia
   */
  update(id, data) {
    return apiClient.put(`/intras/${id}`, data)
  },

  /**
   * Anular transferencia (eliminar y reversar movimientos)
   */
  delete(id) {
    return apiClient.delete(`/intras/${id}`)
  },

  /**
   * Restaurar transferencia anulada
   */
  restore(id) {
    return apiClient.patch(`/intras/${id}/restore`)
  }
}