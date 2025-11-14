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
        <button @click="openCreateModal" class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nuevo Presupuesto
        </button>
      </div>

      <!-- Filtros -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
        <div class="flex flex-wrap gap-4 items-center">
          <div class="flex-1 min-w-48">
            <label class="block text-sm font-bold text-gray-700 mb-2">Ejercicio Fiscal</label>
            <select v-model="filtroEjercicio" @change="filterByEjercicio" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
              <option value="">Todos los ejercicios</option>
              <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
          <div class="flex-1 min-w-48">
            <label class="block text-sm font-bold text-gray-700 mb-2">Buscar</label>
            <input v-model="searchTerm" type="text" placeholder="Buscar por descripción..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
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
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Ejercicio</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Descripción</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Presupuestado</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Ejecutado</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Pendiente</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">% Ejecutado</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="presupuesto in filteredPresupuestos" :key="presupuesto.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-lg font-black text-primary-600">{{ presupuesto.anio }}/{{ presupuesto.mes.toString().padStart(2, '0') }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm font-bold text-gray-900">{{ presupuesto.descripcion }}</div>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span class="text-lg font-black text-blue-600">Q{{ formatMoney(presupuesto.total_presupuestado) }}</span>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span class="text-lg font-black text-green-600">Q{{ formatMoney(presupuesto.total_ejecutado) }}</span>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <span class="text-lg font-black text-orange-600">Q{{ formatMoney(presupuesto.total_pendiente) }}</span>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <div class="flex flex-col items-center">
                    <span :class="[
                      'text-sm font-bold',
                      presupuesto.porcentaje_ejecutado >= 80 ? 'text-red-600' : 
                      presupuesto.porcentaje_ejecutado >= 50 ? 'text-yellow-600' : 'text-green-600'
                    ]">
                      {{ presupuesto.porcentaje_ejecutado }}%
                    </span>
                    <div class="w-16 bg-gray-200 rounded-full h-2 mt-1">
                      <div :class="[
                        'h-2 rounded-full',
                        presupuesto.porcentaje_ejecutado >= 80 ? 'bg-red-500' : 
                        presupuesto.porcentaje_ejecutado >= 50 ? 'bg-yellow-500' : 'bg-green-500'
                      ]" :style="`width: ${presupuesto.porcentaje_ejecutado}%`"></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center space-x-1">
                    <button @click="openEjecutarGastoModal(presupuesto)" class="p-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 transition-colors" title="Ejecutar Gasto">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                      </svg>
                    </button>
                    <button @click="viewDetails(presupuesto)" class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors" title="Ver detalles">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button @click="openEditModal(presupuesto)" class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors" title="Editar">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="confirmDelete(presupuesto)" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors" title="Eliminar">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredPresupuestos.length === 0">
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <svg class="h-20 w-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <p class="text-xl font-semibold text-gray-600 mb-2">No hay presupuestos</p>
                    <p class="text-gray-500">Crea el primer presupuesto para comenzar</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal Crear/Editar -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-2xl font-black text-gray-900">
            {{ isEditing ? 'Editar Presupuesto' : 'Nuevo Presupuesto' }}
          </h3>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Información general -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Año *</label>
              <input
                v-model="form.anio"
                type="number"
                :min="2020"
                :max="2050"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                placeholder="2025"
              >
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Mes *</label>
              <select
                v-model="form.mes"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
              >
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
            <textarea
              v-model="form.descripcion"
              required
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
              placeholder="Descripción del presupuesto..."
            ></textarea>
          </div>

          <!-- Detalles del presupuesto -->
          <div>
            <div class="flex justify-between items-center mb-4">
              <h4 class="text-lg font-bold text-gray-900">Detalles por Renglón</h4>
              <button
                type="button"
                @click="addDetalle"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center"
              >
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Agregar Renglón
              </button>
            </div>

            <div class="space-y-4">
              <div v-for="(detalle, index) in form.detalles" :key="index" class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Renglón *</label>
                    <select
                      v-model="detalle.renglon_id"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                    >
                      <option value="">Seleccionar renglón</option>
                      <option v-for="renglon in renglones" :key="renglon.id" :value="renglon.id">
                        {{ renglon.codigo }} - {{ renglon.nombre }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Monto Asignado *</label>
                    <input
                      v-model="detalle.monto_asignado"
                      type="number"
                      step="0.01"
                      min="0"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                      placeholder="0.00"
                    >
                  </div>
                  <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Descripción</label>
                    <input
                      v-model="detalle.descripcion"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                      placeholder="Descripción del detalle (opcional)"
                    >
                  </div>
                  <div class="md:col-span-2 flex justify-end">
                    <button
                      type="button"
                      @click="removeDetalle(index)"
                      class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                    >
                      Eliminar Renglón
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Total calculado -->
            <div class="mt-4 p-4 bg-primary-50 rounded-xl">
              <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-gray-900">Total del Presupuesto:</span>
                <span class="text-2xl font-black text-primary-600">Q{{ formatMoney(calculateTotal()) }}</span>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="submitting || form.detalles.length === 0"
              class="px-6 py-3 bg-gradient-cfag text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 transition-all"
            >
              {{ submitting ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Ver Detalles -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-2xl font-black text-gray-900">Detalles del Presupuesto</h3>
          <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div v-if="selectedPresupuesto" class="space-y-6">
          <!-- Información general -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-gray-50 rounded-xl">
            <div>
              <label class="block text-sm font-bold text-gray-500 mb-1">Ejercicio Fiscal</label>
              <p class="text-2xl font-black text-primary-600">{{ selectedPresupuesto.anio }}/{{ String(selectedPresupuesto.mes).padStart(2, '0') }}</p>
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
              <p class="text-3xl font-black text-green-600">Q{{ formatMoney(calcularMontoTotal(selectedPresupuesto)) }}</p>
            </div>
          </div>

          <!-- Detalles por renglón -->
          <div>
            <h4 class="text-lg font-bold text-gray-900 mb-4">Detalles por Renglón</h4>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Código</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Renglón</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Monto Asignado</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Saldo Disponible</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="detalle in selectedPresupuesto.detalles" :key="detalle.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                      {{ detalle.renglon?.codigo }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      {{ detalle.renglon?.nombre }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                      Q{{ formatMoney(detalle.monto_asignado) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">
                      Q{{ formatMoney(detalle.saldo_disponible || detalle.monto_asignado) }}
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
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
        <div class="text-center">
          <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
            <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.733-2.755L13.382 4.224a2.25 2.25 0 00-3.764 0L2.349 15.245C1.581 16.833 2.54 18.5 4.08 18.5z" />
            </svg>
          </div>
          <h3 class="text-2xl font-black text-gray-900 mb-3">Confirmar Eliminación</h3>
          <p class="text-gray-600 mb-6">
            ¿Estás seguro de que deseas eliminar el presupuesto del ejercicio <strong>{{ presupuestoToDelete?.anio }}/{{ String(presupuestoToDelete?.mes).padStart(2, '0') }}</strong>?
            Esta acción se puede revertir posteriormente.
          </p>
          <div class="flex justify-center space-x-3">
            <button
              @click="showDeleteModal = false"
              class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button
              @click="deletePresupuesto"
              :disabled="deleting"
              class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 disabled:opacity-50 transition-colors"
            >
              {{ deleting ? 'Eliminando...' : 'Eliminar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Ejecutar Gasto -->
    <div v-if="showEjecutarGastoModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-2xl font-black text-gray-900">Ejecutar Gasto</h3>
          <button @click="closeEjecutarGastoModal" class="text-gray-500 hover:text-gray-700">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="ejecutarGasto" class="space-y-6">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Renglón *</label>
            <select
              v-model="ejecutarGastoForm.renglon_id"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
            >
              <option value="">Seleccionar renglón</option>
              <option v-for="renglon in renglones" :key="renglon.id" :value="renglon.id">
                {{ renglon.codigo }} - {{ renglon.nombre }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Monto a Ejecutar *</label>
            <input
              v-model="ejecutarGastoForm.monto"
              type="number"
              step="0.01"
              min="0.01"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
              placeholder="0.00"
            >
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Descripción *</label>
            <textarea
              v-model="ejecutarGastoForm.descripcion"
              required
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
              placeholder="Descripción del gasto ejecutado..."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="closeEjecutarGastoModal"
              class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="ejecutandoGasto"
              class="px-6 py-3 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 disabled:opacity-50 transition-colors"
            >
              {{ ejecutandoGasto ? 'Ejecutando...' : 'Ejecutar Gasto' }}
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
import presupuestoService from '@/services/presupuestoService'
import renglonService from '@/services/renglonService'

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

// Estado del modal de ejecutar gasto
const showEjecutarGastoModal = ref(false)
const ejecutarGastoForm = ref({
  anio: null,
  mes: null,
  renglon_id: null,
  monto: 0,
  descripcion: ''
})
const ejecutandoGasto = ref(false)

// Formulario
const form = ref({
  anio: new Date().getFullYear(),
  mes: new Date().getMonth() + 1,
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

// Cargar presupuestos reales de la base de datos
const loadPresupuestos = async () => {
  try {
    loading.value = true
    const response = await presupuestoService.getAll()
    
    // Usar solo datos reales del backend - presupuestos individuales
    if (response.data && response.data.success && response.data.data) {
      // Los datos vienen como un array de presupuestos reales
      presupuestos.value = response.data.data
      
      console.log('Presupuestos reales cargados:', presupuestos.value)
      
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
    detalles: []
  }
  showModal.value = true
  // Agregar un detalle por defecto
  addDetalle()
}

// Abrir modal para editar
const openEditModal = (presupuesto) => {
  isEditing.value = true
  form.value = {
    anio: presupuesto.anio,
    mes: presupuesto.mes,
    descripcion: presupuesto.descripcion,
    detalles: presupuesto.detalles?.map(d => ({
      id: d.id,
      renglon_id: d.renglon_id,
      monto_asignado: d.monto_asignado
    })) || []
  }
  showModal.value = true
}

// Ver detalles
const viewDetails = async (presupuesto) => {
  try {
    const response = await presupuestoService.getById(presupuesto.id)
    selectedPresupuesto.value = response.data.data || response.data.presupuesto || response.data
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
    detalles: []
  }
}

// Agregar detalle
const addDetalle = () => {
  form.value.detalles.push({
    renglon_id: '',
    monto_asignado: 0,
    descripcion: ''
  })
}

// Eliminar detalle
const removeDetalle = (index) => {
  form.value.detalles.splice(index, 1)
}

// Calcular total
const calculateTotal = () => {
  return form.value.detalles.reduce((total, detalle) => {
    return total + (parseFloat(detalle.monto_asignado) || 0)
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
    
    const formData = {
      anio: form.value.anio,
      mes: form.value.mes,
      descripcion: form.value.descripcion,
      renglones: form.value.detalles, // El backend espera 'renglones' no 'detalles'
      usuario_id: 1 // Por ahora hardcodeado, luego usar auth
    }

    if (isEditing.value) {
      // Encontrar el presupuesto actual para obtener el ID
      const presupuestoActual = presupuestos.value.find(p => 
        p.anio == form.value.anio && p.mes == form.value.mes
      )
      if (presupuestoActual) {
        await presupuestoService.update(presupuestoActual.id, formData)
        showAlert('success', 'Presupuesto actualizado correctamente')
      }
    } else {
      await presupuestoService.create(formData)
      showAlert('success', 'Presupuesto creado correctamente')
    }

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

// Abrir modal ejecutar gasto
const openEjecutarGastoModal = (presupuesto) => {
  // El backend espera anio y mes, no presupuesto_cab_id
  ejecutarGastoForm.value = {
    anio: presupuesto.anio,
    mes: presupuesto.mes,
    renglon_id: null,
    monto: 0,
    descripcion: ''
  }
  showEjecutarGastoModal.value = true
}

// Cerrar modal ejecutar gasto
const closeEjecutarGastoModal = () => {
  showEjecutarGastoModal.value = false
  ejecutarGastoForm.value = {
    anio: null,
    mes: null,
    renglon_id: null,
    monto: 0,
    descripcion: ''
  }
}

// Ejecutar gasto
const ejecutarGasto = async () => {
  try {
    ejecutandoGasto.value = true
    await presupuestoService.ejecutarGasto(ejecutarGastoForm.value)
    showAlert('success', 'Gasto ejecutado correctamente')
    closeEjecutarGastoModal()
    await loadPresupuestos()
  } catch (error) {
    console.error('Error al ejecutar gasto:', error)
    const message = error.response?.data?.message || 'Error al ejecutar gasto'
    showAlert('error', message)
  } finally {
    ejecutandoGasto.value = false
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

// Mostrar alerta
const showAlert = (type, message) => {
  alert.value = { show: true, type, message }
  setTimeout(() => {
    alert.value.show = false
  }, 5000)
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