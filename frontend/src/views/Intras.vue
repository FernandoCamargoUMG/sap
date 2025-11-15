<template>
    <AppLayout>
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 flex items-center">
                        <span class="bg-gradient-cfag bg-clip-text text-transparent">Transferencias (INTRAS)</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Gestiona las transferencias entre renglones presupuestarios</p>
                </div>
                <button @click="openCreateModal"
                    class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Nueva Transferencia
                </button>
            </div>

            <!-- Alertas -->
            <div v-if="alert.show" :class="[
                'mb-6 p-4 rounded-xl border-l-4 flex items-center justify-between animate-pulse',
                alert.type === 'success' ? 'bg-green-50 border-green-500 text-green-800' : 'bg-red-50 border-red-500 text-red-800'
            ]">
                <div class="flex items-center">
                    <svg v-if="alert.type === 'success'" class="h-6 w-6 mr-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-semibold">{{ alert.message }}</span>
                </div>
                <button @click="alert.show = false">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
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
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Fecha</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Descripción</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Renglón Origen</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Renglón Destino</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Monto</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Estado</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="intra in intras" :key="intra.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900">{{
                                        formatDate(intra.fecha_transferencia) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ intra.descripcion }}</div>
                                    <div v-if="intra.numero_documento" class="text-xs text-gray-500">
                                        Doc: {{ intra.numero_documento }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ intra.renglon_origen?.codigo }}</div>
                                    <div class="text-xs text-gray-500">{{ intra.renglon_origen?.nombre }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ intra.renglon_destino?.codigo }}</div>
                                    <div class="text-xs text-gray-500">{{ intra.renglon_destino?.nombre }}</div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="text-lg font-black text-blue-600">Q{{ formatMoney(intra.monto)
                                        }}</span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-bold',
                                        intra.estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]">
                                        {{ intra.estado === 1 ? 'Activa' : 'Anulada' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button v-if="intra.estado === 1" @click="confirmAnular(intra)"
                                            class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                            title="Anular">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="intras.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-20 w-20 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        <p class="text-xl font-semibold text-gray-600 mb-2">No hay transferencias</p>
                                        <p class="text-gray-500">Crea la primera transferencia para comenzar</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Modal Crear (simplificado) -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-2xl w-full shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900">Nueva Transferencia (INTRA)</h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Fecha de Transferencia *</label>
                            <input v-model="form.fecha_transferencia" type="date" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Número de Documento</label>
                            <input v-model="form.numero_documento" type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                                placeholder="Ej: INTRA-001-2025">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Renglón Origen *</label>
                            <select v-model="form.renglon_origen_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="">Seleccionar renglón origen</option>
                                <option v-for="renglon in renglones" :key="renglon.id" :value="renglon.id">
                                    {{ renglon.codigo }} - {{ renglon.nombre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Renglón Destino *</label>
                            <select v-model="form.renglon_destino_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="">Seleccionar renglón destino</option>
                                <option v-for="renglon in renglones" :key="renglon.id" :value="renglon.id"
                                    :disabled="renglon.id == form.renglon_origen_id">
                                    {{ renglon.codigo }} - {{ renglon.nombre }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Monto a Transferir *</label>
                        <input v-model="form.monto" type="number" step="0.01" min="0" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                            placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción *</label>
                        <textarea v-model="form.descripcion" required rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                            placeholder="Descripción de la transferencia..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" @click="closeModal"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="px-6 py-3 bg-gradient-cfag text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 transition-all">
                            {{ submitting ? 'Procesando...' : 'Crear Transferencia' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modales de confirmación similares -->
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import intraService from '@/services/intraService'
import renglonService from '@/services/renglonService'

// Estado principal
const loading = ref(true)
const intras = ref([])
const renglones = ref([])

// Estado del modal
const showModal = ref(false)
const submitting = ref(false)

// Formulario
const form = ref({
    fecha_transferencia: new Date().toISOString().split('T')[0],
    numero_documento: '',
    renglon_origen_id: '',
    renglon_destino_id: '',
    monto: 0,
    descripcion: ''
})

// Alertas
const alert = ref({
    show: false,
    type: 'success',
    message: ''
})

// Cargar datos iniciales
onMounted(async () => {
    await Promise.all([
        loadIntras(),
        loadRenglones()
    ])
})

const loadIntras = async () => {
    try {
        loading.value = true
        const response = await intraService.getAll()
        intras.value = response.data.intras || response.data
    } catch (error) {
        console.error('Error al cargar transferencias:', error)
        showAlert('error', 'Error al cargar transferencias')
    } finally {
        loading.value = false
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

const openCreateModal = () => {
    form.value = {
        fecha_transferencia: new Date().toISOString().split('T')[0],
        numero_documento: '',
        renglon_origen_id: '',
        renglon_destino_id: '',
        monto: 0,
        descripcion: ''
    }
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
}

const submitForm = async () => {
    try {
        submitting.value = true
        await intraService.create(form.value)
        showAlert('success', 'Transferencia creada correctamente')
        closeModal()
        await loadIntras()
    } catch (error) {
        console.error('Error al crear transferencia:', error)
        showAlert('error', 'Error al crear transferencia')
    } finally {
        submitting.value = false
    }
}

const confirmAnular = (intra) => {
    // Implementar confirmación de anulación
}

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
    return new Date(dateString).toLocaleDateString('es-GT')
}
</script>