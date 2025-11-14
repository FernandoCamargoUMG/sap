import apiClient from './api'

export default {
  /**
   * Registrar una nueva ejecución
   */
  registrarEjecucion(data) {
    return apiClient.post('/ejecuciones', data)
  },

  /**
   * Obtener ejecuciones de un renglón
   */
  getEjecucionesPorRenglon(renglonId) {
    return apiClient.get(`/ejecuciones/renglon/${renglonId}`)
  },

  /**
   * Obtener presupuestos disponibles para ejecutar de un renglón
   */
  getPresupuestosDisponibles(renglonId) {
    return apiClient.get(`/ejecuciones/presupuestos-disponibles/${renglonId}`)
  }
}