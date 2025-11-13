import api from './api'

export const authService = {
  /**
   * Iniciar sesión
   * @param {string} correo 
   * @param {string} password 
   * @returns {Promise}
   */
  async login(correo, password) {
    const response = await api.post('/auth/login', {
      correo,
      contraseña: password  // El backend espera 'contraseña'
    })
    return response.data
  },

  /**
   * Cerrar sesión
   * @returns {Promise}
   */
  async logout() {
    const response = await api.post('/auth/logout')
    return response.data
  },

  /**
   * Obtener usuario actual
   * @returns {Promise}
   */
  async me() {
    const response = await api.get('/auth/me')
    return response.data
  }
}
