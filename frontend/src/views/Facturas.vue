<template>
  <AppLayout>
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-black text-gray-900 flex items-center">
            <span class="bg-gradient-cfag bg-clip-text text-transparent">Facturas</span>
          </h1>
          <p class="text-gray-600 mt-2">Gestiona las facturas y su vinculación con proveedores</p>
        </div>
        <button @click="openCreateModal" class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nueva Factura
        </button>
      </div>

      <!-- Filtros -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
        <div class="flex flex-wrap gap-4 items-center">
          <div class="flex-1 min-w-48">
            <label class="block text-sm font-bold text-gray-700 mb-2">Proveedor</label>
            <select v-model="filtroProveedor" @change="filterByProveedor" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
              <option value="">Todos los proveedores</option>
              <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">{{ proveedor.nombre }}</option>
            </select>
          </div>
          <div class="flex-1 min-w-48">
            <label class="block text-sm font-bold text-gray-700 mb-2">Buscar</label>
            <input v-model="searchTerm" type="text" placeholder="Buscar por número de factura..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
          </div>
          <div class="flex items-end">
            <button @click="clearFilters" class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">
              Limpiar filtros
            </button>
          </div>
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
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-primary-600"></div>
      </div>

      <!-- Tabla -->
      <div v-else class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-cfag">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Factura</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Proveedor</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Total</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Renglones</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Estado</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="factura in filteredFacturas" :key="factura.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="text-sm font-bold text-gray-900">{{ factura.numero_factura }}</div>
                  <div class="text-xs text-gray-500">Serie: {{ factura.serie_factura || '-' }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-bold text-gray-900">{{ factura.proveedor?.nombre }}</div>
                  <div class="text-xs text-gray-500">NIT: {{ factura.proveedor?.nit }}</div>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span class="text-sm text-gray-900">{{ formatDate(factura.fecha_factura) }}</span>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span class="text-lg font-black text-green-600">Q{{ formatMoney(factura.total_factura) }}</span>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">
                    {{ factura.detalles?.length || 0 }} items
                  </span>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span :class="[
                    'px-3 py-1 rounded-full text-xs font-bold',
                    factura.estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ factura.estado === 1 ? 'Activa' : 'Anulada' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center space-x-2">
                    <button @click="viewDetails(factura)" class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors" title="Ver detalles">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button @click="openEditModal(factura)" class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors" title="Editar">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="confirmDelete(factura)" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors" title="Eliminar">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredFacturas.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <svg class="h-20 w-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-xl font-semibold text-gray-600 mb-2">No hay facturas</p>
                    <p class="text-gray-500">Crea la primera factura para comenzar</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal Crear/Editar (simplificado por brevedad) -->
    <!-- Se incluiría el modal completo con formulario para crear/editar facturas -->
    
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import facturaService from '@/services/facturaService'
import proveedorService from '@/services/proveedorService'
import renglonService from '@/services/renglonService'

// Estado principal
const loading = ref(true)
const facturas = ref([])
const proveedores = ref([])
const renglones = ref([])

// Filtros
const filtroProveedor = ref('')
const searchTerm = ref('')

// Estados del modal
const showModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)

// Formulario simplificado
const form = ref({
  numero_factura: '',
  serie_factura: '',
  fecha_factura: new Date().toISOString().split('T')[0],
  proveedor_id: '',
  descripcion: '',
  detalles: []
})

// Alertas
const alert = ref({
  show: false,
  type: 'success',
  message: ''
})

// Computadas
const filteredFacturas = computed(() => {
  let filtered = facturas.value

  if (filtroProveedor.value) {
    filtered = filtered.filter(f => f.proveedor_id == filtroProveedor.value)
  }

  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase()
    filtered = filtered.filter(f => 
      f.numero_factura.toLowerCase().includes(term) ||
      f.serie_factura?.toLowerCase().includes(term)
    )
  }

  return filtered
})

// Cargar datos iniciales
onMounted(async () => {
  await Promise.all([
    loadFacturas(),
    loadProveedores(),
    loadRenglones()
  ])
})

// Funciones de carga
const loadFacturas = async () => {
  try {
    loading.value = true
    const response = await facturaService.getAll()
    facturas.value = response.data.facturas || response.data
  } catch (error) {
    console.error('Error al cargar facturas:', error)
    showAlert('error', 'Error al cargar facturas')
  } finally {
    loading.value = false
  }
}

const loadProveedores = async () => {
  try {
    const response = await proveedorService.getAll()
    proveedores.value = response.data.proveedores || response.data
  } catch (error) {
    console.error('Error al cargar proveedores:', error)
  }
}

const loadRenglones = async () => {
  try {
    const response = await renglonService.getAll()
    renglones.value = response.data.renglones || response.data
  } catch (error) {
    console.error('Error al cargar renglones:', error)
  }
}

// Funciones de modal
const openCreateModal = () => {
  isEditing.value = false
  form.value = {
    numero_factura: '',
    serie_factura: '',
    fecha_factura: new Date().toISOString().split('T')[0],
    proveedor_id: '',
    descripcion: '',
    detalles: []
  }
  showModal.value = true
}

const openEditModal = (factura) => {
  // Implementar edición
}

const viewDetails = (factura) => {
  // Implementar vista de detalles
}

const confirmDelete = (factura) => {
  // Implementar confirmación de eliminación
}

// Funciones de filtros
const filterByProveedor = () => {
  // La computada se encarga del filtrado
}

const clearFilters = () => {
  filtroProveedor.value = ''
  searchTerm.value = ''
}

// Utilidades
const showAlert = (type, message) => {
  alert.value = { show: true, type, message }
  setTimeout(() => {
    alert.value.show = false
  }, 5000)
}

const formatMoney = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('es-GT', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>