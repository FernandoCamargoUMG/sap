<template>
  <AppLayout>
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-black text-gray-900 flex items-center">
            <span class="bg-gradient-cfag bg-clip-text text-transparent">Bitácora</span>
          </h1>
          <p class="text-gray-600 mt-2">Historia de auditoría y operaciones del sistema</p>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Usuario</label>
            <select v-model="filtroUsuario" @change="applyFilters" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
              <option value="">Todos los usuarios</option>
              <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">{{ usuario.nombre }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Tabla</label>
            <select v-model="filtroTabla" @change="applyFilters" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
              <option value="">Todas las tablas</option>
              <option v-for="tabla in tablas" :key="tabla.id" :value="tabla.id">{{ tabla.nombre }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Acción</label>
            <select v-model="filtroAccion" @change="applyFilters" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
              <option value="">Todas las acciones</option>
              <option v-for="accion in acciones" :key="accion.id" :value="accion.id">{{ accion.nombre }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Fecha</label>
            <input v-model="filtroFecha" @change="applyFilters" type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button @click="clearFilters" class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-primary-600"></div>
      </div>

      <!-- Tabla de bitácora -->
      <div v-else class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-cfag">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Fecha/Hora</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Usuario</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Acción</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Tabla</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Registro</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Detalle</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="registro in registros" :key="registro.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900">{{ formatDateTime(registro.fecha_accion) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                      <div class="h-8 w-8 rounded-full bg-gradient-cfag flex items-center justify-center">
                        <span class="text-white font-bold text-xs">{{ getInitials(registro.usuario?.nombre) }}</span>
                      </div>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-bold text-gray-900">{{ registro.usuario?.nombre }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'px-3 py-1 rounded-full text-xs font-bold',
                    getAccionBadgeClass(registro.accion)
                  ]">
                    {{ getAccionNombre(registro.accion) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-bold text-gray-900">{{ getTablaNombre(registro.tabla_afectada) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-600">ID: {{ registro.registro_id }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 max-w-xs truncate" :title="registro.detalle">
                    {{ registro.detalle }}
                  </div>
                </td>
              </tr>
              <tr v-if="registros.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <svg class="h-20 w-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-xl font-semibold text-gray-600 mb-2">No hay registros</p>
                    <p class="text-gray-500">No se encontraron registros de auditoría</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import bitacoraService from '@/services/bitacoraService'
import usuarioService from '@/services/usuarioService'

// Estado principal
const loading = ref(true)
const registros = ref([])
const usuarios = ref([])
const tablas = ref([])
const acciones = ref([])

// Filtros
const filtroUsuario = ref('')
const filtroTabla = ref('')
const filtroAccion = ref('')
const filtroFecha = ref('')

// Cargar datos iniciales
onMounted(async () => {
  await Promise.all([
    loadRegistros(),
    loadUsuarios(),
    loadTablas(),
    loadAcciones()
  ])
})

// Cargar registros de bitácora
const loadRegistros = async () => {
  try {
    loading.value = true
    const params = {}
    
    if (filtroUsuario.value) params.usuario_id = filtroUsuario.value
    if (filtroTabla.value) params.tabla_afectada = filtroTabla.value
    if (filtroAccion.value) params.accion = filtroAccion.value
    if (filtroFecha.value) params.fecha = filtroFecha.value

    const response = await bitacoraService.getAll(params)
    registros.value = response.data.bitacora || response.data
  } catch (error) {
    console.error('Error al cargar bitácora:', error)
  } finally {
    loading.value = false
  }
}

// Cargar usuarios
const loadUsuarios = async () => {
  try {
    const response = await usuarioService.getAll()
    usuarios.value = response.data.usuarios || response.data
  } catch (error) {
    console.error('Error al cargar usuarios:', error)
  }
}

// Cargar tablas
const loadTablas = () => {
  const response = bitacoraService.getTablas()
  tablas.value = response.data
}

// Cargar acciones
const loadAcciones = () => {
  const response = bitacoraService.getAcciones()
  acciones.value = response.data
}

// Aplicar filtros
const applyFilters = async () => {
  await loadRegistros()
}

// Limpiar filtros
const clearFilters = async () => {
  filtroUsuario.value = ''
  filtroTabla.value = ''
  filtroAccion.value = ''
  filtroFecha.value = ''
  await loadRegistros()
}

// Utilidades
const getInitials = (nombre) => {
  if (!nombre) return '??'
  return nombre.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2)
}

const getAccionBadgeClass = (accion) => {
  const classes = {
    'creado': 'bg-green-100 text-green-800',
    'modificado': 'bg-blue-100 text-blue-800',
    'eliminado': 'bg-red-100 text-red-800',
    'anulado': 'bg-orange-100 text-orange-800',
    'restaurado': 'bg-purple-100 text-purple-800'
  }
  return classes[accion] || 'bg-gray-100 text-gray-800'
}

const getAccionNombre = (accion) => {
  const accionObj = acciones.value.find(a => a.id === accion)
  return accionObj ? accionObj.nombre : accion
}

const getTablaNombre = (tabla) => {
  const tablaObj = tablas.value.find(t => t.id === tabla)
  return tablaObj ? tablaObj.nombre : tabla
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('es-GT', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}
</script>