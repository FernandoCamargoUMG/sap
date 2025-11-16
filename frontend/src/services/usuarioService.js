import apiClient from './api'

export default {
  /**
   * Obtener todos los usuarios activos
   */
  getAll() {
    return apiClient.get('/usuarios')
  },

  /**
   * Obtener un usuario específico
   */
  getById(id) {
    return apiClient.get(`/usuarios/${id}`)
  },

  /**
   * Crear nuevo usuario
   */
  create(data) {
    return apiClient.post('/usuarios', data)
  },

  /**
   * Actualizar usuario
   */
  update(id, data) {
    return apiClient.put(`/usuarios/${id}`, data)
  },

  /**
   * Desactivar usuario (cambiar estado a inactivo)
   */
  delete(id) {
    return apiClient.delete(`/usuarios/${id}`)
  },

  /**
   * Activar usuario (cambiar estado a activo)
   */
  activate(id) {
    return apiClient.post(`/usuarios/${id}/activate`)
  },

  /**
   * Obtener todos los roles disponibles
   */
  getRoles() {
    return apiClient.get('/usuarios/roles')
  }
}