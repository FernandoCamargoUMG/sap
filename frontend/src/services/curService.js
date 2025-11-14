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
  }
}