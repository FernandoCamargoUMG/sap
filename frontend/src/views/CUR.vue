<template>
    <AppLayout>
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 flex items-center">
                        <span class="bg-gradient-cfag bg-clip-text text-transparent">Compromisos (CUR)</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Gestiona los compromisos de pago con proveedores</p>
                </div>
                <button @click="openCreateModal"
                    class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    Nuevo CUR
                </button>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Proveedor</label>
                        <select v-model="filtroProveedor" @change="filterByProveedor"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">Todos los proveedores</option>
                            <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">{{
                                proveedor.nombre }}</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Renglón</label>
                        <select v-model="filtroRenglon" @change="filterByRenglon"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">Todos los renglones</option>
                            <option v-for="renglon in renglones" :key="renglon.id" :value="renglon.id">{{ renglon.codigo
                                }} - {{ renglon.nombre }}</option>
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
                                    Fecha</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Proveedor</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Renglón</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Descripción</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Monto</th>
                                <!--<th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Estado</th>-->
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="cur in filteredCurs" :key="cur.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900">{{ formatDate(cur.fecha_compromiso)
                                        }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ cur.proveedor?.nombre }}</div>
                                    <div class="text-xs text-gray-500">NIT: {{ cur.proveedor?.nit }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ cur.renglon?.codigo }}</div>
                                    <div class="text-xs text-gray-500">{{ cur.renglon?.nombre }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ cur.descripcion }}</div>
                                    <div v-if="cur.documentos && cur.documentos.length > 0" class="flex items-center mt-2">
                                        <svg class="h-4 w-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-xs text-gray-600 font-medium">
                                            {{ cur.documentos.length }} documento{{ cur.documentos.length === 1 ? '' : 's' }}
                                        </span>
                                    </div>
                                    <div v-else-if="cur.documento" class="flex items-center mt-2">
                                        <svg class="h-4 w-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-xs text-gray-600 font-medium">PDF adjunto</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="text-lg font-black text-orange-600">Q{{
                                        formatMoney(cur.monto) }}</span>
                                </td>
                                <!--<td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-bold',
                                        cur.estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]">
                                        {{ cur.estado === 1 ? 'Activo' : 'Anulado' }}
                                    </span>
                                </td>-->
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button @click="viewDetails(cur)"
                                            class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                            title="Ver detalles">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button v-if="cur.estado === 1" @click="openEditModal(cur)"
                                            class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors"
                                            title="Editar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button v-if="cur.estado === 1" @click="confirmAnular(cur)"
                                            class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                            title="Eliminar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredCurs.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-20 w-20 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                        </svg>
                                        <p class="text-xl font-semibold text-gray-600 mb-2">No hay compromisos</p>
                                        <p class="text-gray-500">Crea el primer compromiso para comenzar</p>
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
                    <h3 class="text-2xl font-black text-gray-900">Nuevo Compromiso (CUR)</h3>
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
                            <label class="block text-sm font-bold text-gray-700 mb-2">Fecha del Compromiso *</label>
                            <input v-model="form.fecha_compromiso" type="date" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Número CUR</label>
                            <input v-model="form.numero_cur" type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                                placeholder="Ej: CUR-001-2025">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Proveedor *</label>
                            <select v-model="form.proveedor_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="">Seleccionar proveedor</option>
                                <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                                    {{ proveedor.nombre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Renglón *</label>
                            <select v-model="form.renglon_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="">Seleccionar renglón</option>
                                <option v-for="renglon in renglones" :key="renglon.id" :value="renglon.id">
                                    {{ renglon.codigo }} - {{ renglon.nombre }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Monto del Compromiso *</label>
                        <input v-model="form.monto" type="number" step="0.01" min="0" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                            placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción *</label>
                        <textarea v-model="form.descripcion" required rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                            placeholder="Descripción del compromiso..."></textarea>
                    </div>

                    <!-- Documento PDF (igual que facturas) -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Documento PDF</label>
                        <input @change="onFileChange" type="file" accept=".pdf" ref="fileInput"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                        <p class="text-xs text-gray-500 mt-1">Opcional. Máximo 10MB. Solo archivos PDF.</p>
                        <div v-if="fileErrors.length > 0" class="mt-2">
                            <div v-for="error in fileErrors" :key="error" class="text-red-600 text-xs">
                                {{ error }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" @click="closeModal"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="px-6 py-3 bg-gradient-cfag text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 transition-all">
                            {{ submitting ? 'Procesando...' : 'Crear Compromiso' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Editar -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-2xl w-full shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900">Editar Compromiso (CUR)</h3>
                    <button @click="closeEditModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="updateForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Fecha del Compromiso *</label>
                            <input v-model="form.fecha_compromiso" type="date" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Número CUR</label>
                            <input v-model="form.numero_cur" type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                                placeholder="Ej: CUR-001-2025">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Proveedor *</label>
                            <select v-model="form.proveedor_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="">Seleccionar proveedor</option>
                                <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                                    {{ proveedor.nombre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Renglón *</label>
                            <select v-model="form.renglon_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                <option value="">Seleccionar renglón</option>
                                <option v-for="renglon in renglones" :key="renglon.id" :value="renglon.id">
                                    {{ renglon.codigo }} - {{ renglon.nombre }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Monto del Compromiso *</label>
                        <input v-model="form.monto" type="number" step="0.01" min="0" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                            placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción *</label>
                        <textarea v-model="form.descripcion" required rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500"
                            placeholder="Descripción del compromiso..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" @click="closeEditModal"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="px-6 py-3 bg-gradient-cfag text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 transition-all">
                            {{ submitting ? 'Actualizando...' : 'Actualizar Compromiso' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Detalles -->
        <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-3xl w-full shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900">Detalles del Compromiso (CUR)</h3>
                    <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedCur" class="space-y-6">
                    <!-- Información básica -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Información Básica</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Número CUR:</label>
                                <p class="text-lg font-black text-blue-600">{{ selectedCur.numero_cur }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Fecha de Compromiso:</label>
                                <p class="text-lg">{{ formatDate(selectedCur.fecha_compromiso) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Monto:</label>
                                <p class="text-2xl font-black text-orange-600">Q{{ formatMoney(selectedCur.monto) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Estado:</label>
                                <span :class="[
                                    'px-3 py-1 rounded-full text-sm font-bold',
                                    selectedCur.estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]">
                                    {{ selectedCur.estado === 1 ? 'Activo' : 'Anulado' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Información del proveedor -->
                    <div class="bg-blue-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Información del Proveedor</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Nombre:</label>
                                <p class="text-lg font-semibold text-blue-800">{{ selectedCur.proveedor?.nombre || 'No disponible' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">NIT:</label>
                                <p class="text-lg font-mono">{{ selectedCur.proveedor?.nit || 'No disponible' }}</p>
                            </div>
                            <div v-if="selectedCur.proveedor?.direccion">
                                <label class="block text-sm font-bold text-gray-700">Dirección:</label>
                                <p class="text-lg">{{ selectedCur.proveedor.direccion }}</p>
                            </div>
                            <div v-if="selectedCur.proveedor?.telefono">
                                <label class="block text-sm font-bold text-gray-700">Teléfono:</label>
                                <p class="text-lg">{{ selectedCur.proveedor.telefono }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Información del renglón -->
                    <div class="bg-green-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Información del Renglón</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Código:</label>
                                <p class="text-lg font-mono font-bold text-green-800">{{ selectedCur.renglon?.codigo || 'No disponible' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Nombre:</label>
                                <p class="text-lg">{{ selectedCur.renglon?.nombre || 'No disponible' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="bg-orange-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Descripción del Compromiso</h4>
                        <p class="text-lg leading-relaxed">{{ selectedCur.descripcion }}</p>
                    </div>

                    <!-- Usuario que registró -->
                    <div v-if="selectedCur.usuario" class="bg-purple-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Registrado por</h4>
                        <p class="text-lg font-semibold">{{ selectedCur.usuario.nombre || selectedCur.usuario.name || 'Usuario' }}</p>
                    </div>

                    <!-- Gestión de documentos -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Documentos</h4>
                        
                        <!-- Lista de documentos existentes -->
                        <div v-if="selectedCur.documentos && selectedCur.documentos.length > 0" class="mb-4 space-y-3">
                            <div v-for="documento in selectedCur.documentos" :key="documento.id" 
                                 class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200">
                                <div class="flex items-center flex-1">
                                    <svg class="h-8 w-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ documento.nombre_archivo }}</p>
                                        <p class="text-sm text-gray-500">{{ formatFileSize(documento.tamanio) }}</p>
                                        <p class="text-xs text-gray-400">Subido por: {{ documento.usuario?.nombre || documento.usuario?.name || 'Usuario' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button 
                                        @click="downloadSpecificDocument(documento)"
                                        class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                        title="Descargar"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button 
                                        @click="confirmDeleteSpecificDocument(documento)"
                                        class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                        title="Eliminar documento"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje si no hay documentos -->
                        <div v-if="!selectedCur.documentos || selectedCur.documentos.length === 0" class="mb-4">
                            <div class="p-4 bg-white rounded-lg border border-gray-200 text-center text-gray-500">
                                <svg class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p>No hay documentos adjuntos</p>
                            </div>
                        </div>

                        <!-- Agregar nuevo documento -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Agregar documento PDF
                                </label>
                                <input 
                                    @change="onDetailFileChange" 
                                    type="file" 
                                    accept=".pdf" 
                                    ref="detailFileInput"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                >
                                <p class="text-xs text-gray-500 mt-1">Máximo 10MB. Solo archivos PDF.</p>
                                <div v-if="detailFileErrors.length > 0" class="mt-2">
                                    <div v-for="error in detailFileErrors" :key="error" class="text-red-600 text-xs">
                                        {{ error }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botón para subir -->
                            <div v-if="selectedDetailFile" class="flex justify-end">
                                <button 
                                    @click="addDocument"
                                    :disabled="uploadingDocument"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                >
                                    {{ uploadingDocument ? 'Subiendo...' : 'Agregar documento' }}
                                </button>
                            </div>
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
import curService from '@/services/curService'
import proveedorService from '@/services/proveedorService'
import renglonService from '@/services/renglonService'
import documentoService from '@/services/documentoService'

// Estado principal
const loading = ref(true)
const curs = ref([])
const proveedores = ref([])
const renglones = ref([])

// Filtros
const filtroProveedor = ref('')
const filtroRenglon = ref('')

// Estado del modal
const showModal = ref(false)
const showEditModal = ref(false)
const submitting = ref(false)
const showDetailsModal = ref(false)
const selectedCur = ref(null)
const editingCur = ref(null)
const isEditing = ref(false)

// Variables para archivo (singular como facturas)
const selectedFile = ref(null)
const fileErrors = ref([])

// Variables para documentos en vista de detalles
const selectedDetailFile = ref(null)
const detailFileErrors = ref([])
const uploadingDocument = ref(false)

// Formulario
const form = ref({
    fecha_compromiso: new Date().toISOString().split('T')[0],
    numero_cur: '',
    proveedor_id: '',
    renglon_id: '',
    monto: 0,
    descripcion: ''
})

// Alertas
const alert = ref({
    show: false,
    type: 'success',
    message: ''
})

// Computadas
const filteredCurs = computed(() => {
    let filtered = curs.value

    if (filtroProveedor.value) {
        filtered = filtered.filter(c => c.proveedor_id == filtroProveedor.value)
    }

    if (filtroRenglon.value) {
        filtered = filtered.filter(c => c.renglon_id == filtroRenglon.value)
    }

    return filtered
})

// Cargar datos iniciales
onMounted(async () => {
    await Promise.all([
        loadCurs(),
        loadProveedores(),
        loadRenglones()
    ])
})

const loadCurs = async () => {
    try {
        loading.value = true
        const response = await curService.getAll()
        console.log('Respuesta CURs:', response)
        // El backend responde con {success: true, data: [...]}
        curs.value = response.data?.data || response.data || []
    } catch (error) {
        console.error('Error al cargar compromisos:', error)
        showAlert('error', 'Error al cargar compromisos')
    } finally {
        loading.value = false
    }
}

const loadProveedores = async () => {
    try {
        const response = await proveedorService.getAll()
        console.log('Respuesta proveedores:', response)
        // El backend responde con {success: true, data: [...]}
        proveedores.value = response.data?.data || response.data || []
    } catch (error) {
        console.error('Error al cargar proveedores:', error)
        showAlert('error', 'Error al cargar proveedores')
    }
}

const loadRenglones = async () => {
    try {
        const response = await renglonService.getAll()
        console.log('Respuesta renglones:', response)
        // El backend responde con {success: true, data: [...]}
        renglones.value = response.data?.data || response.data || []
    } catch (error) {
        console.error('Error al cargar renglones:', error)
        showAlert('error', 'Error al cargar renglones')
    }
}

const openCreateModal = () => {
    form.value = {
        fecha_compromiso: new Date().toISOString().split('T')[0],
        numero_cur: '',
        proveedor_id: '',
        renglon_id: '',
        monto: 0,
        descripcion: ''
    }
    selectedFile.value = null
    fileErrors.value = []
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    selectedFile.value = null
    fileErrors.value = []
}

const openEditModal = (cur) => {
    editingCur.value = cur
    isEditing.value = true
    
    // Cargar datos del CUR en el formulario
    form.value = {
        fecha_compromiso: cur.fecha_compromiso.split('T')[0], // Convertir fecha ISO a formato de input date
        numero_cur: cur.numero_cur,
        proveedor_id: cur.proveedor_id,
        renglon_id: cur.renglon_id,
        monto: cur.monto,
        descripcion: cur.descripcion
    }
    
    showEditModal.value = true
}

const closeEditModal = () => {
    showEditModal.value = false
    editingCur.value = null
    isEditing.value = false
    
    // Limpiar formulario
    form.value = {
        fecha_compromiso: new Date().toISOString().split('T')[0],
        numero_cur: '',
        proveedor_id: '',
        renglon_id: '',
        monto: 0,
        descripcion: ''
    }
}

const submitForm = async () => {
    try {
        submitting.value = true
        
        // Crear FormData igual que facturas
        const formData = new FormData()
        
        // Agregar datos del formulario
        Object.keys(form.value).forEach(key => {
            if (form.value[key] !== null && form.value[key] !== undefined) {
                formData.append(key, form.value[key])
            }
        })
        
        // Agregar archivo si existe (singular)
        if (selectedFile.value) {
            formData.append('documento', selectedFile.value)
        }
        
        // Crear el compromiso
        await curService.create(formData)
        
        showAlert('success', 'Compromiso creado correctamente')
        closeModal()
        await loadCurs()
    } catch (error) {
        console.error('Error al crear compromiso:', error)
        showAlert('error', error.response?.data?.message || 'Error al crear compromiso')
    } finally {
        submitting.value = false
    }
}

const updateForm = async () => {
    try {
        submitting.value = true
        
        // Para edición, enviar como JSON (sin archivos)
        await curService.update(editingCur.value.id, form.value)
        
        showAlert('success', 'Compromiso actualizado correctamente')
        closeEditModal()
        await loadCurs()
    } catch (error) {
        console.error('Error al actualizar compromiso:', error)
        showAlert('error', error.response?.data?.message || 'Error al actualizar compromiso')
    } finally {
        submitting.value = false
    }
}

const viewDetails = (cur) => {
    selectedCur.value = cur
    selectedDetailFile.value = null
    detailFileErrors.value = []
    showDetailsModal.value = true
}

const confirmAnular = (cur) => {
    if (confirm(`¿Estás seguro de que quieres anular el CUR ${cur.numero_cur}? Esta acción no se puede deshacer.`)) {
        anularCur(cur)
    }
}

const anularCur = async (cur) => {
    try {
        await curService.delete(cur.id)
        
        showAlert('success', 'Compromiso anulado correctamente')
        await loadCurs()
    } catch (error) {
        console.error('Error al anular compromiso:', error)
        showAlert('error', error.response?.data?.message || 'Error al anular compromiso')
    }
}

const filterByProveedor = () => {
    // La computada se encarga del filtrado
}

const filterByRenglon = () => {
    // La computada se encarga del filtrado
}

const clearFilters = () => {
    filtroProveedor.value = ''
    filtroRenglon.value = ''
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

// Método para manejo de archivo (igual que facturas)
const onFileChange = (event) => {
    const file = event.target.files[0]
    fileErrors.value = []
    
    if (!file) {
        selectedFile.value = null
        return
    }
    
    // Validar que sea PDF
    if (file.type !== 'application/pdf') {
        fileErrors.value.push('El archivo debe ser un PDF')
        selectedFile.value = null
        return
    }
    
    // Validar tamaño (10MB máximo)
    if (file.size > 10 * 1024 * 1024) {
        fileErrors.value.push('El archivo no puede ser mayor a 10MB')
        selectedFile.value = null
        return
    }
    
    selectedFile.value = file
}



const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Método simplificado igual que facturas
const downloadDocument = async (cur) => {
    try {
        window.open(`http://localhost:8000/api/cur/${cur.id}/documento`, '_blank')
    } catch (error) {
        console.error('Error al descargar documento:', error)
        showAlert('error', 'Error al descargar el documento')
    }
}

// Método para manejo de archivo en vista de detalles
const onDetailFileChange = (event) => {
    const file = event.target.files[0]
    detailFileErrors.value = []
    
    if (!file) {
        selectedDetailFile.value = null
        return
    }
    
    // Validar que sea PDF
    if (file.type !== 'application/pdf') {
        detailFileErrors.value.push('El archivo debe ser un PDF')
        selectedDetailFile.value = null
        return
    }
    
    // Validar tamaño (10MB máximo)
    if (file.size > 10 * 1024 * 1024) {
        detailFileErrors.value.push('El archivo no puede ser mayor a 10MB')
        selectedDetailFile.value = null
        return
    }
    
    selectedDetailFile.value = file
}

// Método para agregar nuevo documento (múltiples documentos)
const addDocument = async () => {
    if (!selectedDetailFile.value || !selectedCur.value) return
    
    try {
        uploadingDocument.value = true
        
        const formData = new FormData()
        formData.append('documentos[]', selectedDetailFile.value)
        
        // Usar el servicio de CUR para agregar documentos
        await curService.addDocuments(selectedCur.value.id, formData)
        
        showAlert('success', 'Documento agregado correctamente')
        
        // Recargar los datos del CUR específico
        await loadCurs()
        
        // Actualizar el CUR seleccionado con los nuevos datos
        const updatedCur = curs.value.find(c => c.id === selectedCur.value.id)
        if (updatedCur) {
            selectedCur.value = updatedCur
        }
        
        // Limpiar la selección de archivo
        selectedDetailFile.value = null
        detailFileErrors.value = []
        
        // Limpiar el input de archivo
        if (document.querySelector('input[ref="detailFileInput"]')) {
            document.querySelector('input[ref="detailFileInput"]').value = ''
        }
        
    } catch (error) {
        console.error('Error al agregar documento:', error)
        showAlert('error', error.response?.data?.message || 'Error al agregar el documento')
    } finally {
        uploadingDocument.value = false
    }
}

// Método para descargar documento específico
const downloadSpecificDocument = async (documento) => {
    try {
        window.open(`http://localhost:8000/api/documentos/${documento.id}/download`, '_blank')
    } catch (error) {
        console.error('Error al descargar documento:', error)
        showAlert('error', 'Error al descargar el documento')
    }
}

// Método para confirmar eliminación de documento específico
const confirmDeleteSpecificDocument = (documento) => {
    if (confirm('¿Estás seguro de que quieres eliminar este documento? Esta acción no se puede deshacer.')) {
        deleteSpecificDocument(documento)
    }
}

// Método para eliminar documento específico
const deleteSpecificDocument = async (documento) => {
    try {
        await documentoService.delete(documento.id)
        
        showAlert('success', 'Documento eliminado correctamente')
        
        // Recargar los datos
        await loadCurs()
        
        // Actualizar el CUR seleccionado
        const updatedCur = curs.value.find(c => c.id === selectedCur.value.id)
        if (updatedCur) {
            selectedCur.value = updatedCur
        }
        
    } catch (error) {
        console.error('Error al eliminar documento:', error)
        showAlert('error', error.response?.data?.message || 'Error al eliminar el documento')
    }
}
</script>