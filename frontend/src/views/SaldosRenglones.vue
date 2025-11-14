<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Saldos de Renglones</h1>
        <p class="text-gray-600">Seguimiento de saldos y ejecución presupuestaria</p>
      </div>
      <div class="flex space-x-3">
        <router-link 
          to="/dashboard"
          class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center space-x-2"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span>Dashboard</span>
        </router-link>
        <button 
          @click="loadRenglones"
          :disabled="loading" 
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center space-x-2"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>{{ loading ? 'Cargando...' : 'Actualizar' }}</span>
        </button>
      </div>
    </div>

    <!-- Filtros -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar renglón</label>
        <input
          v-model="filtros.busqueda"
          type="text"
          placeholder="Código o nombre..."
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
        <select
          v-model="filtros.estado"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Todos</option>
          <option value="disponible">Con saldo disponible</option>
          <option value="asignado">Totalmente asignado</option>
          <option value="ejecutado">Totalmente ejecutado</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Ordenar por</label>
        <select
          v-model="filtros.orden"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="codigo">Código</option>
          <option value="nombre">Nombre</option>
          <option value="saldo_disponible">Saldo disponible</option>
          <option value="porcentaje_ejecutado">% Ejecutado</option>
        </select>
      </div>
    </div>

    <!-- Resumen -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-blue-50 p-4 rounded-lg">
        <div class="text-sm text-blue-600 font-medium">Total Inicial</div>
        <div class="text-2xl font-bold text-blue-900">Q {{ formatMoney(resumen.total_inicial) }}</div>
      </div>
      <div class="bg-green-50 p-4 rounded-lg">
        <div class="text-sm text-green-600 font-medium">Total Asignado</div>
        <div class="text-2xl font-bold text-green-900">Q {{ formatMoney(resumen.total_asignado) }}</div>
      </div>
      <div class="bg-yellow-50 p-4 rounded-lg">
        <div class="text-sm text-yellow-600 font-medium">Total Ejecutado</div>
        <div class="text-2xl font-bold text-yellow-900">Q {{ formatMoney(resumen.total_ejecutado) }}</div>
      </div>
      <div class="bg-purple-50 p-4 rounded-lg">
        <div class="text-sm text-purple-600 font-medium">Saldo Disponible</div>
        <div class="text-2xl font-bold text-purple-900">Q {{ formatMoney(resumen.total_disponible) }}</div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Cargando renglones...</p>
    </div>

    <!-- Tabla de renglones -->
    <div v-else class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Renglón
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Monto Inicial
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Asignado
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ejecutado
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Disponible
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                % Ejecución
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="renglon in renglonesFiltrados" :key="renglon.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ renglon.codigo }}</div>
                  <div class="text-sm text-gray-500">{{ renglon.nombre }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                Q {{ formatMoney(renglon.monto_inicial) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">Q {{ formatMoney(renglon.monto_asignado) }}</div>
                <div class="text-xs text-gray-500">
                  {{ renglon.porcentaje_asignado.toFixed(1) }}% del inicial
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">Q {{ formatMoney(renglon.monto_ejecutado) }}</div>
                <div class="text-xs text-gray-500">
                  {{ renglon.porcentaje_ejecutado.toFixed(1) }}% del asignado
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium" :class="{
                  'text-green-600': renglon.saldo_disponible > 0,
                  'text-red-600': renglon.saldo_disponible <= 0
                }">
                  Q {{ formatMoney(renglon.saldo_disponible) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                    <div 
                      class="h-2 rounded-full transition-all duration-300"
                      :class="{
                        'bg-green-500': renglon.porcentaje_ejecutado >= 80,
                        'bg-yellow-500': renglon.porcentaje_ejecutado >= 50 && renglon.porcentaje_ejecutado < 80,
                        'bg-red-500': renglon.porcentaje_ejecutado < 50
                      }"
                      :style="{ width: Math.min(renglon.porcentaje_ejecutado, 100) + '%' }"
                    ></div>
                  </div>
                  <span class="text-sm text-gray-600">{{ renglon.porcentaje_ejecutado.toFixed(1) }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button
                  @click="verDetalle(renglon.id)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  Ver detalle
                </button>
                <button
                  @click="registrarEjecucion(renglon.id)"
                  :disabled="renglon.saldo_por_ejecutar <= 0"
                  class="text-green-600 hover:text-green-900 disabled:text-gray-400"
                >
                  Ejecutar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Sin datos -->
      <div v-if="renglonesFiltrados.length === 0" class="text-center py-8">
        <p class="text-gray-500">No se encontraron renglones con los filtros aplicados</p>
      </div>
    </div>

    <!-- Modal de detalle -->
    <div v-if="modalDetalle.show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              Detalle de Saldo - {{ modalDetalle.renglon?.codigo }}
            </h3>
            <button
              @click="cerrarModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div v-if="modalDetalle.loading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>

          <div v-else-if="modalDetalle.data" class="space-y-6">
            <!-- Resumen del renglón -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-gray-900 mb-3">{{ modalDetalle.data.renglon.nombre }}</h4>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-600">Monto inicial:</span>
                  <span class="font-medium ml-2">Q {{ formatMoney(modalDetalle.data.resumen.monto_inicial) }}</span>
                </div>
                <div>
                  <span class="text-gray-600">Saldo disponible:</span>
                  <span class="font-medium ml-2">Q {{ formatMoney(modalDetalle.data.resumen.saldo_disponible) }}</span>
                </div>
                <div>
                  <span class="text-gray-600">Total asignado:</span>
                  <span class="font-medium ml-2">Q {{ formatMoney(modalDetalle.data.resumen.monto_asignado) }}</span>
                </div>
                <div>
                  <span class="text-gray-600">Por ejecutar:</span>
                  <span class="font-medium ml-2">Q {{ formatMoney(modalDetalle.data.resumen.saldo_por_ejecutar) }}</span>
                </div>
              </div>
            </div>

            <!-- Presupuestos asociados -->
            <div v-if="modalDetalle.data.presupuestos.length > 0">
              <h4 class="font-medium text-gray-900 mb-3">Presupuestos Asociados</h4>
              <div class="space-y-2">
                <div
                  v-for="presupuesto in modalDetalle.data.presupuestos"
                  :key="presupuesto.presupuesto_id"
                  class="bg-white p-3 border rounded-lg"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <div class="text-sm font-medium">
                        Presupuesto {{ presupuesto.presupuesto_anio }}/{{ String(presupuesto.presupuesto_mes).padStart(2, '0') }}
                      </div>
                      <div class="text-xs text-gray-500">ID: {{ presupuesto.presupuesto_id }}</div>
                    </div>
                    <div class="text-right">
                      <div class="text-sm">Q {{ formatMoney(presupuesto.monto_asignado) }}</div>
                      <div class="text-xs text-gray-500">{{ presupuesto.porcentaje_ejecucion }}% ejecutado</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-6 text-right">
            <button
              @click="cerrarModal"
              class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de ejecución -->
    <div v-if="modalEjecucion.show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Registrar Ejecución</h3>
            <button @click="cerrarModalEjecucion" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <div v-if="modalEjecucion.cargandoPresupuestos" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Cargando presupuestos disponibles...</p>
          </div>

          <form v-else @submit.prevent="procesarEjecucion" class="space-y-4">
            <div v-if="modalEjecucion.presupuestosDisponibles.length > 1">
              <label class="block text-sm font-medium text-gray-700 mb-2">Presupuesto a ejecutar</label>
              <select
                v-model="modalEjecucion.presupuestoSeleccionado"
                @change="onPresupuestoChange"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Seleccionar presupuesto...</option>
                <option
                  v-for="presupuesto in modalEjecucion.presupuestosDisponibles"
                  :key="presupuesto.id"
                  :value="presupuesto.id"
                >
                  {{ presupuesto.presupuesto_anio }}/{{ String(presupuesto.presupuesto_mes).padStart(2, '0') }} - 
                  Disponible: Q {{ formatMoney(presupuesto.saldo_por_ejecutar) }}
                </option>
              </select>
            </div>

            <div v-else-if="modalEjecucion.presupuestosDisponibles.length === 1" class="bg-blue-50 p-3 rounded-lg">
              <p class="text-sm text-blue-800">
                <strong>Presupuesto:</strong> 
                {{ modalEjecucion.presupuestosDisponibles[0].presupuesto_anio }}/{{ String(modalEjecucion.presupuestosDisponibles[0].presupuesto_mes).padStart(2, '0') }}
              </p>
              <p class="text-sm text-blue-600">
                Saldo disponible: Q {{ formatMoney(modalEjecucion.presupuestosDisponibles[0].saldo_por_ejecutar) }}
              </p>
            </div>

            <div v-if="modalEjecucion.presupuestosDisponibles.length === 0" class="bg-yellow-50 p-3 rounded-lg">
              <p class="text-sm text-yellow-800">No hay presupuestos disponibles para ejecutar en este renglón.</p>
            </div>

            <div v-if="modalEjecucion.maxMonto > 0">
              <label class="block text-sm font-medium text-gray-700 mb-2">Monto a ejecutar</label>
              <input
                v-model.number="modalEjecucion.monto"
                type="number"
                step="0.01"
                min="0.01"
                :max="modalEjecucion.maxMonto"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
              <p class="text-xs text-gray-500 mt-1">
                Máximo disponible: Q {{ formatMoney(modalEjecucion.maxMonto) }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
              <textarea
                v-model="modalEjecucion.descripcion"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Describe el concepto de la ejecución..."
              ></textarea>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="cerrarModalEjecucion"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400"
              >
                Cancelar
              </button>
              <button
                v-if="modalEjecucion.presupuestosDisponibles.length > 0"
                type="submit"
                :disabled="modalEjecucion.procesando || modalEjecucion.maxMonto <= 0"
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 disabled:opacity-50"
              >
                {{ modalEjecucion.procesando ? 'Procesando...' : 'Registrar Ejecución' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useToast } from '@/composables/useToast'
import renglonService from '@/services/renglonService'
import ejecucionService from '@/services/ejecucionService'

const { showToast } = useToast()

// Estado reactivo
const loading = ref(false)
const renglones = ref([])

const filtros = ref({
  busqueda: '',
  estado: '',
  orden: 'codigo'
})

const modalDetalle = ref({
  show: false,
  loading: false,
  renglon: null,
  data: null
})

const modalEjecucion = ref({
  show: false,
  procesando: false,
  cargandoPresupuestos: false,
  renglonId: null,
  presupuestosDisponibles: [],
  presupuestoSeleccionado: null,
  monto: 0,
  maxMonto: 0,
  descripcion: ''
})

// Computed properties
const renglonesFiltrados = computed(() => {
  console.log('Computing renglonesFiltrados:', renglones.value.length, 'renglones')
  console.log('Filtros actuales:', filtros.value)
  
  let resultado = [...renglones.value]
  console.log('Resultado inicial:', resultado.length, 'items')

  // Filtro por búsqueda
  if (filtros.value.busqueda) {
    const busqueda = filtros.value.busqueda.toLowerCase()
    const beforeFilter = resultado.length
    resultado = resultado.filter(r => 
      r.codigo.toLowerCase().includes(busqueda) ||
      r.nombre.toLowerCase().includes(busqueda)
    )
    console.log('Después filtro búsqueda:', beforeFilter, '->', resultado.length)
  }

  // Filtro por estado
  if (filtros.value.estado) {
    const beforeFilter = resultado.length
    resultado = resultado.filter(r => {
      switch (filtros.value.estado) {
        case 'disponible':
          return r.saldo_disponible > 0
        case 'asignado':
          return r.saldo_disponible <= 0 && r.saldo_por_ejecutar > 0
        case 'ejecutado':
          return r.saldo_por_ejecutar <= 0
        default:
          return true
      }
    })
    console.log('Después filtro estado:', beforeFilter, '->', resultado.length)
  }

  // Ordenamiento
  resultado.sort((a, b) => {
    switch (filtros.value.orden) {
      case 'codigo':
        return a.codigo.localeCompare(b.codigo)
      case 'nombre':
        return a.nombre.localeCompare(b.nombre)
      case 'saldo_disponible':
        return b.saldo_disponible - a.saldo_disponible
      case 'porcentaje_ejecutado':
        return b.porcentaje_ejecutado - a.porcentaje_ejecutado
      default:
        return 0
    }
  })

  console.log('Resultado final filtrado:', resultado.length, 'items')
  return resultado
})

const resumen = computed(() => {
  return renglones.value.reduce((acc, r) => ({
    total_inicial: acc.total_inicial + r.monto_inicial,
    total_asignado: acc.total_asignado + r.monto_asignado,
    total_ejecutado: acc.total_ejecutado + r.monto_ejecutado,
    total_disponible: acc.total_disponible + r.saldo_disponible
  }), {
    total_inicial: 0,
    total_asignado: 0,
    total_ejecutado: 0,
    total_disponible: 0
  })
})

// Métodos
const formatMoney = (amount) => {
  return new Intl.NumberFormat('es-GT', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const loadRenglones = async () => {
  loading.value = true
  try {
    const response = await renglonService.getAll()
    console.log('Response from backend:', response) // Debug
    console.log('Response.data:', response.data) // Debug más específico
    
    if (response.data && response.data.success && response.data.data) {
      const processedData = response.data.data.map(r => ({
        ...r,
        monto_inicial: parseFloat(r.monto_inicial) || 0,
        monto_asignado: parseFloat(r.monto_asignado) || 0,
        monto_ejecutado: parseFloat(r.monto_ejecutado) || 0,
        saldo_disponible: parseFloat(r.saldo_disponible) || 0,
        saldo_por_ejecutar: parseFloat(r.saldo_por_ejecutar) || 0,
        porcentaje_asignado: r.monto_inicial > 0 ? (parseFloat(r.monto_asignado) / parseFloat(r.monto_inicial)) * 100 : 0,
        porcentaje_ejecutado: r.monto_asignado > 0 ? (parseFloat(r.monto_ejecutado) / parseFloat(r.monto_asignado)) * 100 : 0
      }))
      
      console.log('About to assign processed data:', processedData)
      renglones.value = processedData
      console.log('Assigned to renglones.value:', renglones.value)
      console.log('renglones.value length:', renglones.value.length)
    } else {
      console.error('Invalid response structure:', response)
      console.error('Expected: response.data.success and response.data.data')
      showToast('Respuesta inválida del servidor', 'error')
    }
  } catch (error) {
    console.error('Error cargando renglones:', error)
    showToast('Error al cargar los renglones', 'error')
  } finally {
    loading.value = false
  }
}

const verDetalle = async (renglonId) => {
  modalDetalle.value.show = true
  modalDetalle.value.loading = true
  modalDetalle.value.renglon = renglones.value.find(r => r.id === renglonId)

  try {
    const response = await renglonService.getSaldo(renglonId)
    if (response.success) {
      modalDetalle.value.data = response.data
    }
  } catch (error) {
    console.error('Error cargando detalle:', error)
    showToast('Error al cargar el detalle del renglón', 'error')
  } finally {
    modalDetalle.value.loading = false
  }
}

const registrarEjecucion = async (renglonId) => {
  const renglon = renglones.value.find(r => r.id === renglonId)
  if (!renglon || renglon.saldo_por_ejecutar <= 0) return

  modalEjecucion.value = {
    show: true,
    procesando: false,
    cargandoPresupuestos: true,
    renglonId: renglonId,
    presupuestosDisponibles: [],
    presupuestoSeleccionado: null,
    monto: 0,
    maxMonto: 0,
    descripcion: ''
  }

  try {
    const response = await ejecucionService.getPresupuestosDisponibles(renglonId)
    if (response.success) {
      modalEjecucion.value.presupuestosDisponibles = response.data
      
      // Auto-seleccionar el primer presupuesto si solo hay uno
      if (response.data.length === 1) {
        modalEjecucion.value.presupuestoSeleccionado = response.data[0].id
        modalEjecucion.value.maxMonto = response.data[0].saldo_por_ejecutar
      }
    }
  } catch (error) {
    console.error('Error cargando presupuestos disponibles:', error)
    showToast('Error al cargar los presupuestos disponibles', 'error')
  } finally {
    modalEjecucion.value.cargandoPresupuestos = false
  }
}

const procesarEjecucion = async () => {
  if (!modalEjecucion.value.presupuestoSeleccionado) {
    showToast('Debe seleccionar un presupuesto', 'error')
    return
  }

  modalEjecucion.value.procesando = true
  
  try {
    const data = {
      presupuesto_det_id: modalEjecucion.value.presupuestoSeleccionado,
      monto: modalEjecucion.value.monto,
      descripcion: modalEjecucion.value.descripcion
    }

    const response = await ejecucionService.registrarEjecucion(data)
    
    if (response.success) {
      showToast('Ejecución registrada exitosamente', 'success')
      cerrarModalEjecucion()
      loadRenglones() // Recargar datos
    } else {
      showToast(response.message || 'Error al registrar la ejecución', 'error')
    }
  } catch (error) {
    console.error('Error procesando ejecución:', error)
    const mensaje = error.response?.data?.message || 'Error al registrar la ejecución'
    showToast(mensaje, 'error')
  } finally {
    modalEjecucion.value.procesando = false
  }
}

const cerrarModal = () => {
  modalDetalle.value = {
    show: false,
    loading: false,
    renglon: null,
    data: null
  }
}

const cerrarModalEjecucion = () => {
  modalEjecucion.value = {
    show: false,
    procesando: false,
    cargandoPresupuestos: false,
    renglonId: null,
    presupuestosDisponibles: [],
    presupuestoSeleccionado: null,
    monto: 0,
    maxMonto: 0,
    descripcion: ''
  }
}

// Watcher para actualizar el monto máximo cuando se selecciona un presupuesto
const onPresupuestoChange = () => {
  const presupuesto = modalEjecucion.value.presupuestosDisponibles.find(
    p => p.id === modalEjecucion.value.presupuestoSeleccionado
  )
  
  if (presupuesto) {
    modalEjecucion.value.maxMonto = presupuesto.saldo_por_ejecutar
    // Resetear el monto si excede el nuevo máximo
    if (modalEjecucion.value.monto > presupuesto.saldo_por_ejecutar) {
      modalEjecucion.value.monto = 0
    }
  }
}

// Watcher para debuggear cambios en renglones
watch(renglones, (newVal, oldVal) => {
  console.log('renglones changed from', oldVal?.length, 'to', newVal?.length)
  console.log('New renglones value:', newVal)
}, { deep: true })

// Lifecycle
onMounted(() => {
  console.log('Component mounted, loading renglones...')
  loadRenglones()
})
</script>