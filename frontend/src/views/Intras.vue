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
                                        formatDate(intra.fecha) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ intra.justificacion }}</div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        Usuario: {{ intra.usuario?.nombre || 'No disponible' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-semibold">{{ intra.renglon_origen?.codigo }}</div>
                                    <div class="text-xs text-gray-500">{{ intra.renglon_origen?.nombre }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-semibold">{{ intra.renglon_destino?.codigo }}</div>
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
                                        <button @click="viewDetails(intra)"
                                            class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                            title="Ver detalles">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button v-if="intra.estado === 1" @click="confirmAnular(intra)"
                                            class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                            title="Anular y revertir presupuestos">
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
                            <input v-model="form.fecha" type="date" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Año Presupuestario</label>
                            <select v-model="form.anio" @change="loadRenglonesDisponibles"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Renglón Origen *</label>
                            <select v-model="form.renglon_origen" required @change="updateRenglonInfo"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="">Seleccionar renglón origen</option>
                                <option v-for="renglon in renglonesDisponibles" :key="renglon.id" :value="renglon.id">
                                    {{ renglon.codigo }} - {{ renglon.nombre }} 
                                    (Disponible: Q{{ formatMoney(renglon.saldo_disponible) }})
                                </option>
                            </select>
                            <div v-if="renglonOrigenInfo" class="mt-2 p-3 bg-blue-50 rounded-lg">
                                <p class="text-xs text-blue-800">
                                    <strong>Presupuesto:</strong> Q{{ formatMoney(renglonOrigenInfo.presupuesto_asignado) }} |
                                    <strong>Ejecutado:</strong> Q{{ formatMoney(renglonOrigenInfo.monto_ejecutado) }} |
                                    <strong>Disponible:</strong> Q{{ formatMoney(renglonOrigenInfo.saldo_disponible) }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Renglón Destino *</label>
                            <select v-model="form.renglon_destino" required @change="updateRenglonInfo"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="">Seleccionar renglón destino</option>
                                <option v-for="renglon in renglonesDisponibles" :key="renglon.id" :value="renglon.id"
                                    :disabled="renglon.id == form.renglon_origen">
                                    {{ renglon.codigo }} - {{ renglon.nombre }}
                                    (Actual: Q{{ formatMoney(renglon.presupuesto_asignado) }})
                                </option>
                            </select>
                            <div v-if="renglonDestinoInfo" class="mt-2 p-3 bg-green-50 rounded-lg">
                                <p class="text-xs text-green-800">
                                    <strong>Presupuesto:</strong> Q{{ formatMoney(renglonDestinoInfo.presupuesto_asignado) }} |
                                    <strong>Ejecutado:</strong> Q{{ formatMoney(renglonDestinoInfo.monto_ejecutado) }} |
                                    <strong>Disponible:</strong> Q{{ formatMoney(renglonDestinoInfo.saldo_disponible) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Monto a Transferir *</label>
                        <input v-model="form.monto" type="number" step="0.01" min="0" required
                            :max="renglonOrigenInfo?.saldo_disponible || 0"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                            placeholder="0.00">
                        <p v-if="renglonOrigenInfo" class="text-xs text-gray-500 mt-1">
                            Máximo disponible: Q{{ formatMoney(renglonOrigenInfo.saldo_disponible) }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Justificación *</label>
                        <textarea v-model="form.justificacion" required rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                            placeholder="Justificación detallada de la transferencia presupuestaria..."></textarea>
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

        <!-- Modal Detalles -->
        <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-3xl w-full shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900">Detalles de la Transferencia</h3>
                    <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedIntra" class="space-y-6">
                    <!-- Información básica -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Información de la Transferencia</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Fecha:</label>
                                <p class="text-lg">{{ formatDate(selectedIntra.fecha) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Monto transferido:</label>
                                <p class="text-2xl font-black text-blue-600">Q{{ formatMoney(selectedIntra.monto) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Estado:</label>
                                <span :class="[
                                    'px-3 py-1 rounded-full text-sm font-bold',
                                    selectedIntra.estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]">
                                    {{ selectedIntra.estado === 1 ? 'Activa' : 'Anulada' }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Usuario:</label>
                                <p class="text-lg">{{ selectedIntra.usuario?.nombre || 'No disponible' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Renglones involucrados -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Renglón Origen -->
                        <div class="bg-red-50 rounded-xl p-6">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M8 7l4-4m0 0l4 4m-4-4v18" />
                                </svg>
                                Renglón Origen
                            </h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Código:</label>
                                    <p class="text-lg font-mono font-bold text-red-800">{{ selectedIntra.renglon_origen?.codigo }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Nombre:</label>
                                    <p class="text-sm">{{ selectedIntra.renglon_origen?.nombre }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Grupo:</label>
                                    <p class="text-sm text-gray-600">{{ selectedIntra.renglon_origen?.grupo }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Renglón Destino -->
                        <div class="bg-green-50 rounded-xl p-6">
                            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M16 17l-4 4m0 0l-4-4m4 4V3" />
                                </svg>
                                Renglón Destino
                            </h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Código:</label>
                                    <p class="text-lg font-mono font-bold text-green-800">{{ selectedIntra.renglon_destino?.codigo }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Nombre:</label>
                                    <p class="text-sm">{{ selectedIntra.renglon_destino?.nombre }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Grupo:</label>
                                    <p class="text-sm text-gray-600">{{ selectedIntra.renglon_destino?.grupo }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Justificación -->
                    <div class="bg-orange-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Justificación</h4>
                        <p class="text-gray-800 leading-relaxed">{{ selectedIntra.justificacion }}</p>
                    </div>

                    <!-- Documentos -->
                    <div class="bg-blue-50 rounded-xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-bold text-gray-900 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Documentos Adjuntos
                            </h4>
                            <div class="flex items-center space-x-2">
                                <input ref="fileInput" type="file" accept=".pdf" @change="handleFileSelect" class="hidden">
                                <button @click="$refs.fileInput.click()" 
                                    :disabled="uploadingDocument"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                                    <svg v-if="!uploadingDocument" class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <div v-else class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                    {{ uploadingDocument ? 'Subiendo...' : 'Subir PDF' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="selectedIntra.documentos && selectedIntra.documentos.length > 0" class="space-y-3">
                            <div v-for="documento in selectedIntra.documentos" :key="documento.id" 
                                class="flex items-center justify-between p-4 bg-white rounded-lg border border-blue-200">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ documento.nombre_archivo }}</p>
                                        <p class="text-xs text-gray-500">
                                            Subido por {{ documento.usuario?.nombre || 'Usuario' }} 
                                            el {{ formatDate(documento.created_at) }}
                                        </p>
                                        <p class="text-xs text-gray-400">{{ documento.tamanio_formateado || 'Tamaño no disponible' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button @click="downloadDocument(documento.id, documento.nombre_archivo)"
                                        class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                        title="Descargar documento">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </button>
                                    <button @click="confirmDeleteDocument(documento)"
                                        class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                        title="Eliminar documento">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-8">
                            <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-gray-500 text-sm">No hay documentos adjuntos</p>
                            <p class="text-gray-400 text-xs mt-1">Haz clic en "Subir PDF" para agregar documentos</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button @click="showDetailsModal = false"
                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import intraService from '@/services/intraService'

// Estado principal
const loading = ref(true)
const intras = ref([])
const renglonesDisponibles = ref([])

// Estado del modal
const showModal = ref(false)
const showDetailsModal = ref(false)
const selectedIntra = ref(null)
const submitting = ref(false)
const uploadingDocument = ref(false)

// Formulario
const form = ref({
    fecha: new Date().toISOString().split('T')[0],
    anio: new Date().getFullYear(),
    renglon_origen: '',
    renglon_destino: '',
    monto: 0,
    justificacion: ''
})

// Info de renglones seleccionados
const renglonOrigenInfo = ref(null)
const renglonDestinoInfo = ref(null)

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
        loadRenglonesDisponibles()
    ])
})

const loadIntras = async () => {
    try {
        loading.value = true
        const response = await intraService.getAll()
        console.log('Respuesta INTRAS:', response)
        intras.value = response.data?.data || response.data || []
    } catch (error) {
        console.error('Error al cargar transferencias:', error)
        showAlert('error', 'Error al cargar transferencias')
    } finally {
        loading.value = false
    }
}

const loadRenglonesDisponibles = async () => {
    try {
        const response = await intraService.getRenglonesDisponibles(form.value.anio)
        console.log('Renglones disponibles:', response)
        renglonesDisponibles.value = response.data?.data || response.data || []
    } catch (error) {
        console.error('Error al cargar renglones disponibles:', error)
        showAlert('error', 'Error al cargar renglones disponibles')
    }
}

const openCreateModal = () => {
    form.value = {
        fecha: new Date().toISOString().split('T')[0],
        anio: new Date().getFullYear(),
        renglon_origen: '',
        renglon_destino: '',
        monto: 0,
        justificacion: ''
    }
    renglonOrigenInfo.value = null
    renglonDestinoInfo.value = null
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    renglonOrigenInfo.value = null
    renglonDestinoInfo.value = null
}

const submitForm = async () => {
    try {
        submitting.value = true
        
        // Validar que el monto no exceda el disponible
        if (renglonOrigenInfo.value && form.value.monto > renglonOrigenInfo.value.saldo_disponible) {
            throw new Error(`El monto no puede exceder el saldo disponible de Q${formatMoney(renglonOrigenInfo.value.saldo_disponible)}`)
        }
        
        const response = await intraService.create(form.value)
        console.log('Transferencia creada:', response)
        
        showAlert('success', 'Transferencia presupuestaria creada correctamente')
        closeModal()
        await Promise.all([
            loadIntras(),
            loadRenglonesDisponibles() // Actualizar saldos
        ])
    } catch (error) {
        console.error('Error al crear transferencia:', error)
        showAlert('error', error.response?.data?.message || error.message || 'Error al crear transferencia')
    } finally {
        submitting.value = false
    }
}

const confirmAnular = (intra) => {
    if (confirm(`¿Estás seguro de que quieres anular la transferencia de Q${formatMoney(intra.monto)} del renglón ${intra.renglon_origen?.codigo} al ${intra.renglon_destino?.codigo}? Esta acción revertirá los presupuestos.`)) {
        anularIntra(intra)
    }
}

const anularIntra = async (intra) => {
    try {
        await intraService.delete(intra.id)
        showAlert('success', 'Transferencia anulada y presupuestos revertidos correctamente')
        await Promise.all([
            loadIntras(),
            loadRenglonesDisponibles() // Actualizar saldos
        ])
    } catch (error) {
        console.error('Error al anular transferencia:', error)
        showAlert('error', error.response?.data?.message || 'Error al anular transferencia')
    }
}

const updateRenglonInfo = () => {
    if (form.value.renglon_origen) {
        renglonOrigenInfo.value = renglonesDisponibles.value.find(r => r.id == form.value.renglon_origen)
    }
    if (form.value.renglon_destino) {
        renglonDestinoInfo.value = renglonesDisponibles.value.find(r => r.id == form.value.renglon_destino)
    }
}

const viewDetails = (intra) => {
    selectedIntra.value = intra
    showDetailsModal.value = true
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

// Métodos para manejo de documentos
const handleFileSelect = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    // Validar tipo de archivo
    if (file.type !== 'application/pdf') {
        showAlert('error', 'Solo se permiten archivos PDF')
        event.target.value = ''
        return
    }

    // Validar tamaño (10MB máximo)
    if (file.size > 10 * 1024 * 1024) {
        showAlert('error', 'El archivo no debe exceder los 10MB')
        event.target.value = ''
        return
    }

    await uploadDocument(file)
    event.target.value = ''
}

const uploadDocument = async (file) => {
    if (!selectedIntra.value) return

    try {
        uploadingDocument.value = true
        const response = await intraService.uploadDocument(selectedIntra.value.id, file)
        
        // Buscar el intra en la lista principal
        const intraIndex = intras.value.findIndex(c => c.id === selectedIntra.value.id)
        if (intraIndex !== -1) {
            // Inicializar documentos si no existe
            if (!intras.value[intraIndex].documentos) {
                intras.value[intraIndex].documentos = []
            }
            // Agregar el documento al intra principal
            intras.value[intraIndex].documentos.unshift(response.data.data)
            
            // Actualizar la referencia del selectedIntra
            selectedIntra.value = intras.value[intraIndex]
        }
        
        showAlert('success', 'Documento subido correctamente')
    } catch (error) {
        console.error('Error al subir documento:', error)
        showAlert('error', error.response?.data?.message || 'Error al subir el documento')
    } finally {
        uploadingDocument.value = false
    }
}

const downloadDocument = async (documentoId, fileName) => {
    try {
        const response = await intraService.downloadDocument(documentoId)
        
        // Crear un enlace temporal para descargar
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.style.display = 'none'
        link.href = url
        link.download = fileName
        
        document.body.appendChild(link)
        link.click()
        
        // Limpiar
        window.URL.revokeObjectURL(url)
        document.body.removeChild(link)
        
        showAlert('success', 'Documento descargado correctamente')
    } catch (error) {
        console.error('Error al descargar documento:', error)
        showAlert('error', 'Error al descargar el documento')
    }
}

const confirmDeleteDocument = (documento) => {
    if (confirm(`¿Estás seguro de que quieres eliminar el documento "${documento.nombre_archivo}"?`)) {
        deleteDocument(documento)
    }
}

const deleteDocument = async (documento) => {
    try {
        await intraService.deleteDocument(documento.id)
        
        // Buscar el intra en la lista principal y eliminar el documento
        const intraIndex = intras.value.findIndex(c => c.id === selectedIntra.value.id)
        if (intraIndex !== -1 && intras.value[intraIndex].documentos) {
            const docIndex = intras.value[intraIndex].documentos.findIndex(d => d.id === documento.id)
            if (docIndex !== -1) {
                intras.value[intraIndex].documentos.splice(docIndex, 1)
            }
            
            // Actualizar la referencia del selectedIntra
            selectedIntra.value = intras.value[intraIndex]
        }
        
        showAlert('success', 'Documento eliminado correctamente')
    } catch (error) {
        console.error('Error al eliminar documento:', error)
        showAlert('error', 'Error al eliminar el documento')
    }
}
</script>