import axios from 'axios'

// Configuración base de Axios
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true // Importante para enviar cookies de sesión
})

// Interceptor de respuesta para manejar errores globalmente
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      // El servidor respondió con un código de estado fuera del rango 2xx
      switch (error.response.status) {
        case 401:
          // No autenticado - redirigir al login
          console.error('No autenticado')
          sessionStorage.removeItem('usuario')
          window.location.href = '/login'
          break
        case 403:
          console.error('No tienes permisos para realizar esta acción')
          break
        case 404:
          console.error('Recurso no encontrado')
          break
        case 500:
          console.error('Error interno del servidor')
          break
        default:
          console.error('Error en la petición:', error.response.data.message)
      }
    } else if (error.request) {
      // La petición fue hecha pero no hubo respuesta
      console.error('No se pudo conectar con el servidor')
    } else {
      // Algo sucedió al configurar la petición
      console.error('Error:', error.message)
    }
    return Promise.reject(error)
  }
)

export default api
