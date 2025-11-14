<template>
  <AppLayout>
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-black text-gray-900 flex items-center">
            <span class="bg-gradient-cfag bg-clip-text text-transparent">Proveedores</span>
          </h1>
          <p class="text-gray-600 mt-2">Gestiona los proveedores del sistema</p>
        </div>
        <button @click="openCreateModal" class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nuevo Proveedor
        </button>
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
        <button @click="alert.show = false" class="text-gray-500 hover:text-gray-700">
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
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Nombre</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">NIT</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Dirección</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Teléfono</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Correo</th>
                <!--<th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Estado</th>-->
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="proveedor in proveedores" :key="proveedor.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <span class="text-sm font-bold text-gray-900">{{ proveedor.nombre }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-600 font-mono">{{ proveedor.nit }}</span>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-600">{{ proveedor.direccion || '-' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-600">{{ proveedor.telefono || '-' }}</span>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-600">{{ proveedor.correo || '-' }}</span>
                </td>
                <!--<td class="px-6 py-4 text-center whitespace-nowrap">
                  <span :class="[
                    'px-3 py-1 rounded-full text-xs font-bold',
                    proveedor.estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ proveedor.estado === 1 ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>-->
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center space-x-2">
                    <button @click="openEditModal(proveedor)" class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors" title="Editar">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="confirmDelete(proveedor)" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors" title="Eliminar">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="proveedores.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                  <svg class="h-16 w-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <p class="text-lg font-semibold">No hay proveedores registrados</p>
                  <p class="text-sm">Crea el primer proveedor</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Crear/Editar -->
      <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-cfag p-6 rounded-t-2xl">
          <h3 class="text-2xl font-black text-white">{{ isEdit ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h3>
        </div>
        
        <form @submit.prevent="submitForm" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Nombre *</label>
            <input v-model="form.nombre" type="text" required class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-primary-200 focus:border-primary-500 outline-none transition-all" placeholder="Nombre del proveedor">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">NIT *</label>
              <input v-model="form.nit" type="text" required class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-primary-200 focus:border-primary-500 outline-none transition-all" placeholder="12345678-9">
            </div>
            
            <!--<div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Estado *</label>
              <select v-model="form.estado" required class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-primary-200 focus:border-primary-500 outline-none transition-all">
                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>
              </select>
            </div>-->
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Dirección</label>
            <textarea v-model="form.direccion" rows="2" class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-primary-200 focus:border-primary-500 outline-none transition-all" placeholder="Dirección completa"></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Teléfono</label>
              <input v-model="form.telefono" type="text" class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-primary-200 focus:border-primary-500 outline-none transition-all" placeholder="5555-5555">
            </div>

            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Correo</label>
              <input v-model="form.correo" type="email" class="w-full px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-primary-200 focus:border-primary-500 outline-none transition-all" placeholder="correo@ejemplo.com">
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button type="button" @click="closeModal" class="px-6 py-2 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-100 transition-colors">
              Cancelar
            </button>
            <button type="submit" class="px-6 py-2 bg-gradient-cfag text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
              {{ isEdit ? 'Actualizar' : 'Crear' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Confirmar Eliminación -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="bg-red-600 p-6 rounded-t-2xl">
          <h3 class="text-2xl font-black text-white">Confirmar Eliminación</h3>
        </div>
        
        <div class="p-6">
          <p class="text-gray-700 mb-4">¿Estás seguro de eliminar el proveedor <strong>{{ proveedorToDelete?.nombre }}</strong>?</p>
          <p class="text-sm text-red-600 font-semibold">Esta acción no se puede deshacer.</p>
        </div>

        <div class="flex justify-end space-x-3 p-6 border-t">
          <button @click="showDeleteModal = false" class="px-6 py-2 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-100 transition-colors">
            Cancelar
          </button>
          <button @click="deleteProveedor" class="px-6 py-2 bg-red-600 text-white rounded-xl font-semibold shadow-lg hover:bg-red-700 transition-colors">
            Eliminar
          </button>
        </div>
      </div>
    </div>
    </main>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import { useAuthStore } from '@/stores/auth'
import proveedorService from '@/services/proveedorService'

const authStore = useAuthStore()

const proveedores = ref([])
const loading = ref(false)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEdit = ref(false)
const proveedorToDelete = ref(null)
const alert = ref({ show: false, type: '', message: '' })

const form = ref({
  nombre: '',
  nit: '',
  direccion: '',
  telefono: '',
  correo: '',
  estado: 1
})

onMounted(() => {
  loadProveedores()
})

const loadProveedores = async () => {
  loading.value = true
  try {
    const response = await proveedorService.getAll()
    proveedores.value = response.data.data
  } catch (error) {
    showAlert('error', 'Error al cargar los proveedores')
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  isEdit.value = false
  resetForm()
  showModal.value = true
}

const openEditModal = (proveedor) => {
  isEdit.value = true
  form.value = { ...proveedor }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const resetForm = () => {
  form.value = {
    nombre: '',
    nit: '',
    direccion: '',
    telefono: '',
    correo: '',
    estado: 1
  }
}

const submitForm = async () => {
  try {
    if (isEdit.value) {
      await proveedorService.update(form.value.id, form.value)
      showAlert('success', 'Proveedor actualizado exitosamente')
    } else {
      await proveedorService.create(form.value)
      showAlert('success', 'Proveedor creado exitosamente')
    }
    closeModal()
    loadProveedores()
  } catch (error) {
    showAlert('error', error.response?.data?.message || 'Error al guardar el proveedor')
  }
}

const confirmDelete = (proveedor) => {
  proveedorToDelete.value = proveedor
  showDeleteModal.value = true
}

const deleteProveedor = async () => {
  try {
    await proveedorService.delete(proveedorToDelete.value.id)
    showAlert('success', 'Proveedor eliminado exitosamente')
    showDeleteModal.value = false
    loadProveedores()
  } catch (error) {
    showAlert('error', error.response?.data?.message || 'Error al eliminar el proveedor')
  }
}

const showAlert = (type, message) => {
  alert.value = { show: true, type, message }
  setTimeout(() => {
    alert.value.show = false
  }, 5000)
}
</script>
