<template>
    <AppLayout>
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 flex items-center">
                        <span class="bg-gradient-cfag bg-clip-text text-transparent">Documentos</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Gestiona documentos adjuntos para diferentes entidades</p>
                </div>
                <button @click="openCreateModal"
                    class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Subir Documento
                </button>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Entidad</label>
                        <select v-model="filtroTipoEntidad" @change="filterByTipoEntidad"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">Todas las entidades</option>
                            <option value="App\\Models\\Proveedor">Proveedores</option>
                            <option value="App\\Models\\MovimientoCab">Movimientos</option>
                            <option value="App\\Models\\FacturaCab">Facturas</option>
                            <option value="App\\Models\\PresupuestoCab">Presupuestos</option>
                            <option value="App\\Models\\Renglon">Renglones</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Archivo</label>
                        <select v-model="filtroTipoArchivo" @change="filterByTipoArchivo"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">Todos los tipos</option>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="word">Word</option>
                            <option value="imagen">Imagen</option>
                            <option value="otro">Otros</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button @click="clearFilters"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">
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
                                    Archivo</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Entidad</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Tipo</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Descripción</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Tamaño</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Fecha</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="documento in filteredDocumentos" :key="documento.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <svg v-if="getFileIcon(documento.tipo_archivo)"
                                                :class="getFileIconColor(documento.tipo_archivo)" class="h-8 w-8"
                                                fill="currentColor" viewBox="0 0 24 24">
                                                <path v-html="getFileIcon(documento.tipo_archivo)"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-bold text-gray-900">{{ documento.nombre_archivo }}
                                            </div>
                                            <div class="text-xs text-gray-500">{{ documento.tipo_archivo }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <span :class="getEntityBadgeClass(documento.documentable_type)">
                                            {{ formatEntityType(documento.documentable_type) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">ID: {{ documento.documentable_id }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="getFileTypeBadgeClass(documento.tipo_archivo)">
                                        {{ documento.tipo_archivo.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ documento.descripcion || 'Sin descripción' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="text-sm text-gray-600">{{ formatFileSize(documento.tamano_archivo)
                                        }}</span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="text-sm text-gray-600">{{ formatDate(documento.created_at) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button @click="downloadDocument(documento)"
                                            class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors"
                                            title="Descargar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </button>
                                        <button @click="viewDocument(documento)"
                                            class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                            title="Ver">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button @click="confirmDelete(documento)"
                                            class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                            title="Eliminar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredDocumentos.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-20 w-20 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-xl font-semibold text-gray-600 mb-2">No hay documentos</p>
                                        <p class="text-gray-500">Sube el primer documento para comenzar</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Modal Subir Documento -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-2xl w-full shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900">Subir Documento</h3>
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
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Entidad *</label>
                            <select v-model="form.documentable_type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="">Seleccionar entidad</option>
                                <option value="App\\Models\\Proveedor">Proveedor</option>
                                <option value="App\\Models\\MovimientoCab">Movimiento</option>
                                <option value="App\\Models\\FacturaCab">Factura</option>
                                <option value="App\\Models\\PresupuestoCab">Presupuesto</option>
                                <option value="App\\Models\\Renglon">Renglón</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">ID de la Entidad *</label>
                            <input v-model="form.documentable_id" type="number" required min="1"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                                placeholder="ID de la entidad">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Archivo *</label>
                        <div
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-primary-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file-upload"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                        <span>Subir archivo</span>
                                        <input id="file-upload" @change="handleFileSelect" type="file" class="sr-only"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                                    </label>
                                    <p class="pl-1">o arrastra y suelta</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, PDF, DOC, XLS hasta 10MB</p>
                            </div>
                        </div>
                        <div v-if="selectedFile" class="mt-3 p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-medium text-green-800">{{ selectedFile.name }}</span>
                                <span class="text-xs text-green-600 ml-2">({{ formatFileSize(selectedFile.size)
                                    }})</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción</label>
                        <textarea v-model="form.descripcion" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                            placeholder="Descripción del documento (opcional)..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" @click="closeModal"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="submitting || !selectedFile"
                            class="px-6 py-3 bg-gradient-cfag text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 transition-all">
                            {{ submitting ? 'Subiendo...' : 'Subir Documento' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import documentoService from '@/services/documentoService'

// Estado principal
const loading = ref(true)
const documentos = ref([])

// Filtros
const filtroTipoEntidad = ref('')
const filtroTipoArchivo = ref('')

// Estado del modal
const showModal = ref(false)
const submitting = ref(false)
const selectedFile = ref(null)

// Formulario
const form = ref({
    documentable_type: '',
    documentable_id: '',
    descripcion: ''
})

// Alertas
const alert = ref({
    show: false,
    type: 'success',
    message: ''
})

// Computadas
const filteredDocumentos = computed(() => {
    let filtered = documentos.value

    if (filtroTipoEntidad.value) {
        filtered = filtered.filter(d => d.documentable_type === filtroTipoEntidad.value)
    }

    if (filtroTipoArchivo.value) {
        filtered = filtered.filter(d => d.tipo_archivo === filtroTipoArchivo.value)
    }

    return filtered
})

// Cargar datos iniciales
onMounted(async () => {
    await loadDocumentos()
})

const loadDocumentos = async () => {
    try {
        loading.value = true
        const response = await documentoService.getAll()
        documentos.value = response.data.documentos || response.data
    } catch (error) {
        console.error('Error al cargar documentos:', error)
        showAlert('error', 'Error al cargar documentos')
    } finally {
        loading.value = false
    }
}

const openCreateModal = () => {
    form.value = {
        documentable_type: '',
        documentable_id: '',
        descripcion: ''
    }
    selectedFile.value = null
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
}

const handleFileSelect = (event) => {
    const file = event.target.files[0]
    if (file) {
        selectedFile.value = file
    }
}

const submitForm = async () => {
    if (!selectedFile.value) {
        showAlert('error', 'Debe seleccionar un archivo')
        return
    }

    try {
        submitting.value = true
        const formData = new FormData()
        formData.append('archivo', selectedFile.value)
        formData.append('documentable_type', form.value.documentable_type)
        formData.append('documentable_id', form.value.documentable_id)
        if (form.value.descripcion) {
            formData.append('descripcion', form.value.descripcion)
        }

        await documentoService.upload(formData)
        showAlert('success', 'Documento subido correctamente')
        closeModal()
        await loadDocumentos()
    } catch (error) {
        console.error('Error al subir documento:', error)
        showAlert('error', 'Error al subir documento')
    } finally {
        submitting.value = false
    }
}

const downloadDocument = async (documento) => {
    try {
        await documentoService.download(documento.id)
    } catch (error) {
        console.error('Error al descargar documento:', error)
        showAlert('error', 'Error al descargar documento')
    }
}

const viewDocument = (documento) => {
    // Implementar vista de documento
    window.open(`/api/documentos/${documento.id}/view`, '_blank')
}

const confirmDelete = async (documento) => {
    if (confirm('¿Está seguro de eliminar este documento?')) {
        try {
            await documentoService.delete(documento.id)
            showAlert('success', 'Documento eliminado correctamente')
            await loadDocumentos()
        } catch (error) {
            console.error('Error al eliminar documento:', error)
            showAlert('error', 'Error al eliminar documento')
        }
    }
}

const filterByTipoEntidad = () => {
    // La computada se encarga del filtrado
}

const filterByTipoArchivo = () => {
    // La computada se encarga del filtrado
}

const clearFilters = () => {
    filtroTipoEntidad.value = ''
    filtroTipoArchivo.value = ''
}

const showAlert = (type, message) => {
    alert.value = { show: true, type, message }
    setTimeout(() => {
        alert.value.show = false
    }, 5000)
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('es-GT')
}

const formatEntityType = (type) => {
    const types = {
        'App\\Models\\Proveedor': 'Proveedor',
        'App\\Models\\MovimientoCab': 'Movimiento',
        'App\\Models\\FacturaCab': 'Factura',
        'App\\Models\\PresupuestoCab': 'Presupuesto',
        'App\\Models\\Renglon': 'Renglón',
        'App\\Models\\Cur': 'Compromiso (CUR)',
        'App\\Models\\Intra': 'Transferencia (INTRA)'
    }
    return types[type] || type
}

const getEntityBadgeClass = (type) => {
    const classes = {
        'App\\Models\\Proveedor': 'px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800',
        'App\\Models\\MovimientoCab': 'px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800',
        'App\\Models\\FacturaCab': 'px-2 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800',
        'App\\Models\\PresupuestoCab': 'px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800',
        'App\\Models\\Renglon': 'px-2 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800',
        'App\\Models\\Cur': 'px-2 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800',
        'App\\Models\\Intra': 'px-2 py-1 rounded-full text-xs font-bold bg-teal-100 text-teal-800'
    }
    return classes[type] || 'px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800'
}

const getFileTypeBadgeClass = (type) => {
    const classes = {
        pdf: 'px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800',
        excel: 'px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800',
        word: 'px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800',
        imagen: 'px-2 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800'
    }
    return classes[type] || 'px-2 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800'
}

const getFileIcon = (type) => {
    // Iconos SVG simplificados para diferentes tipos de archivo
    return 'd="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"'
}

const getFileIconColor = (type) => {
    const colors = {
        pdf: 'text-red-500',
        excel: 'text-green-500',
        word: 'text-blue-500',
        imagen: 'text-purple-500'
    }
    return colors[type] || 'text-gray-500'
}
</script>