import apiClient from './api'

export default {
  /**
   * Obtener todos los proveedores activos
   */
  getAll() {
    return apiClient.get('/proveedores')
  },

  /**
   * Obtener un proveedor específico
   */
  getById(id) {
    return apiClient.get(`/proveedores/${id}`)
  },

  /**
   * Crear nuevo proveedor
   */
  create(data) {
    return apiClient.post('/proveedores', data)
  },

  /**
   * Actualizar proveedor
   */
  update(id, data) {
    return apiClient.put(`/proveedores/${id}`, data)
  },

  /**
   * Eliminar proveedor (soft delete)
   */
  delete(id) {
    return apiClient.delete(`/proveedores/${id}`)
  },

  /**
   * Restaurar proveedor eliminado
   */
  restore(id) {
    return apiClient.patch(`/proveedores/${id}/restore`)
  }
}
