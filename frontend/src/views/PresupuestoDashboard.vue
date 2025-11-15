<template>
  <AppLayout>
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-black text-gray-900 flex items-center">
            <span class="bg-gradient-cfag bg-clip-text text-transparent">Dashboard Presupuestario</span>
          </h1>
          <p class="text-gray-600 mt-2">Análisis y resumen ejecutivo del presupuesto</p>
        </div>
        <div class="flex space-x-3">
          <button 
            @click="$router.push('/presupuestos')" 
            class="bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:bg-gray-50 transform hover:-translate-y-0.5 transition-all duration-200 flex items-center"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            Ver Presupuestos
          </button>
          <button 
            @click="loadDashboard" 
            :disabled="loading"
            class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center disabled:opacity-50"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            {{ loading ? 'Actualizando...' : 'Actualizar' }}
          </button>
        </div>
      </div>

      <!-- Alertas -->
      <div v-if="alert.show" :class="[
        'mb-6 p-4 rounded-xl border-l-4 flex items-center justify-between animate-pulse',
        alert.type === 'success' ? 'bg-green-50 border-green-500 text-green-800' : 'bg-red-50 border-red-500 text-red-800'
      ]">
        <div class="flex items-center">
          <svg v-if="alert.type === 'success'" class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="font-semibold">{{ alert.message }}</span>
        </div>
        <button @click="alert.show = false">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="text-center">
          <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-primary-600 mx-auto mb-4"></div>
          <p class="text-gray-600 font-semibold">Cargando dashboard presupuestario...</p>
        </div>
      </div>

      <!-- Dashboard Content -->
      <div v-else-if="dashboardData">
        <PresupuestoDashboard 
          :resumen="dashboardData.resumen"
          :renglones="dashboardData.renglones"
          :loading="loading"
        />
      </div>

      <!-- Estado vacío -->
      <div v-else class="text-center py-20">
        <div class="bg-white rounded-2xl shadow-xl p-12 border border-gray-200">
          <svg class="mx-auto h-24 w-24 text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <h3 class="text-2xl font-bold text-gray-900 mb-3">No hay datos disponibles</h3>
          <p class="text-gray-600 mb-6">No se encontraron datos para mostrar en el dashboard presupuestario.</p>
          <button 
            @click="$router.push('/presupuestos')"
            class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
          >
            Ir a Presupuestos
          </button>
        </div>
      </div>
    </main>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import PresupuestoDashboard from '@/components/PresupuestoDashboard.vue'
import presupuestoService from '@/services/presupuestoService'

// Estado
const loading = ref(true)
const dashboardData = ref(null)

// Alertas
const alert = ref({
  show: false,
  type: 'success',
  message: ''
})

// Cargar dashboard
const loadDashboard = async () => {
  try {
    loading.value = true
    
    const response = await presupuestoService.getDashboard()
    if (response.data && response.data.success && response.data.data) {
      dashboardData.value = response.data.data
      console.log('Dashboard presupuestario cargado:', dashboardData.value)
    } else {
      dashboardData.value = null
      showAlert('error', 'No se pudieron cargar los datos del dashboard')
    }
  } catch (error) {
    console.error('Error al cargar dashboard:', error)
    showAlert('error', 'Error al conectar con el backend: ' + (error.response?.data?.message || error.message))
    dashboardData.value = null
  } finally {
    loading.value = false
  }
}

// Mostrar alerta
const showAlert = (type, message) => {
  alert.value = { show: true, type, message }
  setTimeout(() => {
    alert.value.show = false
  }, 5000)
}

// Cargar datos al montar
onMounted(() => {
  loadDashboard()
})
</script>