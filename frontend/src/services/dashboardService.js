import apiClient from './api'

export default {
  /**
   * Obtener estadísticas del dashboard
   */
  getEstadisticas() {
    return apiClient.get('/dashboard/estadisticas')
  },

  /**
   * Obtener actividad reciente
   */
  getActividadReciente() {
    return apiClient.get('/dashboard/actividad-reciente')
  }
}