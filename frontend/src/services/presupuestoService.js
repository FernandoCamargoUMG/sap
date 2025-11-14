import apiClient from './api'

export default {
  /**
   * Obtener dashboard de presupuestos con lógica de Guatemala
   */
  getDashboard() {
    return apiClient.get('/presupuestos')
  },

  /**
   * Obtener lista de presupuestos individuales
   */
  getAll() {
    return apiClient.get('/presupuestos/lista')
  },

  /**
   * Obtener un presupuesto específico con detalles
   */
  getById(id) {
    return apiClient.get(`/presupuestos/${id}`)
  },

  /**
   * Crear nuevo presupuesto con detalles (Guatemala)
   */
  create(data) {
    return apiClient.post('/presupuestos', data)
  },

  /**
   * Actualizar presupuesto
   */
  update(id, data) {
    return apiClient.put(`/presupuestos/${id}`, data)
  },

  /**
   * Eliminar presupuesto (soft delete)
   */
  delete(id) {
    return apiClient.delete(`/presupuestos/${id}`)
  },

  /**
   * Ejecutar gasto contra presupuesto (lógica Guatemala)
   */
  ejecutarGasto(data) {
    return apiClient.post('/presupuestos/ejecutar-gasto', data)
  },

  /**
   * Obtener presupuestos disponibles para ejecución
   */
  getDisponibles() {
    return apiClient.get('/presupuestos/disponibles')
  },

  /**
   * Obtener presupuestos por ejercicio fiscal
   */
  getByEjercicio(ejercicio) {
    return apiClient.get(`/presupuestos?ejercicio_fiscal=${ejercicio}`)
  },

  /**
   * Obtener saldo disponible de un presupuesto específico
   */
  getSaldo(id) {
    return apiClient.get(`/presupuestos/${id}/saldo`)
  }
}