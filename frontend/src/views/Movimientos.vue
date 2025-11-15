<template>
    <AppLayout>
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 flex items-center">
                        <span class="bg-gradient-cfag bg-clip-text text-transparent">Movimientos</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Gestiona los movimientos presupuestarios</p>
                </div>
                <button @click="openCreateModal"
                    class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Movimiento
                </button>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
                <div class="flex flex-wrap gap-4 items-center">
                    <div class="flex-1 min-w-48">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Movimiento</label>
                        <select v-model="filtroTipo" @change="filterByTipo"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="">Todos los tipos</option>
                            <option v-for="tipo in tiposMovimiento" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}
                            </option>
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

            <!-- Tabla -->
            <div v-else class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-cfag">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Fecha</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Tipo</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Descripción</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Monto Total</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Renglones</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Estado</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="movimiento in filteredMovimientos" :key="movimiento.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900">{{
                                        formatDate(movimiento.fecha_movimiento) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-bold',
                                        getTipoBadgeClass(movimiento.tipo)
                                    ]">
                                        {{ getTipoNombre(movimiento.tipo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">{{ movimiento.descripcion }}</div>
                                    <div v-if="movimiento.numero_documento" class="text-xs text-gray-500">
                                        Doc: {{ movimiento.numero_documento }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span :class="[
                                        'text-lg font-black',
                                        getMontoClass(movimiento.tipo)
                                    ]">
                                        {{ getMontoPrefix(movimiento.tipo) }}Q{{ formatMoney(movimiento.monto_total) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">
                                        {{ movimiento.detalles?.length || 0 }} renglones
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-bold',
                                        movimiento.estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]">
                                        {{ movimiento.estado === 1 ? 'Activo' : 'Anulado' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button @click="viewDetails(movimiento)"
                                            class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                            title="Ver detalles">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button v-if="movimiento.estado === 1" @click="confirmAnular(movimiento)"
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
                            <tr v-if="filteredMovimientos.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-20 w-20 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        <p class="text-xl font-semibold text-gray-600 mb-2">No hay movimientos</p>
                                        <p class="text-gray-500">Crea el primer movimiento para comenzar</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Modal Crear -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900">Nuevo Movimiento Presupuestario</h3>
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
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Movimiento *</label>
                            <select v-model="form.tipo" required @change="onTipoChange"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                <option value="">Seleccionar tipo</option>
                                <option v-for="tipo in tiposMovimiento" :key="tipo.id" :value="tipo.id">
                                    {{ tipo.nombre }}
                                </option>
                            </select>
                            <p v-if="form.tipo" class="text-xs text-gray-500 mt-1">
                                {{ getTipoDescripcion(form.tipo) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Fecha del Movimiento *</label>
                            <input v-model="form.fecha_movimiento" type="date" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Número de Documento</label>
                            <input v-model="form.numero_documento" type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="Ej: OF-001-2025">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Documento de Respaldo</label>
                            <input v-model="form.documento_respaldo" type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="Referencia del documento">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción *</label>
                        <textarea v-model="form.descripcion" required rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            placeholder="Descripción del movimiento..."></textarea>
                    </div>

                    <!-- Detalles del movimiento -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-bold text-gray-900">Detalles por Renglón</h4>
                            <button type="button" @click="addDetalle"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Agregar Renglón
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div v-for="(detalle, index) in form.detalles" :key="index"
                                class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Renglón *</label>
                                        <select v-model="detalle.renglon_id" required
                                            @change="checkSaldoDisponible(detalle, index)"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                                            <option value="">Seleccionar renglón</option>
                                            <option v-for="renglon in renglones" :key="renglon.id" :value="renglon.id">
                                                {{ renglon.codigo }} - {{ renglon.nombre }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Monto *</label>
                                        <input v-model="detalle.monto" type="number" step="0.01" min="0" required
                                            @input="checkSaldoDisponible(detalle, index)"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                                            placeholder="0.00">
                                        <div v-if="detalle.saldoDisponible !== null" class="text-xs mt-1">
                                            <span
                                                :class="detalle.saldoDisponible >= detalle.monto ? 'text-green-600' : 'text-red-600'">
                                                Saldo disponible: Q{{ formatMoney(detalle.saldoDisponible) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button" @click="removeDetalle(index)"
                                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total calculado -->
                        <div class="mt-4 p-4 bg-primary-50 rounded-xl">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-gray-900">Total del Movimiento:</span>
                                <span :class="[
                                    'text-2xl font-black',
                                    getMontoClass(form.tipo)
                                ]">
                                    {{ getMontoPrefix(form.tipo) }}Q{{ formatMoney(calculateTotal()) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" @click="closeModal"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="submitting || form.detalles.length === 0 || !canSubmit"
                            class="px-6 py-3 bg-gradient-cfag text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 transition-all">
                            {{ submitting ? 'Procesando...' : 'Crear Movimiento' }}
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
                    <h3 class="text-2xl font-black text-gray-900">Detalles del Movimiento</h3>
                    <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedMovimiento" class="space-y-6">
                    <!-- Información general -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-gray-50 rounded-xl">
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-1">Tipo</label>
                            <span :class="[
                                'px-3 py-1 rounded-full text-sm font-bold',
                                getTipoBadgeClass(selectedMovimiento.tipo)
                            ]">
                                {{ getTipoNombre(selectedMovimiento.tipo) }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-1">Fecha</label>
                            <p class="text-lg text-gray-900">{{ formatDate(selectedMovimiento.fecha_movimiento) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-1">Número de Documento</label>
                            <p class="text-gray-900">{{ selectedMovimiento.numero_documento || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-1">Documento de Respaldo</label>
                            <p class="text-gray-900">{{ selectedMovimiento.documento_respaldo || '-' }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-500 mb-1">Descripción</label>
                            <p class="text-gray-900">{{ selectedMovimiento.descripcion }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-500 mb-1">Monto Total</label>
                            <p :class="[
                                'text-3xl font-black',
                                getMontoClass(selectedMovimiento.tipo)
                            ]">
                                {{ getMontoPrefix(selectedMovimiento.tipo) }}Q{{
                                    formatMoney(selectedMovimiento.monto_total) }}
                            </p>
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
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">
                                            Efecto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="detalle in selectedMovimiento.detalles" :key="detalle.id"
                                        class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                            {{ detalle.renglon?.codigo }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ detalle.renglon?.nombre }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                            Q{{ formatMoney(detalle.monto) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <span :class="[
                                                'px-2 py-1 rounded text-xs font-bold',
                                                getEfectoClass(selectedMovimiento.tipo)
                                            ]">
                                                {{ getEfectoTexto(selectedMovimiento.tipo) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Confirmar Anulación -->
        <div v-if="showAnularModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.733-2.755L13.382 4.224a2.25 2.25 0 00-3.764 0L2.349 15.245C1.581 16.833 2.54 18.5 4.08 18.5z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 mb-3">Confirmar Anulación</h3>
                    <p class="text-gray-600 mb-6">
                        ¿Estás seguro de que deseas anular este movimiento?
                        <strong>Esta acción revertirá los saldos afectados</strong> y no se puede deshacer.
                    </p>
                    <div class="flex justify-center space-x-3">
                        <button @click="showAnularModal = false"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button @click="anularMovimiento" :disabled="anulando"
                            class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 disabled:opacity-50 transition-colors">
                            {{ anulando ? 'Anulando...' : 'Anular Movimiento' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import movimientoService from '@/services/movimientoService'
import renglonService from '@/services/renglonService'

// Estado principal
const loading = ref(true)
const movimientos = ref([])
const renglones = ref([])
const tiposMovimiento = ref([])

// Filtros
const filtroTipo = ref('')
const searchTerm = ref('')

// Estado del modal
const showModal = ref(false)
const submitting = ref(false)

// Estado del modal de detalles
const showDetailsModal = ref(false)
const selectedMovimiento = ref(null)

// Estado del modal de anulación
const showAnularModal = ref(false)
const movimientoToAnular = ref(null)
const anulando = ref(false)

// Formulario
const form = ref({
    tipo: '',
    fecha_movimiento: new Date().toISOString().split('T')[0],
    numero_documento: '',
    documento_respaldo: '',
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
const filteredMovimientos = computed(() => {
    let filtered = movimientos.value

    if (filtroTipo.value) {
        filtered = filtered.filter(m => m.tipo === filtroTipo.value)
    }

    if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase()
        filtered = filtered.filter(m =>
            m.descripcion.toLowerCase().includes(term) ||
            (m.numero_documento && m.numero_documento.toLowerCase().includes(term))
        )
    }

    return filtered
})

const canSubmit = computed(() => {
    return form.value.detalles.every(detalle => {
        if (!detalle.renglon_id || !detalle.monto) return false

        // Verificar saldo disponible para tipos que lo requieren
        const tiposQueRequierenSaldo = ['compromiso', 'devengado', 'egreso', 'reduccion']
        if (tiposQueRequierenSaldo.includes(form.value.tipo)) {
            return detalle.saldoDisponible !== null && detalle.saldoDisponible >= parseFloat(detalle.monto)
        }

        return true
    })
})

// Cargar datos iniciales
onMounted(async () => {
    await Promise.all([
        loadMovimientos(),
        loadRenglones(),
        loadTiposMovimiento()
    ])
})

// Cargar movimientos
const loadMovimientos = async () => {
    try {
        loading.value = true
        const response = await movimientoService.getAll()
        movimientos.value = response.data.movimientos || response.data
    } catch (error) {
        console.error('Error al cargar movimientos:', error)
        showAlert('error', 'Error al cargar movimientos')
    } finally {
        loading.value = false
    }
}

// Cargar renglones
const loadRenglones = async () => {
    try {
        const response = await renglonService.getAll()
        renglones.value = response.data.renglones || response.data
    } catch (error) {
        console.error('Error al cargar renglones:', error)
    }
}

// Cargar tipos de movimiento
const loadTiposMovimiento = () => {
    const response = movimientoService.getTipos()
    tiposMovimiento.value = response.data
}

// Abrir modal para crear
const openCreateModal = () => {
    form.value = {
        tipo: '',
        fecha_movimiento: new Date().toISOString().split('T')[0],
        numero_documento: '',
        documento_respaldo: '',
        descripcion: '',
        detalles: []
    }
    showModal.value = true
}

// Ver detalles
const viewDetails = async (movimiento) => {
    try {
        const response = await movimientoService.getById(movimiento.id)
        selectedMovimiento.value = response.data.movimiento || response.data
        showDetailsModal.value = true
    } catch (error) {
        console.error('Error al cargar detalles:', error)
        showAlert('error', 'Error al cargar detalles del movimiento')
    }
}

// Cerrar modal
const closeModal = () => {
    showModal.value = false
    form.value = {
        tipo: '',
        fecha_movimiento: new Date().toISOString().split('T')[0],
        numero_documento: '',
        documento_respaldo: '',
        descripcion: '',
        detalles: []
    }
}

// Agregar detalle
const addDetalle = () => {
    form.value.detalles.push({
        renglon_id: '',
        monto: 0,
        saldoDisponible: null
    })
}

// Eliminar detalle
const removeDetalle = (index) => {
    form.value.detalles.splice(index, 1)
}

// Calcular total
const calculateTotal = () => {
    return form.value.detalles.reduce((total, detalle) => {
        return total + (parseFloat(detalle.monto) || 0)
    }, 0)
}

// Verificar saldo disponible
const checkSaldoDisponible = async (detalle, index) => {
    if (!detalle.renglon_id) {
        detalle.saldoDisponible = null
        return
    }

    try {
        const response = await renglonService.getSaldo(detalle.renglon_id)
        detalle.saldoDisponible = response.data.saldo_disponible || 0
    } catch (error) {
        console.error('Error al obtener saldo:', error)
        detalle.saldoDisponible = 0
    }
}

// Cambio de tipo
const onTipoChange = () => {
    // Limpiar detalles cuando cambia el tipo
    form.value.detalles = []
}

// Enviar formulario
const submitForm = async () => {
    try {
        submitting.value = true

        const formData = {
            ...form.value,
            monto_total: calculateTotal()
        }

        await movimientoService.create(formData)
        showAlert('success', 'Movimiento creado correctamente')
        closeModal()
        await loadMovimientos()
    } catch (error) {
        console.error('Error al guardar movimiento:', error)
        const message = error.response?.data?.message || 'Error al guardar movimiento'
        showAlert('error', message)
    } finally {
        submitting.value = false
    }
}

// Confirmar anulación
const confirmAnular = (movimiento) => {
    movimientoToAnular.value = movimiento
    showAnularModal.value = true
}

// Anular movimiento
const anularMovimiento = async () => {
    try {
        anulando.value = true
        await movimientoService.delete(movimientoToAnular.value.id)
        showAlert('success', 'Movimiento anulado correctamente')
        showAnularModal.value = false
        movimientoToAnular.value = null
        await loadMovimientos()
    } catch (error) {
        console.error('Error al anular movimiento:', error)
        showAlert('error', 'Error al anular movimiento')
    } finally {
        anulando.value = false
    }
}

// Filtrar por tipo
const filterByTipo = () => {
    // La computada se encarga del filtrado
}

// Limpiar filtros
const clearFilters = () => {
    filtroTipo.value = ''
    searchTerm.value = ''
}

// Mostrar alerta
const showAlert = (type, message) => {
    alert.value = { show: true, type, message }
    setTimeout(() => {
        alert.value.show = false
    }, 5000)
}

// Utilidades
const getTipoNombre = (tipo) => {
    const tipoObj = tiposMovimiento.value.find(t => t.id === tipo)
    return tipoObj ? tipoObj.nombre : tipo
}

const getTipoDescripcion = (tipo) => {
    const tipoObj = tiposMovimiento.value.find(t => t.id === tipo)
    return tipoObj ? tipoObj.descripcion : ''
}

const getTipoBadgeClass = (tipo) => {
    const classes = {
        'ampliacion': 'bg-green-100 text-green-800',
        'reduccion': 'bg-red-100 text-red-800',
        'compromiso': 'bg-blue-100 text-blue-800',
        'devengado': 'bg-purple-100 text-purple-800',
        'egreso': 'bg-gray-100 text-gray-800',
        'liberacion': 'bg-yellow-100 text-yellow-800',
        'reintegro': 'bg-indigo-100 text-indigo-800'
    }
    return classes[tipo] || 'bg-gray-100 text-gray-800'
}

const getMontoClass = (tipo) => {
    const aumentan = ['ampliacion', 'liberacion', 'reintegro']
    return aumentan.includes(tipo) ? 'text-green-600' : 'text-red-600'
}

const getMontoPrefix = (tipo) => {
    const aumentan = ['ampliacion', 'liberacion', 'reintegro']
    return aumentan.includes(tipo) ? '+' : '-'
}

const getEfectoClass = (tipo) => {
    const aumentan = ['ampliacion', 'liberacion', 'reintegro']
    return aumentan.includes(tipo) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
}

const getEfectoTexto = (tipo) => {
    const efectos = {
        'ampliacion': 'Aumenta saldo',
        'reduccion': 'Reduce saldo',
        'compromiso': 'Compromete recursos',
        'devengado': 'Devenga gasto',
        'egreso': 'Pago efectivo',
        'liberacion': 'Libera compromiso',
        'reintegro': 'Reintegra fondos'
    }
    return efectos[tipo] || 'Afecta saldo'
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