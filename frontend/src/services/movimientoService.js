import apiClient from './api'

export default {
  /**
   * Obtener todos los movimientos
   */
  getAll() {
    return apiClient.get('/movimientos')
  },

  /**
   * Obtener un movimiento específico con detalles
   */
  getById(id) {
    return apiClient.get(`/movimientos/${id}`)
  },

  /**
   * Crear nuevo movimiento con detalles
   */
  create(data) {
    return apiClient.post('/movimientos', data)
  },

  /**
   * Actualizar movimiento
   */
  update(id, data) {
    return apiClient.put(`/movimientos/${id}`, data)
  },

  /**
   * Anular movimiento (eliminar lógicamente y reversar saldos)
   */
  delete(id) {
    return apiClient.delete(`/movimientos/${id}`)
  },

  /**
   * Restaurar movimiento anulado
   * NOTA: No hay endpoint restore para movimientos en api.php
   */
  restore(id) {
    console.warn('⚠️ Endpoint PATCH /movimientos/{id}/restore no existe en el backend')
    return Promise.reject(new Error('Endpoint no implementado'))
  },

  /**
   * Obtener movimientos por tipo
   */
  getByTipo(tipo) {
    return apiClient.get(`/movimientos?tipo=${tipo}`)
  },

  /**
   * Obtener movimientos por renglón
   */
  getByRenglon(renglonId) {
    return apiClient.get(`/movimientos?renglon_id=${renglonId}`)
  },

  /**
   * Tipos de movimiento disponibles (datos estáticos del frontend)
   * NOTA: Estos tipos están definidos en el frontend para la UI,
   * el backend los valida en MovimientoRequest.php
   */
  getTipos() {
    return {
      data: [
        { id: 'ampliacion', nombre: 'Ampliación', descripcion: 'Incrementa presupuesto y saldo disponible' },
        { id: 'reduccion', nombre: 'Reducción', descripcion: 'Reduce presupuesto y saldo disponible' },
        { id: 'compromiso', nombre: 'Compromiso', descripcion: 'Reserva recursos (reduce saldo disponible)' },
        { id: 'devengado', nombre: 'Devengado', descripcion: 'Ejecuta gasto comprometido' },
        { id: 'egreso', nombre: 'Egreso', descripcion: 'Pago efectivo' },
        { id: 'liberacion', nombre: 'Liberación', descripcion: 'Libera recursos comprometidos' },
        { id: 'reintegro', nombre: 'Reintegro', descripcion: 'Devuelve fondos al renglón' }
      ]
    }
  }
}