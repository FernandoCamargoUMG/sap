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
   * Eliminar usuario (soft delete)
   */
  delete(id) {
    return apiClient.delete(`/usuarios/${id}`)
  },

  /**
   * Restaurar usuario eliminado
   */
  restore(id) {
    return apiClient.post(`/usuarios/${id}/restore`)
  },

  /**
   * Obtener todos los roles disponibles
   * TEMPORAL: Datos hardcodeados hasta que se implemente RolesController
   */
  getRoles() {
    // TODO: Implementar endpoint GET /api/roles en el backend
    console.warn('⚠️ Endpoint /roles no implementado, usando datos temporales')
    return Promise.resolve({
      data: {
        roles: [
          { id: 1, nombre: 'administrador', descripcion: 'Administrador del sistema' },
          { id: 2, nombre: 'editor', descripcion: 'Editor con permisos de escritura' },
          { id: 3, nombre: 'lector', descripcion: 'Solo lectura' }
        ]
      }
    })
  }
}