import apiClient from './api'

export default {
  /**
   * Obtener todos los compromisos (CUR)
   */
  getAll() {
    return apiClient.get('/cur')
  },

  /**
   * Obtener un compromiso específico
   */
  getById(id) {
    return apiClient.get(`/cur/${id}`)
  },

  /**
   * Crear nuevo compromiso de pago
   */
  create(data) {
    // Si es FormData (contiene archivos), usar Content-Type multipart
    if (data instanceof FormData) {
      return apiClient.post('/cur', data, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
    }
    
    // Si es objeto normal, usar JSON
    return apiClient.post('/cur', data)
  },

  /**
   * Actualizar compromiso
   */
  update(id, data) {
    return apiClient.put(`/cur/${id}`, data)
  },

  /**
   * Anular compromiso (soft delete)
   */
  delete(id) {
    return apiClient.delete(`/cur/${id}`)
  },

  /**
   * Restaurar compromiso anulado
   */
  restore(id) {
    return apiClient.patch(`/cur/${id}/restore`)
  },

  /**
   * Obtener compromisos por proveedor
   */
  getByProveedor(proveedorId) {
    return apiClient.get(`/cur?proveedor_id=${proveedorId}`)
  },

  /**
   * Obtener compromisos por renglón
   */
  getByRenglon(renglonId) {
    return apiClient.get(`/cur?renglon_id=${renglonId}`)
  },

  /**
   * Agregar documentos a un CUR existente
   */
  addDocuments(curId, formData) {
    return apiClient.post(`/cur/${curId}/documentos`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  /**
   * Subir o actualizar documento de un CUR
   */
  uploadDocument(curId, formData) {
    return apiClient.post(`/cur/${curId}/documento`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  /**
   * Eliminar documento de un CUR
   */
  deleteDocument(curId) {
    return apiClient.delete(`/cur/${curId}/documento`)
  }
}