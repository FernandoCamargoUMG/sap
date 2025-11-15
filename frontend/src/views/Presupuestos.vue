<template>
    <AppLayout>
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 flex items-center">
                        <span class="bg-gradient-cfag bg-clip-text text-transparent">Presupuestos</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Gestiona los presupuestos por ejercicio fiscal</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="$router.push('/dashboard-presupuestario')"
                        class="bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:bg-gray-50 transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Dashboard Presupuestario
                    </button>
                    <button @click="openCreateModal"
                        class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Ejercicio Fiscal
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Ejercicio Fiscal</label>
                        <select v-model="filtroEjercicio" @change="filterByEjercicio"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">Todos los ejercicios</option>
                            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Buscar</label>
                        <input v-model="searchTerm" type="text" placeholder="Buscar por descripción..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
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

            <!-- Cards de Presupuestos -->
            <div v-else>
                <!-- Estado vacío -->
                <div v-if="filteredPresupuestos.length === 0" class="text-center py-20">
                    <div class="bg-white rounded-2xl shadow-xl p-12 border border-gray-200">
                        <svg class="mx-auto h-24 w-24 text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">No hay presupuestos</h3>
                        <p class="text-gray-600 mb-6">No se encontraron presupuestos que coincidan con los filtros seleccionados.</p>
                        <button @click="openCreateModal" class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            Crear el primer presupuesto
                        </button>
                    </div>
                </div>

                <!-- Grid de Cards -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div
                        v-for="presupuesto in filteredPresupuestos"
                        :key="presupuesto.id"
                        class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300"
                    >
                        <!-- Header del Card -->
                        <div class="bg-gradient-cfag px-6 py-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-2xl font-black text-white">
                                        {{ presupuesto.anio }}/{{ presupuesto.mes.toString().padStart(2, '0') }}
                                    </h3>
                                    <p class="text-blue-100 text-sm font-semibold">Ejercicio Fiscal</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-2 backdrop-blur-sm">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="px-6 py-4 border-b border-gray-100">
                            <p class="text-gray-800 font-semibold line-clamp-2">{{ presupuesto.descripcion }}</p>
                        </div>

                        <!-- Métricas Financieras -->
                        <div class="px-6 py-4 space-y-4">
                            <!-- Total Presupuestado -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-600">Total Presupuestado</span>
                                <span class="text-lg font-black text-blue-600">Q{{ formatMoney(presupuesto.total_presupuestado) }}</span>
                            </div>
                            
                            <!-- Total Ejecutado -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-600">Total Ejecutado</span>
                                <span class="text-lg font-black text-green-600">Q{{ formatMoney(presupuesto.total_ejecutado) }}</span>
                            </div>
                            
                            <!-- Total Pendiente -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-600">Total Pendiente</span>
                                <span class="text-lg font-black text-orange-600">Q{{ formatMoney(presupuesto.total_pendiente) }}</span>
                            </div>

                            <!-- Progreso de Ejecución -->
                            <div class="pt-2">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-bold text-gray-700">Progreso de Ejecución</span>
                                    <span :class="[
                                        'text-sm font-bold',
                                        presupuesto.porcentaje_ejecutado >= 80 ? 'text-red-600' :
                                        presupuesto.porcentaje_ejecutado >= 50 ? 'text-yellow-600' : 'text-green-600'
                                    ]">
                                        {{ presupuesto.porcentaje_ejecutado }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div :class="[
                                        'h-3 rounded-full transition-all duration-500',
                                        presupuesto.porcentaje_ejecutado >= 80 ? 'bg-red-500' :
                                        presupuesto.porcentaje_ejecutado >= 50 ? 'bg-yellow-500' : 'bg-green-500'
                                    ]" :style="`width: ${presupuesto.porcentaje_ejecutado}%`"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                            <div class="flex justify-center space-x-2">
                                <button 
                                    @click="openMovimientoModal(presupuesto)"
                                    class="flex-1 p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 transition-colors flex items-center justify-center"
                                    title="Crear Movimiento"
                                >
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <span class="text-xs font-semibold">Movimiento</span>
                                </button>
                                <button 
                                    @click="viewDetails(presupuesto)"
                                    class="flex-1 p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors flex items-center justify-center"
                                    title="Ver detalles"
                                >
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span class="text-xs font-semibold">Detalles</span>
                                </button>
                                <button 
                                    @click="openEditModal(presupuesto)"
                                    class="flex-1 p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors flex items-center justify-center"
                                    title="Editar"
                                >
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span class="text-xs font-semibold">Editar</span>
                                </button>
                                <button 
                                    @click="confirmDelete(presupuesto)"
                                    class="flex-1 p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors flex items-center justify-center"
                                    title="Eliminar"
                                >
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span class="text-xs font-semibold">Eliminar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modal Crear/Editar -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900">
                            {{ isEditing ? 'Editar Ejercicio Fiscal' : 'Nuevo Ejercicio Fiscal' }}
                        </h3>
                        <p class="text-gray-600 text-sm mt-1">
                            {{ isEditing ? 'Modifica la información del ejercicio fiscal' : 'Crea un nuevo ejercicio fiscal para gestionar presupuestos' }}
                        </p>
                    </div>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Información general -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Año *</label>
                            <input v-model="form.anio" type="number" :min="2020" :max="2050" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="2025">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Mes *</label>
                            <select v-model="form.mes" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción *</label>
                        <textarea v-model="form.descripcion" required rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            placeholder="Descripción del presupuesto..."></textarea>
                    </div>

                    <!-- Asignación de Renglones -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-bold text-gray-900">Asignación Presupuestaria por Renglón</h4>
                            <button type="button" @click="addRenglon"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                                + Agregar Renglón
                            </button>
                        </div>

                        <div v-if="form.renglones.length === 0" class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p>No hay renglones asignados</p>
                            <p class="text-sm">Agrega al menos un renglón con su monto presupuestado</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="(renglon, index) in form.renglones" :key="index"
                                class="grid grid-cols-1 md:grid-cols-12 gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <div class="md:col-span-5">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Renglón *</label>
                                    <select v-model="renglon.renglon_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                        <option value="">Seleccionar renglón</option>
                                        <option v-for="r in renglones" :key="r.id" :value="r.id"
                                            :disabled="form.renglones.some((fr, i) => i !== index && fr.renglon_id === r.id)">
                                            {{ r.codigo }} - {{ r.nombre }}
                                        </option>
                                    </select>
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Monto Asignado *</label>
                                    <input v-model="renglon.monto_asignado" type="number" step="0.01" min="0.01"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                        placeholder="0.00">
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Descripción</label>
                                    <input v-model="renglon.descripcion" type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                        placeholder="Descripción opcional">
                                </div>
                                <div class="md:col-span-1 flex items-end">
                                    <button type="button" @click="removeRenglon(index)"
                                        class="w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                                        ✕
                                    </button>
                                </div>
                            </div>

                            <!-- Total presupuestado -->
                            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-green-900">Total Presupuestado:</span>
                                    <span class="text-2xl font-black text-green-700">Q{{
                                        formatMoney(calcularTotalPresupuesto()) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos adjuntos -->
                    <div v-if="isEditing && form.id">
                        <DocumentUploader
                            ref="documentUploader"
                            title="Documentos de respaldo del presupuesto"
                            documentable-type="App\Models\PresupuestoCab"
                            :documentable-id="form.id"
                            :usuario-id="1"
                            @uploaded="handleDocumentUploaded"
                            @deleted="handleDocumentDeleted"
                            @error="handleDocumentError"
                        />
                    </div>
                    <div v-else class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                        <div class="text-center text-gray-600">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="font-semibold">Documentos de respaldo</p>
                            <p class="text-sm">Podrás subir documentos después de crear el presupuesto</p>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" @click="closeModal"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="px-6 py-3 bg-gradient-cfag text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 transition-all">
                            {{ submitting ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear Ejercicio') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Ver Detalles -->
        <div v-if="showDetailsModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900">Detalles del Presupuesto</h3>
                    <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedPresupuesto" class="space-y-6">
                    <!-- Información general -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-gray-50 rounded-xl">
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-1">Ejercicio Fiscal</label>
                            <p class="text-2xl font-black text-primary-600">{{ selectedPresupuesto.anio }}/{{
                                String(selectedPresupuesto.mes).padStart(2, '0') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-1">Fecha de Creación</label>
                            <p class="text-lg text-gray-900">{{ formatDate(selectedPresupuesto.fecha_creacion) }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-500 mb-1">Descripción</label>
                            <p class="text-gray-900">{{ selectedPresupuesto.descripcion }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-1">Monto Total</label>
                            <p class="text-3xl font-black text-green-600">Q{{
                                formatMoney(calcularMontoTotal(selectedPresupuesto)) }}</p>
                        </div>
                    </div>

                    <!-- Detalles por renglón -->
                    <div>
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Detalles por Renglón</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Código
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">
                                            Renglón</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Monto
                                            Asignado</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Saldo
                                            Disponible</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="detalle in selectedPresupuesto.detalles" :key="detalle.id"
                                        class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                            {{ detalle.renglon?.codigo }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ detalle.renglon?.nombre }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                            Q{{ formatMoney(detalle.monto_asignado) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">
                                            Q{{ formatMoney(detalle.saldo_disponible || detalle.monto_asignado) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sección de Movimientos -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-bold text-gray-900">Movimientos Ejecutados</h4>
                            <button 
                                @click="loadMovimientos(selectedPresupuesto.id)"
                                class="text-primary-600 hover:text-primary-700 text-sm font-semibold"
                            >
                                🔄 Actualizar
                            </button>
                        </div>
                        
                        <div v-if="movimientosPresupuesto.length === 0" class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <p>No se han registrado movimientos para este presupuesto</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Renglón</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Descripción</th>
                                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="movimiento in movimientosPresupuesto" :key="movimiento.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(movimiento.fecha) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                {{ movimiento.tipo_movimiento || 'Ejecución' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ movimiento.renglon }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-red-600">
                                            Q{{ formatMoney(movimiento.monto) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                            {{ movimiento.descripcion }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex justify-center space-x-2">
                                                <button 
                                                    @click="editarMovimiento(movimiento)"
                                                    class="text-blue-600 hover:text-blue-900 p-1 rounded"
                                                    title="Editar movimiento"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button 
                                                    @click="confirmarEliminarMovimiento(movimiento)"
                                                    class="text-red-600 hover:text-red-900 p-1 rounded"
                                                    title="Eliminar movimiento"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Confirmar Eliminación -->
        <div v-if="showDeleteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.733-2.755L13.382 4.224a2.25 2.25 0 00-3.764 0L2.349 15.245C1.581 16.833 2.54 18.5 4.08 18.5z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 mb-3">Confirmar Eliminación</h3>
                    <p class="text-gray-600 mb-6">
                        ¿Estás seguro de que deseas eliminar el presupuesto del ejercicio <strong>{{
                            presupuestoToDelete?.anio }}/{{ String(presupuestoToDelete?.mes).padStart(2, '0')
                            }}</strong>?
                        Esta acción se puede revertir posteriormente.
                    </p>
                    <div class="flex justify-center space-x-3">
                        <button @click="showDeleteModal = false"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button @click="deletePresupuesto" :disabled="deleting"
                            class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 disabled:opacity-50 transition-colors">
                            {{ deleting ? 'Eliminando...' : 'Eliminar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Movimiento -->
        <div v-if="showEditMovimientoModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900">Editar Movimiento</h3>
                    <button @click="showEditMovimientoModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="guardarMovimientoEditado" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Fecha *</label>
                        <input 
                            v-model="editMovimientoForm.fecha" 
                            type="date" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Monto *</label>
                        <input 
                            v-model="editMovimientoForm.monto" 
                            type="number" 
                            step="0.01" 
                            min="0.01" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción *</label>
                        <textarea 
                            v-model="editMovimientoForm.descripcion" 
                            rows="3" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="Descripción del movimiento..."
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Referencia</label>
                        <input 
                            v-model="editMovimientoForm.referencia" 
                            type="text" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="Número de factura, orden de compra, etc."
                        />
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button 
                            type="button" 
                            @click="showEditMovimientoModal = false"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit"
                            class="px-6 py-3 bg-primary-600 text-white rounded-xl font-semibold hover:bg-primary-700 transition-colors"
                        >
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Confirmar Eliminación de Movimiento -->
        <div v-if="showDeleteMovimientoModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.733-2.755L13.382 4.224a2.25 2.25 0 00-3.764 0L2.349 15.245C1.581 16.833 2.54 18.5 4.08 18.5z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 mb-3">Confirmar Eliminación</h3>
                    <p class="text-gray-600 mb-6">
                        ¿Estás seguro de que deseas eliminar este movimiento por <strong>Q{{
                            formatMoney(movimientoToDelete?.monto) }}</strong>?
                        <br><br>
                        <span class="text-sm text-red-600">⚠️ Esta acción no se puede deshacer</span>
                    </p>
                    <div class="flex justify-center space-x-3">
                        <button @click="showDeleteMovimientoModal = false"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button @click="eliminarMovimiento"
                            class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition-colors">
                            Eliminar Movimiento
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Crear Movimiento -->
        <div v-if="showMovimientoModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] shadow-2xl overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h3 class="text-2xl font-black text-gray-900">Crear Movimiento Presupuestario</h3>
                    <button @click="closeMovimientoModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
                    <form @submit.prevent="crearMovimiento" class="p-6 space-y-6">
                        <!-- Información del presupuesto -->
                        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-blue-700 mb-1">Ejercicio Fiscal</label>
                                    <p class="text-xl font-black text-blue-900">{{ movimientoForm.anio }}/{{
                                        String(movimientoForm.mes).padStart(2, '0') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-blue-700 mb-1">Descripción</label>
                                    <p class="text-sm text-blue-800">{{ presupuestoSeleccionado?.descripcion ||
                                        'Cargando...' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-blue-700 mb-1">Total Presupuesto</label>
                                    <p class="text-lg font-black text-green-700">Q{{
                                        formatMoney(calcularMontoTotal(presupuestoSeleccionado)) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Movimiento *</label>
                                <select v-model="movimientoForm.tipo" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500">
                                    <option value="ejecucion_presupuestaria">💰 Ejecución Presupuestaria</option>
                                    <option value="ajuste">⚖️ Ajuste</option>
                                    <option value="traslado">🔄 Traslado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Fecha *</label>
                                <input v-model="movimientoForm.fecha" type="date" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                            </div>
                        </div>

                        <!-- Selección de renglón del presupuesto -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Renglón del Presupuesto *</label>
                            <div v-if="!presupuestoSeleccionado?.detalles || presupuestoSeleccionado.detalles.length === 0"
                                class="text-center py-4 text-gray-500">
                                <p>Cargando renglones del presupuesto...</p>
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="detalle in presupuestoSeleccionado.detalles" :key="detalle.id" :class="[
                                    'border rounded-lg p-3 cursor-pointer transition-all',
                                    movimientoForm.renglon_id === detalle.renglon_id
                                        ? 'border-primary-500 bg-primary-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]" @click="seleccionarRenglon(detalle)">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-1">
                                                <input type="radio" :value="detalle.renglon_id"
                                                    v-model="movimientoForm.renglon_id" class="mr-2">
                                                <span class="font-bold text-primary-600">{{ detalle.renglon?.codigo
                                                    }}</span>
                                                <span class="ml-2 text-gray-900">{{ detalle.renglon?.nombre }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 ml-6">{{ detalle.renglon?.grupo }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold text-blue-600">Asignado: Q{{
                                                formatMoney(detalle.monto_asignado) }}</div>
                                            <div class="text-sm font-bold text-green-600">Disponible: Q{{
                                                formatMoney(calcularSaldoDetalle(detalle)) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Monto del Movimiento *</label>
                            <div class="relative">
                                <input v-model="movimientoForm.monto" type="number" step="0.01" min="0.01"
                                    :max="getSaldoDisponible()" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                    placeholder="0.00">
                                <div v-if="getSaldoDisponible() > 0" class="mt-1 text-xs text-gray-500">
                                    Saldo disponible: Q{{ formatMoney(getSaldoDisponible()) }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Descripción *</label>
                            <textarea v-model="movimientoForm.descripcion" required rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="Descripción detallada del movimiento..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Referencia</label>
                            <input v-model="movimientoForm.referencia" type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="Número de factura, orden de compra, etc.">
                        </div>

                        <!-- Documentos de respaldo -->
                        <div v-if="movimientoCreatedId">
                            <DocumentUploader
                                ref="movimientoDocumentUploader"
                                title="Documentos de respaldo del movimiento"
                                documentable-type="App\Models\MovimientoCab"
                                :documentable-id="movimientoCreatedId"
                                :usuario-id="1"
                                @uploaded="handleDocumentUploaded"
                                @deleted="handleDocumentDeleted"
                                @error="handleDocumentError"
                            />
                        </div>
                        <div v-else class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                            <div class="text-center text-gray-600">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="font-semibold">Documentos de respaldo</p>
                                <p class="text-sm">Los documentos se pueden subir después de crear el movimiento</p>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="button" @click="closeMovimientoModal"
                                class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="creandoMovimiento"
                                class="px-6 py-3 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 disabled:opacity-50 transition-colors">
                                {{ creandoMovimiento ? 'Creando...' : 'Crear Movimiento' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import DocumentUploader from '@/components/DocumentUploader.vue'
import presupuestoService from '@/services/presupuestoService'
import renglonService from '@/services/renglonService'

// Componentes
const components = {
  AppLayout,
  DocumentUploader
}

// Estado principal
const loading = ref(true)
const presupuestos = ref([])
const renglones = ref([])

// Filtros
const filtroEjercicio = ref('')
const searchTerm = ref('')

// Estado del modal
const showModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)

// Estado del modal de detalles
const showDetailsModal = ref(false)
const selectedPresupuesto = ref(null)

// Estado del modal de eliminación
const showDeleteModal = ref(false)
const presupuestoToDelete = ref(null)
const deleting = ref(false)

// Variables para gestión de movimientos
const movimientosPresupuesto = ref([])
const showEditMovimientoModal = ref(false)
const showDeleteMovimientoModal = ref(false)
const movimientoToEdit = ref(null)
const movimientoToDelete = ref(null)
const editMovimientoForm = ref({
    id: null,
    fecha: '',
    monto: 0,
    descripcion: '',
    referencia: ''
})

// Estado del modal de movimientos
const showMovimientoModal = ref(false)
const presupuestoSeleccionado = ref(null)
const movimientoCreatedId = ref(null)
const movimientoForm = ref({
    presupuesto_id: null,
    anio: null,
    mes: null,
    tipo: 'ejecucion_presupuestaria',
    renglon_id: null,
    monto: 0,
    fecha: new Date().toISOString().split('T')[0],
    descripcion: '',
    referencia: ''
})
const creandoMovimiento = ref(false)

// Referencias a componentes
const documentUploader = ref(null)
const movimientoDocumentUploader = ref(null)

// Formulario
const form = ref({
    anio: new Date().getFullYear(),
    mes: new Date().getMonth() + 1,
    descripcion: '',
    renglones: []
})

// Alertas
const alert = ref({
    show: false,
    type: 'success',
    message: ''
})

// Computadas
const availableYears = computed(() => {
    const years = [...new Set(presupuestos.value.map(p => p.anio))].sort((a, b) => b - a)
    return years.length > 0 ? years : [new Date().getFullYear()]
})

const filteredPresupuestos = computed(() => {
    let filtered = presupuestos.value

    if (filtroEjercicio.value) {
        filtered = filtered.filter(p => p.anio == filtroEjercicio.value)
    }

    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase()
        filtered = filtered.filter(p =>
            p.descripcion && p.descripcion.toLowerCase().includes(term)
        )
    }

    return filtered
})

// Cargar datos iniciales
onMounted(async () => {
    await Promise.all([
        loadPresupuestos(),
        loadRenglones()
    ])
})

const loadPresupuestos = async () => {
    try {
        loading.value = true

        // Cargar lista de presupuestos para la tabla
        const response = await presupuestoService.getAll()
        if (response.data && response.data.success && response.data.data) {
            presupuestos.value = response.data.data
            console.log('Presupuestos cargados:', presupuestos.value)

            if (presupuestos.value.length === 0) {
                showAlert('error', 'No hay presupuestos creados')
            }
        } else {
            presupuestos.value = []
            showAlert('error', 'No se pudieron cargar los presupuestos')
        }
    } catch (error) {
        console.error('Error al cargar presupuestos:', error)
        showAlert('error', 'Error al conectar con el backend: ' + (error.response?.data?.message || error.message))
        presupuestos.value = []
    } finally {
        loading.value = false
    }
}

// Cargar renglones
const loadRenglones = async () => {
    try {
        const response = await renglonService.getAll()

        // El backend devuelve los renglones en response.data.data
        if (response.data && response.data.success && response.data.data) {
            renglones.value = response.data.data
        } else {
            renglones.value = response.data.data || response.data.renglones || response.data || []
        }
    } catch (error) {
        console.error('Error al cargar renglones:', error)
        showAlert('error', 'Error al cargar renglones: ' + (error.message || 'Error desconocido'))
    }
}

// Abrir modal para crear
const openCreateModal = () => {
    isEditing.value = false
    form.value = {
        anio: new Date().getFullYear(),
        mes: new Date().getMonth() + 1,
        descripcion: '',
        renglones: []
    }
    showModal.value = true
}

// Abrir modal para editar
const openEditModal = (presupuesto) => {
    isEditing.value = true
    form.value = {
        id: presupuesto.id,
        anio: presupuesto.anio,
        mes: presupuesto.mes,
        descripcion: presupuesto.descripcion,
        renglones: presupuesto.detalles ? presupuesto.detalles.map(detalle => ({
            renglon_id: detalle.renglon_id,
            monto_asignado: detalle.monto_asignado,
            descripcion: detalle.descripcion || ''
        })) : []
    }
    showModal.value = true
}

// Ver detalles
const viewDetails = async (presupuesto) => {
    try {
        const response = await presupuestoService.getById(presupuesto.id)
        selectedPresupuesto.value = response.data.data || response.data.presupuesto || response.data
        
        // Cargar movimientos del presupuesto
        await loadMovimientos(presupuesto.id)
        
        showDetailsModal.value = true
    } catch (error) {
        console.error('Error al cargar detalles:', error)
        showAlert('error', 'Error al cargar detalles del presupuesto')
    }
}

// Cerrar modal
const closeModal = () => {
    showModal.value = false
    isEditing.value = false
    form.value = {
        anio: new Date().getFullYear(),
        mes: new Date().getMonth() + 1,
        descripcion: '',
        renglones: []
    }
}

// Funciones para manejar renglones en el formulario
const addRenglon = () => {
    form.value.renglones.push({
        renglon_id: '',
        monto_asignado: 0,
        descripcion: ''
    })
}

const removeRenglon = (index) => {
    form.value.renglones.splice(index, 1)
}

const calcularTotalPresupuesto = () => {
    return form.value.renglones.reduce((total, renglon) => {
        return total + (parseFloat(renglon.monto_asignado) || 0)
    }, 0)
}



// Calcular monto total del presupuesto (para compatibilidad)
const calcularMontoTotal = (presupuesto) => {
    // Si tiene el campo del backend restructurado, usarlo
    if (presupuesto.total_presupuestado !== undefined) {
        return presupuesto.total_presupuestado
    }

    // Fallback para compatibilidad con estructura anterior
    if (!presupuesto.detalles || presupuesto.detalles.length === 0) {
        return 0
    }
    return presupuesto.detalles.reduce((total, detalle) => {
        return total + (parseFloat(detalle.monto_asignado) || 0)
    }, 0)
}

// Enviar formulario
const submitForm = async () => {
    try {
        submitting.value = true

        // Validar que haya al menos un renglón
        if (form.value.renglones.length === 0) {
            showAlert('error', 'Debes agregar al menos un renglón con su monto presupuestado')
            return
        }

        // Validar que todos los renglones tengan datos válidos
        for (let i = 0; i < form.value.renglones.length; i++) {
            const renglon = form.value.renglones[i]
            if (!renglon.renglon_id) {
                showAlert('error', `Selecciona un renglón en la posición ${i + 1}`)
                return
            }
            if (!renglon.monto_asignado || renglon.monto_asignado <= 0) {
                showAlert('error', `Ingresa un monto válido para el renglón en la posición ${i + 1}`)
                return
            }
        }

        const formData = {
            anio: form.value.anio,
            mes: form.value.mes,
            descripcion: form.value.descripcion,
            renglones: form.value.renglones,
            usuario_id: 1 // Por ahora hardcodeado, luego usar auth
        }

        let presupuestoCreado = null

        if (isEditing.value && form.value.id) {
            await presupuestoService.update(form.value.id, formData)
            showAlert('success', 'Presupuesto actualizado correctamente')
            presupuestoCreado = { id: form.value.id }
        } else {
            const response = await presupuestoService.create(formData)
            showAlert('success', 'Presupuesto creado correctamente')
            presupuestoCreado = response.data.data
        }

        // Para nuevos presupuestos, no manejamos documentos aquí ya que solo se muestran en modo edición
        // Los documentos se pueden subir después editando el presupuesto

        closeModal()
        await loadPresupuestos()
    } catch (error) {
        console.error('Error al guardar presupuesto:', error)
        const message = error.response?.data?.message || 'Error al guardar presupuesto'
        showAlert('error', message)
    } finally {
        submitting.value = false
    }
}

// Filtrar por ejercicio
const filterByEjercicio = () => {
    // La computada se encarga del filtrado
}

// Limpiar filtros
const clearFilters = () => {
    filtroEjercicio.value = ''
    searchTerm.value = ''
}

// Abrir modal crear movimiento
const openMovimientoModal = async (presupuesto) => {
    try {
        // Cargar detalles completos del presupuesto
        const response = await presupuestoService.getById(presupuesto.id)
        presupuestoSeleccionado.value = response.data.data || response.data.presupuesto || response.data
        
        // Debug: verificar datos recibidos
        console.log('Datos del presupuesto para modal movimiento:', presupuestoSeleccionado.value)
        if (presupuestoSeleccionado.value?.detalles) {
            presupuestoSeleccionado.value.detalles.forEach((detalle, index) => {
                console.log(`Detalle ${index}:`, {
                    id: detalle.id,
                    renglon_id: detalle.renglon_id,
                    monto_asignado: detalle.monto_asignado,
                    monto_ejecutado: detalle.monto_ejecutado,
                    saldo_disponible: detalle.saldo_disponible
                })
            })
        }

        movimientoForm.value = {
            presupuesto_id: presupuesto.id,
            anio: presupuesto.anio || new Date().getFullYear(),
            mes: presupuesto.mes || new Date().getMonth() + 1,
            tipo: 'ejecucion_presupuestaria',
            renglon_id: null,
            monto: 0,
            fecha: new Date().toISOString().split('T')[0],
            descripcion: '',
            referencia: ''
        }
        showMovimientoModal.value = true
    } catch (error) {
        console.error('Error al cargar presupuesto:', error)
        showAlert('error', 'Error al cargar los detalles del presupuesto')
    }
}

// Cerrar modal crear movimiento
const closeMovimientoModal = () => {
    showMovimientoModal.value = false
    presupuestoSeleccionado.value = null
    movimientoCreatedId.value = null
    movimientoForm.value = {
        presupuesto_id: null,
        anio: null,
        mes: null,
        tipo: 'ejecucion_presupuestaria',
        renglon_id: null,
        monto: 0,
        fecha: new Date().toISOString().split('T')[0],
        descripcion: '',
        referencia: ''
    }
}

// Seleccionar renglón del presupuesto
const seleccionarRenglon = (detalle) => {
    movimientoForm.value.renglon_id = detalle.renglon_id
}

// Calcular saldo disponible de un detalle
const calcularSaldoDetalle = (detalle) => {
    if (!detalle) return 0
    
    // Usar saldo_disponible si existe, sino calcular manualmente
    if (detalle.saldo_disponible !== undefined && detalle.saldo_disponible !== null) {
        return parseFloat(detalle.saldo_disponible)
    }
    
    // Calcular manualmente si no viene el saldo_disponible
    const asignado = parseFloat(detalle.monto_asignado || 0)
    const ejecutado = parseFloat(detalle.monto_ejecutado || 0)
    return Math.max(0, asignado - ejecutado)
}

// Obtener saldo disponible del renglón seleccionado
const getSaldoDisponible = () => {
    if (!movimientoForm.value.renglon_id || !presupuestoSeleccionado.value?.detalles) {
        return 0
    }

    const detalle = presupuestoSeleccionado.value.detalles.find(
        d => d.renglon_id === movimientoForm.value.renglon_id
    )

    if (detalle) {
        const saldo = calcularSaldoDetalle(detalle)
        console.log('Calculando saldo disponible:', {
            renglon_id: detalle.renglon_id,
            monto_asignado: detalle.monto_asignado,
            monto_ejecutado: detalle.monto_ejecutado,
            saldo_disponible: detalle.saldo_disponible,
            calculo_manual: detalle.monto_asignado - (detalle.monto_ejecutado || 0),
            resultado_final: saldo
        })
        return saldo
    }

    return 0
}

// Crear movimiento
const crearMovimiento = async () => {
    try {
        creandoMovimiento.value = true

        // Validar que el monto no exceda el saldo disponible
        const saldoDisponible = getSaldoDisponible()
        if (movimientoForm.value.monto > saldoDisponible) {
            showAlert('error', `El monto no puede exceder el saldo disponible (Q${formatMoney(saldoDisponible)})`)
            return
        }

        // Importar el servicio de movimientos
        const movimientoService = (await import('@/services/movimientoService')).default

        const movimientoData = {
            anio: movimientoForm.value.anio,
            mes: movimientoForm.value.mes,
            tipo: movimientoForm.value.tipo,
            descripcion: movimientoForm.value.descripcion,
            referencia: movimientoForm.value.referencia,
            fecha: movimientoForm.value.fecha,
            renglon_id: movimientoForm.value.renglon_id,
            monto: movimientoForm.value.monto
        }

        const response = await movimientoService.create(movimientoData)
        const movimientoCreado = response.data.data

        showAlert('success', 'Movimiento creado correctamente')
        closeMovimientoModal()
        await loadPresupuestos()
    } catch (error) {
        console.error('Error al crear movimiento:', error)
        const message = error.response?.data?.message || 'Error al crear movimiento'
        showAlert('error', message)
    } finally {
        creandoMovimiento.value = false
    }
}

// Confirmar eliminación
const confirmDelete = (presupuesto) => {
    presupuestoToDelete.value = presupuesto
    showDeleteModal.value = true
}

// Eliminar presupuesto
const deletePresupuesto = async () => {
    try {
        deleting.value = true
        await presupuestoService.delete(presupuestoToDelete.value.id)
        showAlert('success', 'Presupuesto eliminado correctamente')
        showDeleteModal.value = false
        presupuestoToDelete.value = null
        await loadPresupuestos()
    } catch (error) {
        console.error('Error al eliminar presupuesto:', error)
        showAlert('error', 'Error al eliminar presupuesto')
    } finally {
        deleting.value = false
    }
}

// Manejar documentos
const handleDocumentUploaded = (documento) => {
    showAlert('success', 'Documento subido correctamente')
}

const handleDocumentDeleted = (documento) => {
    showAlert('info', 'Documento eliminado')
}

const handleDocumentError = (error) => {
    showAlert('error', error)
}

// Mostrar alerta
const showAlert = (type, message) => {
    alert.value = { show: true, type, message }
    setTimeout(() => {
        alert.value.show = false
    }, 5000)
}

// ========== GESTIÓN DE MOVIMIENTOS ==========

// Cargar movimientos de un presupuesto
const loadMovimientos = async (presupuestoId) => {
    try {
        // Los movimientos ya vienen en los detalles del presupuesto
        if (selectedPresupuesto.value && selectedPresupuesto.value.detalles) {
            console.log('Detalles del presupuesto:', selectedPresupuesto.value.detalles)
            const movimientos = []
            selectedPresupuesto.value.detalles.forEach(detalle => {
                console.log('Detalle:', detalle)
                console.log('Renglón del detalle:', detalle.renglon)
                if (detalle.movimientos && detalle.movimientos.length > 0) {
                    detalle.movimientos.forEach(movimiento => {
                        // Agregar información del detalle y renglón al movimiento
                        movimientos.push({
                            id: movimiento.id,
                            fecha: movimiento.movimiento_cab?.fecha || movimiento.created_at,
                            tipo: movimiento.movimiento_cab?.tipo_movimiento || 'ejecucion_presupuestaria',
                            renglon: detalle.renglon ? `${detalle.renglon.codigo} - ${detalle.renglon.nombre}` : `Renglón ${detalle.renglon_id}`,
                            monto: parseFloat(movimiento.monto),
                            descripcion: movimiento.descripcion_detalle || movimiento.movimiento_cab?.descripcion,
                            referencia: movimiento.movimiento_cab?.numero_documento || '',
                            movimiento_det_id: movimiento.id,
                            movimiento_cab_id: movimiento.movimiento_id,
                            renglon_id: detalle.renglon_id,
                            presupuesto_det_id: detalle.id
                        })
                    })
                }
            })
            movimientosPresupuesto.value = movimientos
        } else {
            movimientosPresupuesto.value = []
        }
    } catch (error) {
        console.error('Error al cargar movimientos:', error)
        movimientosPresupuesto.value = []
    }
}

// Editar movimiento
const editarMovimiento = (movimiento) => {
    console.log('Editando movimiento:', movimiento)
    movimientoToEdit.value = movimiento
    
    // Formatear fecha para input de tipo date
    let fechaFormateada = ''
    if (movimiento.fecha) {
        const fecha = new Date(movimiento.fecha)
        fechaFormateada = fecha.toISOString().split('T')[0]
    }
    
    editMovimientoForm.value = {
        id: movimiento.movimiento_cab_id || movimiento.id,
        fecha: fechaFormateada,
        monto: parseFloat(movimiento.monto || 0),
        descripcion: movimiento.descripcion || '',
        referencia: movimiento.referencia || ''
    }
    showEditMovimientoModal.value = true
}

// Confirmar eliminación de movimiento
const confirmarEliminarMovimiento = (movimiento) => {
    movimientoToDelete.value = movimiento
    showDeleteMovimientoModal.value = true
}

// Guardar cambios del movimiento editado
const guardarMovimientoEditado = async () => {
    try {
        const movimientoService = (await import('@/services/movimientoService')).default
        
        await movimientoService.update(editMovimientoForm.value.id, {
            fecha: editMovimientoForm.value.fecha,
            monto: editMovimientoForm.value.monto,
            descripcion: editMovimientoForm.value.descripcion,
            referencia: editMovimientoForm.value.referencia
        })

        showAlert('success', 'Movimiento actualizado correctamente')
        showEditMovimientoModal.value = false
        
        // Recargar los detalles del presupuesto para obtener movimientos actualizados
        await viewDetails(selectedPresupuesto.value)
        await loadPresupuestos() // Actualizar los totales
    } catch (error) {
        console.error('Error al actualizar movimiento:', error)
        showAlert('error', error.response?.data?.message || 'Error al actualizar movimiento')
    }
}

// Eliminar movimiento
const eliminarMovimiento = async () => {
    try {
        const movimientoService = (await import('@/services/movimientoService')).default
        
        await movimientoService.delete(movimientoToDelete.value.movimiento_cab_id || movimientoToDelete.value.id)

        showAlert('success', 'Movimiento eliminado correctamente')
        showDeleteMovimientoModal.value = false
        
        // Recargar los detalles del presupuesto para obtener movimientos actualizados
        await viewDetails(selectedPresupuesto.value)
        await loadPresupuestos() // Actualizar los totales
    } catch (error) {
        console.error('Error al eliminar movimiento:', error)
        showAlert('error', error.response?.data?.message || 'Error al eliminar movimiento')
    }
}

// Utilidades
const formatMoney = (amount) => {
    if (!amount) return '0.00'
    return parseFloat(amount).toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (dateString) => {
    if (!dateString) return '-'
    return new Date(dateString).toLocaleDateString('es-GT', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>