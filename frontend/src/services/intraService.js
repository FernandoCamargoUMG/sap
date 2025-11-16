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
  },

  /**
   * Obtener renglones disponibles para transferencias
   */
  getRenglonesDisponibles(anio = null) {
    const params = anio ? `?anio=${anio}` : ''
    return apiClient.get(`/intras/renglones-disponibles${params}`)
  },

  /**
   * Subir documento a una transferencia
   */
  uploadDocument(id, file) {
    const formData = new FormData()
    formData.append('documento', file)
    
    return apiClient.post(`/intras/${id}/documento`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  /**
   * Descargar documento de una transferencia
   */
  downloadDocument(documentoId) {
    return apiClient.get(`/intras/documento/${documentoId}`, {
      responseType: 'blob'
    })
  },

  /**
   * Eliminar documento de una transferencia
   */
  deleteDocument(documentoId) {
    return apiClient.delete(`/intras/documento/${documentoId}`)
  }
}