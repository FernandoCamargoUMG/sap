<template>
  <div class="min-h-screen bg-gradient-cfag flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Patrón de fondo decorativo -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-0 left-0 w-96 h-96 bg-secondary-500 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary-500 rounded-full blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10">
      <!-- Header con logo -->
      <div class="text-center">
        <div class="mx-auto h-24 w-24 bg-white rounded-full flex items-center justify-center shadow-2xl ring-4 ring-secondary-500 ring-opacity-50">
          <svg class="h-14 w-14 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h2 class="mt-6 text-4xl font-extrabold text-white drop-shadow-lg">
          SAP
        </h2>
        <p class="mt-2 text-xl font-semibold text-secondary-300">
          Sistema de Administración Presupuestaria
        </p>
        <p class="mt-1 text-sm text-blue-100">
          Universidad Mariano Gálvez de Guatemala
        </p>
      </div>

      <!-- Formulario -->
      <div class="bg-white rounded-2xl shadow-2xl p-8 backdrop-blur-sm">
        <form class="space-y-6" @submit.prevent="handleLogin">
          <!-- Alert de error -->
          <div v-if="authStore.error" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg animate-pulse">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-red-700">{{ authStore.error }}</p>
              </div>
            </div>
          </div>

          <!-- Campo Email -->
          <div>
            <label for="correo" class="block text-sm font-semibold text-gray-700 mb-2">
              Correo Electrónico
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                </svg>
              </div>
              <input
                id="correo"
                v-model="form.correo"
                type="email"
                required
                class="pl-10 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-primary-200 focus:border-primary-500 outline-none transition-all duration-200 hover:border-primary-300"
                placeholder="correo@ejemplo.com"
                :disabled="authStore.loading"
              >
            </div>
          </div>

          <!-- Campo Password -->
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
              Contraseña
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </div>
              <input
                id="password"
                v-model="form.password"
                type="password"
                required
                class="pl-10 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-primary-200 focus:border-primary-500 outline-none transition-all duration-200 hover:border-primary-300"
                placeholder="••••••••"
                :disabled="authStore.loading"
              >
            </div>
          </div>

          <!-- Botón Submit -->
          <div>
            <button
              type="submit"
              class="w-full flex justify-center items-center px-4 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-cfag hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
              :disabled="authStore.loading"
            >
              <svg v-if="authStore.loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              <svg v-else class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              {{ authStore.loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
            </button>
          </div>

          <!-- Credenciales de prueba -->
          <!--<div class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-yellow-50 rounded-xl border-2 border-secondary-200">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-secondary-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-xs font-bold text-gray-800 mb-1">Credenciales de prueba:</p>
                <p class="text-xs text-gray-700">
                  <strong class="text-primary-700">Correo:</strong> administrador@contabilidad.com<br>
                  <strong class="text-primary-700">Contraseña:</strong> admin123
                </p>
              </div>
            </div>
          </div>-->
        </form>
      </div>

      <!-- Footer -->
      <p class="text-center text-sm text-white drop-shadow-lg">
        © 2025 Universidad Mariano Gálvez de Guatemala
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  correo: '',
  password: ''
})

const handleLogin = async () => {
  const success = await authStore.login(form.value.correo, form.value.password)
  
  if (success) {
    router.push('/dashboard')
  }
}
</script>
