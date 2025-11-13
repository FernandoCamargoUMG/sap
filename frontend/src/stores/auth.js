import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'

export const useAuthStore = defineStore('auth', () => {
  // State
  const usuario = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const isAuthenticated = computed(() => !!usuario.value)
  const nombreUsuario = computed(() => usuario.value?.nombre || '')
  const rolUsuario = computed(() => usuario.value?.rol?.nombre || '')

  // Actions
  async function login(correo, password) {
    loading.value = true
    error.value = null
    
    try {
      const response = await authService.login(correo, password)
      
      if (response.success) {
        usuario.value = response.data.usuario
        // Guardar en sessionStorage
        sessionStorage.setItem('usuario', JSON.stringify(response.data.usuario))
        return true
      } else {
        error.value = response.message || 'Error al iniciar sesión'
        return false
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al conectar con el servidor'
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true
    
    try {
      await authService.logout()
    } catch (err) {
      console.error('Error al cerrar sesión:', err)
    } finally {
      usuario.value = null
      sessionStorage.removeItem('usuario')
      loading.value = false
    }
  }

  async function checkAuth() {
    // Verificar si hay usuario en sessionStorage
    const storedUser = sessionStorage.getItem('usuario')
    
    if (storedUser) {
      try {
        usuario.value = JSON.parse(storedUser)
        
        // Verificar con el servidor que la sesión sigue activa
        const response = await authService.me()
        if (response.success) {
          usuario.value = response.data
          sessionStorage.setItem('usuario', JSON.stringify(response.data))
        }
      } catch (err) {
        // Si falla, limpiar la sesión
        usuario.value = null
        sessionStorage.removeItem('usuario')
      }
    }
  }

  function initializeAuth() {
    const storedUser = sessionStorage.getItem('usuario')
    if (storedUser) {
      usuario.value = JSON.parse(storedUser)
    }
  }

  return {
    // State
    usuario,
    loading,
    error,
    // Getters
    isAuthenticated,
    nombreUsuario,
    rolUsuario,
    // Actions
    login,
    logout,
    checkAuth,
    initializeAuth
  }
})
