import apiClient from './api'

export default {
  /**
   * Obtener todas las facturas
   */
  getAll() {
    return apiClient.get('/facturas')
  },

  /**
   * Obtener una factura específica con detalles
   */
  getById(id) {
    return apiClient.get(`/facturas/${id}`)
  },

  /**
   * Crear nueva factura con detalles
   */
  create(data) {
    return apiClient.post('/facturas', data)
  },

  /**
   * Actualizar factura
   */
  update(id, data) {
    return apiClient.put(`/facturas/${id}`, data)
  },

  /**
   * Eliminar factura (soft delete)
   */
  delete(id) {
    return apiClient.delete(`/facturas/${id}`)
  },

  /**
   * Restaurar factura eliminada
   */
  restore(id) {
    return apiClient.patch(`/facturas/${id}/restore`)
  },

  /**
   * Obtener facturas por proveedor
   */
  getByProveedor(proveedorId) {
    return apiClient.get(`/facturas?proveedor_id=${proveedorId}`)
  },

  /**
   * Obtener facturas por rango de fechas
   */
  getByDateRange(fechaInicio, fechaFin) {
    return apiClient.get(`/facturas?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`)
  }
}