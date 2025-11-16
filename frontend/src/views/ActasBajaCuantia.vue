<template>
  <AppLayout>
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-black text-gray-900 flex items-center">
            <span class="bg-gradient-cfag bg-clip-text text-transparent">Actas de Baja Cuantía</span>
          </h1>
          <p class="text-gray-600 mt-2">Gestión de actas de procesos de contratación de baja cuantía</p>
        </div>
        <button
          @click="mostrarModalCrear = true"
          class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center"
        >
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Nueva Acta
        </button>
      </div>
      
      <!-- Filtros -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
        <div class="flex flex-wrap gap-4 items-center">
          <div class="flex-1 min-w-48">
            <label class="block text-sm font-bold text-gray-700 mb-2">Buscar por número o descripción</label>
            <input
              v-model="filtros.busqueda"
              type="text"
              placeholder="Buscar..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
            >
          </div>
          <div class="flex-1 min-w-48">
            <label class="block text-sm font-bold text-gray-700 mb-2">Proveedor</label>
            <select
              v-model="filtros.proveedorId"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
            >
              <option value="">Todos los proveedores</option>
              <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                {{ proveedor.nombre }}
              </option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="limpiarFiltros"
              class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
              Limpiar Filtros
            </button>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="cargando" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-primary-600"></div>
      </div>

      <!-- Lista de Actas -->
      <div v-else-if="actasFiltradas.length > 0" class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-cfag">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Número Acta</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Fecha</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Proveedor</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">Descripción</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Monto</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Documentos</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="acta in actasFiltradas" :key="acta.id" class="hover:bg-blue-50 transition-colors duration-200">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="font-bold text-gray-900">{{ acta.numero_acta }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ formatDate(acta.fecha_acta) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ acta.proveedor?.nombre || 'N/A' }}</div>
                  <div class="text-sm text-gray-500">{{ acta.proveedor?.nit || '' }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 max-w-xs truncate">{{ acta.descripcion_compra }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-bold rounded-full">
                    {{ formatCurrency(acta.monto_total) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="text-sm font-semibold text-gray-600">{{ acta.documentos?.length || 0 }} documentos</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="flex space-x-2 justify-center">
                    <button
                      @click="verDetalles(acta)"
                      class="bg-blue-100 hover:bg-blue-200 text-blue-600 p-2 rounded-lg transition-all duration-200 transform hover:scale-110"
                      title="Ver detalles"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                    <button
                      @click="editarActa(acta)"
                      class="bg-green-100 hover:bg-green-200 text-green-600 p-2 rounded-lg transition-all duration-200 transform hover:scale-110"
                      title="Editar"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                    <button
                      @click="confirmarEliminacion(acta)"
                      class="bg-red-100 hover:bg-red-200 text-red-600 p-2 rounded-lg transition-all duration-200 transform hover:scale-110"
                      title="Eliminar"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Sin resultados -->
      <div v-else class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl shadow-xl border border-indigo-200 p-12 text-center">
        <div class="bg-gradient-cfag rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6 shadow-lg">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-3">No hay actas registradas</h3>
        <p class="text-gray-600 mb-6 text-lg">Comienza creando tu primera acta de baja cuantía</p>
        <button
          @click="mostrarModalCrear = true"
          class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200"
        >
          Crear Primera Acta
        </button>
      </div>

    <!-- Modal Crear/Editar Acta -->
    <div v-if="mostrarModalCrear || mostrarModalEditar" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <!-- Header del Modal -->
          <div class="flex justify-between items-center pb-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ mostrarModalEditar ? 'Editar Acta de Baja Cuantía' : 'Nueva Acta de Baja Cuantía' }}
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

          <!-- Formulario -->
          <form @submit.prevent="guardarActa" class="mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              
              <!-- Número de Acta -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Número de Acta *</label>
                <input
                  v-model="formulario.numero_acta"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Ej: AB-2024-001"
                >
              </div>

              <!-- Fecha del Acta -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del Acta *</label>
                <input
                  v-model="formulario.fecha_acta"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
              </div>

              <!-- Proveedor -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor *</label>
                <select
                  v-model="formulario.proveedor_id"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Seleccionar proveedor</option>
                  <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                    {{ proveedor.nombre }} - {{ proveedor.nit }}
                  </option>
                </select>
              </div>

              <!-- Descripción de la Compra -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción de la Compra *</label>
                <textarea
                  v-model="formulario.descripcion_compra"
                  required
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Descripción detallada de los bienes o servicios a adquirir"
                ></textarea>
              </div>

              <!-- Monto Total -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Monto Total *</label>
                <input
                  v-model="formulario.monto_total"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="0.00"
                >
              </div>

              <!-- Estado -->
              <!--<div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select
                  v-model="formulario.estado"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="1">Activo</option>
                  <option value="0">Inactivo</option>
                </select>
              </div>-->

              <!-- Detalle -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Detalle</label>
                <textarea
                  v-model="formulario.detalle"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Detalles adicionales del proceso"
                ></textarea>
              </div>

              <!-- Contenido del Acta -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contenido del Acta</label>
                <textarea
                  v-model="formulario.contenido_acta"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Contenido detallado del acta"
                ></textarea>
              </div>

              <!-- Responsable -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Responsable</label>
                <input
                  v-model="formulario.responsable"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Nombre del responsable"
                >
              </div>

              <!-- Cargo del Responsable -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cargo del Responsable</label>
                <input
                  v-model="formulario.cargo_responsable"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Cargo del responsable"
                >
              </div>

              <!-- Documento -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Documento (PDF)</label>
                <input
                  ref="fileInput"
                  type="file"
                  accept=".pdf"
                  @change="manejarArchivoSeleccionado"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <p class="text-sm text-gray-500 mt-1">Solo archivos PDF, máximo 10MB</p>
              </div>
            </div>

            <!-- Botones del Modal -->
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
              <button
                type="button"
                @click="cerrarModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="guardando"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors disabled:opacity-50"
              >
                {{ guardando ? 'Guardando...' : (mostrarModalEditar ? 'Actualizar' : 'Crear') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Detalles -->
    <div v-if="mostrarModalDetalles" class="fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
      <div class="relative w-full max-w-6xl bg-white rounded-2xl shadow-2xl border border-gray-200 max-h-[90vh] overflow-hidden">
        <!-- Header Elegante -->
        <div class="bg-gradient-cfag px-8 py-6 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
              <div class="bg-white rounded-full p-3 shadow-lg">
                <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <div>
                <h3 class="text-2xl font-black text-white">
                  Acta {{ actaSeleccionada?.numero_acta }}
                </h3>
                <p class="text-blue-100 text-sm">Detalles completos del acta de baja cuantía</p>
              </div>
            </div>
            <button
              @click="mostrarModalDetalles = false"
              class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-xl transition-all duration-200"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>

        <!-- Contenido Scrollable -->
        <div class="overflow-y-auto max-h-[calc(90vh-120px)] px-8 py-6">
          <!-- Información Principal -->
          <div v-if="actaSeleccionada" class="space-y-8">
            
            <!-- Tarjetas de Información Básica -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <!-- Número de Acta -->
              <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                <div class="flex items-center space-x-3">
                  <div class="bg-blue-500 rounded-full p-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-blue-600">Número de Acta</p>
                    <p class="text-xl font-black text-blue-900">{{ actaSeleccionada.numero_acta }}</p>
                  </div>
                </div>
              </div>

              <!-- Fecha -->
              <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                <div class="flex items-center space-x-3">
                  <div class="bg-purple-500 rounded-full p-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-purple-600">Fecha del Acta</p>
                    <p class="text-xl font-black text-purple-900">{{ formatDate(actaSeleccionada.fecha_acta) }}</p>
                  </div>
                </div>
              </div>

              <!-- Monto -->
              <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                <div class="flex items-center space-x-3">
                  <div class="bg-green-500 rounded-full p-2">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-green-600">Monto Total</p>
                    <p class="text-xl font-black text-green-900">{{ formatCurrency(actaSeleccionada.monto_total) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Información del Proveedor -->
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-6 border border-indigo-200">
              <h4 class="text-lg font-black text-indigo-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Información del Proveedor
              </h4>
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-lg font-bold text-gray-900">{{ actaSeleccionada.proveedor?.nombre || 'N/A' }}</p>
                <p class="text-sm text-gray-600">NIT: {{ actaSeleccionada.proveedor?.nit || 'N/A' }}</p>
                <p class="text-sm text-gray-600" v-if="actaSeleccionada.proveedor?.direccion">{{ actaSeleccionada.proveedor.direccion }}</p>
              </div>
            </div>

            <!-- Descripción de la Compra -->
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-6 border border-yellow-200">
              <h4 class="text-lg font-black text-yellow-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Descripción de la Compra
              </h4>
              <div class="bg-white rounded-lg p-4 shadow-sm">
                <p class="text-gray-900 leading-relaxed">{{ actaSeleccionada.descripcion_compra }}</p>
              </div>
            </div>

            <!-- Estado y Responsable -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Estado -->
              <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <h5 class="text-sm font-bold text-gray-700 mb-3">Estado del Acta</h5>
                <span :class="actaSeleccionada.estado == 1 ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200'" 
                      class="px-4 py-2 text-sm font-bold rounded-full border inline-flex items-center">
                  <div :class="actaSeleccionada.estado == 1 ? 'bg-green-500' : 'bg-red-500'" class="w-2 h-2 rounded-full mr-2"></div>
                  {{ actaSeleccionada.estado == 1 ? 'Activo' : 'Inactivo' }}
                </span>
              </div>

              <!-- Responsable -->
              <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm" v-if="actaSeleccionada.responsable">
                <h5 class="text-sm font-bold text-gray-700 mb-3">Responsable</h5>
                <p class="text-lg font-semibold text-gray-900">{{ actaSeleccionada.responsable }}</p>
                <p class="text-sm text-gray-600" v-if="actaSeleccionada.cargo_responsable">{{ actaSeleccionada.cargo_responsable }}</p>
              </div>
            </div>

            <!-- Detalles y Contenido -->
            <div class="space-y-6" v-if="actaSeleccionada.detalle || actaSeleccionada.contenido_acta">
              <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm" v-if="actaSeleccionada.detalle">
                <h5 class="text-lg font-black text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Detalle
                </h5>
                <p class="text-gray-900 leading-relaxed">{{ actaSeleccionada.detalle }}</p>
              </div>

              <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm" v-if="actaSeleccionada.contenido_acta">
                <h5 class="text-lg font-black text-gray-900 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Contenido del Acta
                </h5>
                <p class="text-gray-900 leading-relaxed whitespace-pre-wrap">{{ actaSeleccionada.contenido_acta }}</p>
              </div>
            </div>

            <!-- Documentos con Diseño Moderno -->
            <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl p-6 border border-slate-200 mt-6">
              <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-black text-slate-900 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Documentos Adjuntos
                </h4>
                <button
                  @click="abrirSelectorDocumento"
                  class="group inline-flex items-center px-4 py-3 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl hover:from-blue-700 hover:to-blue-800 transform hover:scale-105 transition-all duration-200 shadow-lg"
                >
                  <svg class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                  </svg>
                  Subir Documento
                </button>
              </div>

              <input
                ref="documentoInput"
                type="file"
                accept=".pdf"
                @change="subirDocumento"
                class="hidden"
              >

              <div v-if="actaSeleccionada.documentos && actaSeleccionada.documentos.length > 0" class="space-y-3">
                <div
                  v-for="documento in actaSeleccionada.documentos"
                  :key="documento.id"
                  class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                      <div class="bg-red-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <div>
                        <p class="font-bold text-gray-900">{{ documento.nombre_archivo }}</p>
                        <p class="text-sm text-gray-600">
                          Subido por: <span class="font-medium">{{ documento.usuario?.nombre || 'Sistema' }}</span> - 
                          {{ formatDate(documento.created_at) }}
                        </p>
                      </div>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="descargarDocumento(documento.id)"
                        class="group p-2 text-blue-600 bg-blue-50 border border-blue-200 rounded-full hover:bg-blue-100 hover:text-blue-700 transition-all duration-200"
                        title="Descargar"
                      >
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                      </button>
                      <button
                        @click="confirmarEliminacionDocumento(documento.id)"
                        class="group p-2 text-red-600 bg-red-50 border border-red-200 rounded-full hover:bg-red-100 hover:text-red-700 transition-all duration-200"
                        title="Eliminar"
                      >
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-12">
                <div class="bg-gray-100 rounded-full p-4 w-20 h-20 mx-auto mb-4">
                  <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </div>
                <p class="text-gray-500 font-medium">No hay documentos adjuntos</p>
                <p class="text-sm text-gray-400 mt-1">Sube archivos PDF para adjuntarlos a esta acta</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Pie del Modal con Diseño Moderno -->
        <div class="bg-gradient-to-r from-gray-50 to-slate-50 border-t border-gray-200 px-8 py-6 rounded-b-2xl">
          <div class="flex justify-end">
            <button 
              @click="mostrarModalDetalles = false" 
              class="group inline-flex items-center px-6 py-3 text-sm font-bold text-gray-700 bg-white border-2 border-gray-300 rounded-xl hover:bg-gray-50 hover:border-gray-400 transform hover:scale-105 transition-all duration-200 shadow-lg"
            >
              <svg class="w-4 h-4 mr-2 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>
    </main>
  </AppLayout>
</template>

<script>
import AppLayout from '@/components/AppLayout.vue'
import actaBajaCuantiaService from '@/services/actaBajaCuantiaService'

export default {
  name: 'ActasBajaCuantia',
  components: {
    AppLayout
  },
  data() {
    return {
      actas: [],
      proveedores: [],
      cargando: false,
      guardando: false,

      // Modales
      mostrarModalCrear: false,
      mostrarModalEditar: false,
      mostrarModalDetalles: false,
      actaSeleccionada: null,

      // Filtros
      filtros: {
        busqueda: '',
        proveedorId: ''
      },

      // Formulario
      formulario: {
        numero_acta: '',
        fecha_acta: '',
        proveedor_id: '',
        descripcion_compra: '',
        monto_total: '',
        detalle: '',
        contenido_acta: '',
        responsable: '',
        cargo_responsable: '',
        estado: 1,
        documento: null
      }
    }
  },

  computed: {
    actasFiltradas() {
      let actas = [...this.actas]

      // Filtro por búsqueda
      if (this.filtros.busqueda.trim()) {
        const busqueda = this.filtros.busqueda.toLowerCase()
        actas = actas.filter(acta => 
          acta.numero_acta.toLowerCase().includes(busqueda) ||
          acta.descripcion_compra.toLowerCase().includes(busqueda)
        )
      }

      // Filtro por proveedor
      if (this.filtros.proveedorId) {
        actas = actas.filter(acta => acta.proveedor_id == this.filtros.proveedorId)
      }

      return actas
    }
  },

  async mounted() {
    await this.cargarDatos()
  },

  methods: {
    async cargarDatos() {
      this.cargando = true
      try {
        await Promise.all([
          this.cargarActas(),
          this.cargarProveedores()
        ])
      } catch (error) {
        console.error('Error al cargar datos:', error)
        this.$toast.error('Error al cargar los datos')
      } finally {
        this.cargando = false
      }
    },

    async cargarActas() {
      try {
        const response = await actaBajaCuantiaService.getAll()
        if (response.success) {
          this.actas = response.data
        }
      } catch (error) {
        console.error('Error al cargar actas:', error)
        throw error
      }
    },

    async cargarProveedores() {
      try {
        const response = await actaBajaCuantiaService.getProveedores()
        if (response.success) {
          this.proveedores = response.data
        }
      } catch (error) {
        console.error('Error al cargar proveedores:', error)
        throw error
      }
    },

    limpiarFiltros() {
      this.filtros = {
        busqueda: '',
        proveedorId: ''
      }
    },

    limpiarFormulario() {
      this.formulario = {
        numero_acta: '',
        fecha_acta: '',
        proveedor_id: '',
        descripcion_compra: '',
        monto_total: '',
        detalle: '',
        contenido_acta: '',
        responsable: '',
        cargo_responsable: '',
        estado: 1,
        documento: null
      }
      
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = ''
      }
    },

    cerrarModal() {
      this.mostrarModalCrear = false
      this.mostrarModalEditar = false
      this.actaSeleccionada = null
      this.limpiarFormulario()
    },

    manejarArchivoSeleccionado(evento) {
      const archivo = evento.target.files[0]
      if (archivo) {
        const validacion = actaBajaCuantiaService.validatePdfFile(archivo)
        if (validacion.valid) {
          this.formulario.documento = archivo
        } else {
          this.$toast.error(validacion.message)
          evento.target.value = ''
          this.formulario.documento = null
        }
      }
    },

    async guardarActa() {
      this.guardando = true
      try {
        let response
        
        if (this.mostrarModalEditar) {
          response = await actaBajaCuantiaService.update(this.actaSeleccionada.id, this.formulario)
        } else {
          response = await actaBajaCuantiaService.create(this.formulario)
        }

        if (response.success) {
          this.$toast.success(response.message)
          await this.cargarActas()
          this.cerrarModal()
        }
      } catch (error) {
        console.error('Error al guardar acta:', error)
        if (error.response?.data?.message) {
          this.$toast.error(error.response.data.message)
        } else {
          this.$toast.error('Error al guardar el acta')
        }
      } finally {
        this.guardando = false
      }
    },

    editarActa(acta) {
      this.actaSeleccionada = acta
      this.formulario = {
        numero_acta: acta.numero_acta,
        fecha_acta: acta.fecha_acta,
        proveedor_id: acta.proveedor_id,
        descripcion_compra: acta.descripcion_compra,
        monto_total: acta.monto_total,
        detalle: acta.detalle || '',
        contenido_acta: acta.contenido_acta || '',
        responsable: acta.responsable || '',
        cargo_responsable: acta.cargo_responsable || '',
        estado: acta.estado,
        documento: null
      }
      this.mostrarModalEditar = true
    },

    async verDetalles(acta) {
      try {
        const response = await actaBajaCuantiaService.getById(acta.id)
        if (response.success) {
          this.actaSeleccionada = response.data
          this.mostrarModalDetalles = true
        }
      } catch (error) {
        console.error('Error al cargar detalles:', error)
        this.$toast.error('Error al cargar los detalles del acta')
      }
    },

    confirmarEliminacion(acta) {
      if (confirm(`¿Estás seguro de que deseas eliminar el acta "${acta.numero_acta}"?`)) {
        this.eliminarActa(acta.id)
      }
    },

    async eliminarActa(id) {
      try {
        const response = await actaBajaCuantiaService.delete(id)
        if (response.success) {
          this.$toast.success(response.message)
          await this.cargarActas()
        }
      } catch (error) {
        console.error('Error al eliminar acta:', error)
        this.$toast.error('Error al eliminar el acta')
      }
    },

    // Métodos para documentos
    abrirSelectorDocumento() {
      this.$refs.documentoInput.click()
    },

    async subirDocumento(evento) {
      const archivo = evento.target.files[0]
      if (!archivo) return

      const validacion = actaBajaCuantiaService.validatePdfFile(archivo)
      if (!validacion.valid) {
        this.$toast.error(validacion.message)
        return
      }

      try {
        const response = await actaBajaCuantiaService.uploadDocument(this.actaSeleccionada.id, archivo)
        if (response.success) {
          this.$toast.success(response.message)
          // Recargar detalles del acta
          await this.verDetalles(this.actaSeleccionada)
        }
      } catch (error) {
        console.error('Error al subir documento:', error)
        this.$toast.error('Error al subir el documento')
      } finally {
        evento.target.value = ''
      }
    },

    async descargarDocumento(documentoId) {
      try {
        const response = await actaBajaCuantiaService.downloadDocument(documentoId)
        
        // Crear un enlace temporal para descargar
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        
        // Extraer el nombre del archivo de los headers
        const contentDisposition = response.headers['content-disposition']
        let filename = 'documento.pdf'
        if (contentDisposition) {
          const filenameMatch = contentDisposition.match(/filename="(.+)"/)
          if (filenameMatch) {
            filename = filenameMatch[1]
          }
        }
        
        link.setAttribute('download', filename)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
      } catch (error) {
        console.error('Error al descargar documento:', error)
        this.$toast.error('Error al descargar el documento')
      }
    },

    confirmarEliminacionDocumento(documentoId) {
      if (confirm('¿Estás seguro de que deseas eliminar este documento?')) {
        this.eliminarDocumento(documentoId)
      }
    },

    async eliminarDocumento(documentoId) {
      try {
        const response = await actaBajaCuantiaService.deleteDocument(documentoId)
        if (response.success) {
          this.$toast.success(response.message)
          // Recargar detalles del acta
          await this.verDetalles(this.actaSeleccionada)
        }
      } catch (error) {
        console.error('Error al eliminar documento:', error)
        this.$toast.error('Error al eliminar el documento')
      }
    },

    // Métodos de formato
    formatDate(date) {
      return actaBajaCuantiaService.formatDate(date)
    },

    formatCurrency(amount) {
      return actaBajaCuantiaService.formatCurrency(amount)
    }
  }
}
</script>