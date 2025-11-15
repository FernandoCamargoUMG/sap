<template>
  <AppLayout>
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-4xl font-black text-gray-900 flex items-center">
            <span class="bg-gradient-cfag bg-clip-text text-transparent">Gestión de Facturas</span>
          </h1>
          <p class="text-gray-600 mt-2">Administra facturas con documentos PDF adjuntos</p>
        </div>
        <div class="flex space-x-4">
          <button @click="abrirModalReportes"
            class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Reportes
          </button>
          <button @click="abrirModalCrear"
            class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Factura
          </button>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Proveedor</label>
            <select v-model="filtros.proveedor_id" @change="aplicarFiltros"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
              <option value="">Todos los proveedores</option>
              <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                {{ proveedor.nombre }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Tipo</label>
            <select v-model="filtros.tipo" @change="aplicarFiltros"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
              <option value="">Todos los tipos</option>
              <option value="inventario">Inventario</option>
              <option value="bodega">Bodega</option>
              <option value="despensa">Despensa</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Fecha Desde</label>
            <input v-model="filtros.fecha_desde" @change="aplicarFiltros" type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Fecha Hasta</label>
            <input v-model="filtros.fecha_hasta" @change="aplicarFiltros" type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Folio</label>
            <input v-model="filtros.folio" @input="buscarPorFolio" type="text" placeholder="Buscar por folio..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
          </div>
        </div>
        <div class="flex justify-end mt-4">
          <button @click="limpiarFiltros"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200">
            <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Alertas -->
      <div v-if="alert.show" :class="[
        'mb-6 p-4 rounded-xl border-l-4 flex items-center justify-between',
        alert.type === 'success' ? 'bg-green-50 border-green-500 text-green-800' : 'bg-red-50 border-red-500 text-red-800'
      ]">
        <div class="flex items-center">
          <svg v-if="alert.type === 'success'" class="h-5 w-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg v-else class="h-5 w-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          {{ alert.message }}
        </div>
        <button @click="alert.show = false" class="text-current hover:text-opacity-75">
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>

      <!-- Cargando -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <p class="mt-2 text-gray-600">Cargando facturas...</p>
      </div>

      <!-- Lista de Facturas -->
      <div v-else class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 backdrop-blur-sm">
        <!-- Header de la tabla con estadísticas -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
              <div class="text-sm text-gray-600">
                <span class="font-semibold text-gray-900">{{ paginacion.total || facturas.length }}</span> facturas encontradas
              </div>
              <div v-if="filtros.proveedor_id || filtros.tipo || filtros.fecha_desde || filtros.fecha_hasta || filtros.folio" 
                class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                Filtros aplicados
              </div>
            </div>
            <div class="text-sm text-gray-500">
              <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Gestión de Facturas
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-cfag text-white">
              <tr>
                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider">
                  <div class="flex items-center space-x-1">
                    <span>Folio</span>
                    <svg class="h-3 w-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider">
                  <div class="flex items-center space-x-1">
                    <span>Proveedor</span>
                    <svg class="h-3 w-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider">
                  <div class="flex items-center space-x-1">
                    <span>Fecha</span>
                    <svg class="h-3 w-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider">
                  <div class="flex items-center space-x-1">
                    <span>Tipo</span>
                    <svg class="h-3 w-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider">
                  <div class="flex items-center space-x-1">
                    <span>Total</span>
                    <svg class="h-3 w-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider">
                  <div class="flex items-center space-x-1">
                    <span>Documento</span>
                    <svg class="h-3 w-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-5 text-center text-xs font-bold uppercase tracking-wider">
                  <div class="flex items-center justify-center space-x-1">
                    <span>Acciones</span>
                    <svg class="h-3 w-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                    </svg>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(factura, index) in facturasFiltradas" :key="factura.id" 
                class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 border-l-4 border-transparent hover:border-l-blue-400">
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">
                      {{ String(factura.folio).slice(-2) }}
                    </div>
                    <div>
                      <div class="text-sm font-bold text-gray-900 group-hover:text-blue-800 transition-colors">{{ factura.folio }}</div>
                      <div class="text-xs text-gray-500">Factura #{{ index + 1 }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                      <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900 group-hover:text-blue-800 transition-colors">{{ factura.proveedor?.nombre || 'N/A' }}</div>
                      <div class="text-xs text-gray-500">NIT: {{ factura.proveedor?.nit || 'N/A' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                      <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ formatDate(factura.fecha) }}</div>
                      <div class="text-xs text-gray-500">Fecha de emisión</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full shadow-sm border',
                    getTipoClasses(factura.tipo)
                  ]">
                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    {{ getTipoLabel(factura.tipo) }}
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 bg-emerald-100 rounded-full flex items-center justify-center mr-3">
                      <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                      </svg>
                    </div>
                    <div>
                      <div class="text-sm font-bold text-emerald-700 group-hover:text-emerald-800 transition-colors">{{ formatMoney(factura.total) }}</div>
                      <div class="text-xs text-gray-500">Total factura</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div v-if="factura.documento" class="flex items-center space-x-2">
                    <!-- Indicador PDF -->
                    <div class="flex-shrink-0 h-8 w-8 bg-red-100 rounded-lg flex items-center justify-center">
                      <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    
                    <!-- Botones de acción compactos -->
                    <div class="flex space-x-1">
                      <!-- Botón descargar -->
                      <div class="relative">
                        <button @click="descargarDocumento(factura.id)"
                          @mouseenter="$event.target.nextElementSibling.classList.remove('opacity-0')"
                          @mouseleave="$event.target.nextElementSibling.classList.add('opacity-0')"
                          class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-all duration-200 border border-transparent hover:border-blue-200">
                          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3" />
                          </svg>
                        </button>
                        <!-- Tooltip solo para este botón -->
                        <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 opacity-0 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                          Descargar PDF
                        </div>
                      </div>
                      
                      <!-- Botón eliminar -->
                      <div class="relative">
                        <button @click="confirmarEliminarDocumento(factura)"
                          @mouseenter="$event.target.nextElementSibling.classList.remove('opacity-0')"
                          @mouseleave="$event.target.nextElementSibling.classList.add('opacity-0')"
                          class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-all duration-200 border border-transparent hover:border-red-200">
                          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                        <!-- Tooltip solo para este botón -->
                        <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 opacity-0 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                          Eliminar PDF
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Estado sin documento -->
                  <div v-else class="flex items-center space-x-2">
                    <div class="flex-shrink-0 h-8 w-8 bg-gray-100 rounded-lg flex items-center justify-center">
                      <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <div class="text-xs text-gray-400">
                      <div class="font-medium">Sin documento</div>
                      <div class="text-[10px]">No adjuntado</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                  <div class="flex justify-center space-x-2">
                    <div class="relative">
                      <button @click="verFactura(factura.id)"
                        @mouseenter="$event.target.nextElementSibling.classList.remove('opacity-0')"
                        @mouseleave="$event.target.nextElementSibling.classList.add('opacity-0')"
                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-3 rounded-xl transition-all duration-200 border border-transparent hover:border-blue-200 hover:shadow-md">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                      <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 opacity-0 transition-opacity whitespace-nowrap pointer-events-none z-10">
                        Ver detalles
                      </div>
                    </div>
                    <div class="relative">
                      <button @click="editarFactura(factura)"
                        @mouseenter="$event.target.nextElementSibling.classList.remove('opacity-0')"
                        @mouseleave="$event.target.nextElementSibling.classList.add('opacity-0')"
                        class="text-amber-600 hover:text-amber-800 hover:bg-amber-50 p-3 rounded-xl transition-all duration-200 border border-transparent hover:border-amber-200 hover:shadow-md">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 opacity-0 transition-opacity whitespace-nowrap pointer-events-none z-10">
                        Editar factura
                      </div>
                    </div>
                    <div class="relative">
                      <button @click="eliminarFactura(factura.id)"
                        @mouseenter="$event.target.nextElementSibling.classList.remove('opacity-0')"
                        @mouseleave="$event.target.nextElementSibling.classList.add('opacity-0')"
                        class="text-red-600 hover:text-red-800 hover:bg-red-50 p-3 rounded-xl transition-all duration-200 border border-transparent hover:border-red-200 hover:shadow-md">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                      <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 opacity-0 transition-opacity whitespace-nowrap pointer-events-none z-10">
                        Eliminar factura
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
              <tr v-if="facturasFiltradas.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="text-gray-500">
                    <div class="mx-auto h-20 w-20 bg-gradient-to-br from-blue-100 to-indigo-200 rounded-full flex items-center justify-center mb-6">
                      <svg class="h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">No hay facturas registradas</h3>
                    <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                      {{ filtros.proveedor_id || filtros.tipo || filtros.fecha_desde || filtros.fecha_hasta || filtros.folio 
                        ? 'No se encontraron facturas que coincidan con los filtros aplicados.' 
                        : 'Comienza creando tu primera factura para gestionar tus documentos financieros.' 
                      }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                      <button @click="abrirModalCrear" 
                        class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Crear primera factura
                      </button>
                      <button v-if="filtros.proveedor_id || filtros.tipo || filtros.fecha_desde || filtros.fecha_hasta || filtros.folio" 
                        @click="limpiarFiltros"
                        class="px-6 py-3 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Limpiar filtros
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación mejorada -->
        <div v-if="paginacion.total > paginacion.per_page" class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t border-gray-200">
          <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
            <!-- Información de registros -->
            <div class="flex items-center space-x-4">
              <div class="text-sm text-gray-700 bg-white px-3 py-2 rounded-lg shadow-sm border border-gray-200">
                <svg class="h-4 w-4 inline mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Mostrando <span class="font-semibold text-blue-600">{{ paginacion.from }}</span> - 
                <span class="font-semibold text-blue-600">{{ paginacion.to }}</span> de 
                <span class="font-semibold text-blue-600">{{ paginacion.total }}</span> facturas
              </div>
              
              <!-- Selector de registros por página -->
              <div class="flex items-center space-x-2">
                <label class="text-sm text-gray-600">Ver:</label>
                <select v-model="filtros.per_page" @change="aplicarFiltros" 
                  class="text-sm border border-gray-300 rounded-lg px-3 py-1 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
                <span class="text-sm text-gray-600">registros</span>
              </div>
            </div>

            <!-- Controles de paginación -->
            <div class="flex items-center space-x-2">
              <!-- Botón primera página -->
              <button v-if="paginacion.current_page > 1" @click="cargarPagina(paginacion.first_page_url)"
                class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
              </button>

              <!-- Botón página anterior -->
              <button v-if="paginacion.prev_page_url" @click="cargarPagina(paginacion.prev_page_url)"
                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm flex items-center">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Anterior
              </button>

              <!-- Números de página -->
              <div class="flex space-x-1">
                <button v-for="link in paginacion.links" :key="link.label" 
                  v-if="link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                  @click="cargarPagina(link.url)"
                  :disabled="!link.url" 
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 shadow-sm',
                    link.active
                      ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg transform scale-105'
                      : link.url
                        ? 'text-gray-700 bg-white border border-gray-300 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300'
                        : 'text-gray-400 bg-gray-100 cursor-not-allowed border border-gray-200'
                  ]" 
                  v-html="link.label">
                </button>
              </div>

              <!-- Botón página siguiente -->
              <button v-if="paginacion.next_page_url" @click="cargarPagina(paginacion.next_page_url)"
                class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm flex items-center">
                Siguiente
                <svg class="h-4 w-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>

              <!-- Botón última página -->
              <button v-if="paginacion.current_page < paginacion.last_page" @click="cargarPagina(paginacion.last_page_url)"
                class="px-3 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Información adicional -->
          <div class="mt-3 pt-3 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 space-y-2 sm:space-y-0">
            <div class="flex items-center space-x-4">
              <span>Página {{ paginacion.current_page }} de {{ paginacion.last_page }}</span>
              <span>•</span>
              <span>{{ paginacion.per_page }} registros por página</span>
            </div>
            <div class="flex items-center space-x-2">
              <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Navega con las flechas del teclado</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Reportes -->
      <div v-if="modalReportesAbierto" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex justify-center items-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-screen overflow-y-auto border border-gray-200">
          <div class="sticky top-0 bg-white border-b border-gray-200 px-8 py-6 rounded-t-2xl">
            <div class="flex justify-between items-center">
              <h2 class="text-2xl font-bold text-gray-900">Generar Reportes de Facturas</h2>
              <button @click="cerrarModalReportes"
                class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <div class="p-8">
            <!-- Filtros de fecha -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Fecha</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                  <input type="date" v-model="filtroReporte.fechaInicio" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                  <input type="date" v-model="filtroReporte.fechaFin" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                </div>
              </div>
              <div class="mt-4 flex justify-end">
                <button @click="cargarFacturasParaReporte" :disabled="loadingReporte"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:bg-blue-300 disabled:cursor-not-allowed transition-all duration-200 flex items-center">
                  <svg v-if="!loadingReporte" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  <svg v-else class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ loadingReporte ? 'Cargando...' : 'Actualizar Datos' }}
                </button>
              </div>
            </div>

            <!-- Tipo de reporte -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Tipo de Reporte</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all duration-200">
                  <input type="radio" v-model="filtroReporte.tipoReporte" value="resumen" id="resumen"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                  <label for="resumen" class="ml-3 text-sm font-medium text-gray-700">Resumen de Facturas</label>
                </div>
                <div class="flex items-center p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-all duration-200">
                  <input type="radio" v-model="filtroReporte.tipoReporte" value="detallado" id="detallado"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                  <label for="detallado" class="ml-3 text-sm font-medium text-gray-700">Reporte Detallado</label>
                </div>
              </div>
            </div>

            <!-- Vista previa de datos -->
            <div v-if="facturasParaReporte.length > 0" class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Vista Previa</h3>
              <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-sm text-gray-600">Se encontraron <strong>{{ facturasParaReporte.length }}</strong> facturas en el rango seleccionado</p>
                <p class="text-sm text-gray-600 mt-1">Total: <strong>Q{{ totalFacturasFiltradas.toFixed(2) }}</strong></p>
              </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
              <button @click="cerrarModalReportes"
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-all duration-200">
                Cancelar
              </button>
              <button @click="generarReportePDF" :disabled="facturasParaReporte.length === 0"
                class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-all duration-200 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Generar PDF
              </button>
              <button @click="generarReporteExcel" :disabled="facturasParaReporte.length === 0"
                class="px-6 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-all duration-200 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Generar Excel
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de Crear/Editar -->
      <div v-if="mostrarModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-screen overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">
                {{ modoEdicion ? 'Editar Factura' : 'Nueva Factura' }}
              </h2>
              <button @click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <form @submit.prevent="guardarFactura" class="space-y-6">
              <!-- Datos básicos -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Proveedor *</label>
                  <select v-model="formulario.proveedor_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    <option value="">Seleccionar proveedor</option>
                    <option v-for="proveedor in proveedores" :key="proveedor.id" :value="proveedor.id">
                      {{ proveedor.nombre }} - {{ proveedor.nit }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Folio *</label>
                  <input v-model="formulario.folio" type="text" required placeholder="Número de factura"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Fecha *</label>
                  <input v-model="formulario.fecha" type="date" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                  <label class="block text-sm font-bold text-gray-700 mb-2">Tipo *</label>
                  <select v-model="formulario.tipo" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    <option value="">Seleccionar tipo</option>
                    <option value="inventario">Inventario</option>
                    <option value="bodega">Bodega</option>
                    <option value="despensa">Despensa</option>
                  </select>
                </div>
              </div>

              <!-- Documento PDF -->
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Documento PDF</label>
                <input @change="onFileChange" type="file" accept=".pdf" ref="fileInput"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                <p class="text-xs text-gray-500 mt-1">Opcional. Máximo 10MB. Solo archivos PDF.</p>
                <div v-if="erroresArchivo.length > 0" class="mt-2">
                  <div v-for="error in erroresArchivo" :key="error" class="text-red-600 text-xs">
                    {{ error }}
                  </div>
                </div>
              </div>

              <!-- Detalles de la factura -->
              <div>
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-lg font-bold text-gray-900">Detalles de la Factura</h3>
                  <button type="button" @click="agregarDetalle"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-all duration-200 flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Agregar Item
                  </button>
                </div>

                <div v-if="formulario.detalles.length === 0" class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                  <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  <p>No hay items en la factura</p>
                  <p class="text-sm">Agrega al menos un item para continuar</p>
                </div>

                <div v-else class="space-y-4">
                  <div v-for="(detalle, index) in formulario.detalles" :key="index"
                    class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                      <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Renglón *</label>
                        <select v-model="detalle.renglon_id" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                          <option value="">Seleccionar renglón</option>
                          <option v-for="renglon in renglones" :key="renglon.id" :value="renglon.id">
                            {{ renglon.codigo }} - {{ renglon.nombre }}
                          </option>
                        </select>
                      </div>
                      <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción *</label>
                        <input v-model="detalle.item" type="text" required placeholder="Descripción del item"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                      </div>
                      <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Cantidad *</label>
                        <input v-model.number="detalle.cantidad" type="number" min="1" required
                          @input="calcularSubtotal(index)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                      </div>
                      <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Precio Unit. *</label>
                        <input v-model.number="detalle.precio_unitario" type="number" min="0" step="0.01" required
                          @input="calcularSubtotal(index)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                      </div>
                    </div>
                    <div class="flex justify-between items-center mt-4">
                      <div class="text-sm font-bold text-gray-700">
                        Subtotal: {{ formatMoney(detalle.subtotal || 0) }}
                      </div>
                      <button type="button" @click="eliminarDetalle(index)"
                        class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-all duration-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <!-- Total -->
                  <div class="bg-primary-50 p-4 rounded-lg border border-primary-200">
                    <div class="text-right">
                      <span class="text-lg font-bold text-primary-800">
                        Total: {{ formatMoney(totalFactura) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Botones -->
              <div class="flex justify-end space-x-4">
                <button type="button" @click="cerrarModal"
                  class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200">
                  Cancelar
                </button>
                <button type="submit" :disabled="guardando || formulario.detalles.length === 0"
                  class="px-6 py-3 bg-gradient-cfag text-white rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                  <svg v-if="guardando" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ guardando ? 'Guardando...' : (modoEdicion ? 'Actualizar' : 'Crear') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal Ver Detalle -->
      <div v-if="mostrarModalDetalle" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-screen overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">Detalle de Factura</h2>
              <button @click="mostrarModalDetalle = false" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div v-if="facturaDetalle" class="space-y-6">
              <!-- Información general -->
              <div class="bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <span class="text-sm font-bold text-gray-700">Folio:</span>
                    <p class="text-lg font-semibold text-gray-900">{{ facturaDetalle.folio }}</p>
                  </div>
                  <div>
                    <span class="text-sm font-bold text-gray-700">Proveedor:</span>
                    <p class="text-lg text-gray-900">{{ facturaDetalle.proveedor?.nombre }}</p>
                    <p class="text-sm text-gray-500">{{ facturaDetalle.proveedor?.nit }}</p>
                  </div>
                  <div>
                    <span class="text-sm font-bold text-gray-700">Fecha:</span>
                    <p class="text-lg text-gray-900">{{ formatDate(facturaDetalle.fecha) }}</p>
                  </div>
                  <div>
                    <span class="text-sm font-bold text-gray-700">Tipo:</span>
                    <span :class="['inline-flex px-2 py-1 text-xs font-semibold rounded-full', getTipoClasses(facturaDetalle.tipo)]">
                      {{ getTipoLabel(facturaDetalle.tipo) }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Detalles -->
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4">Items de la Factura</h3>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Renglón</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P. Unitario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="detalle in facturaDetalle.detalles" :key="detalle.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          {{ detalle.renglon?.codigo }} - {{ detalle.renglon?.nombre }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ detalle.item }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ detalle.cantidad }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatMoney(detalle.precio_unitario) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ formatMoney(detalle.subtotal) }}</td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                      <tr>
                        <td colspan="4" class="px-6 py-4 text-right text-sm font-bold text-gray-900">Total:</td>
                        <td class="px-6 py-4 text-sm font-bold text-primary-600">{{ formatMoney(facturaDetalle.total) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <!-- Documento -->
              <div v-if="facturaDetalle.documento">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Documento Adjunto</h3>
                <div class="bg-gray-50 p-4 rounded-lg flex items-center justify-between">
                  <div class="flex items-center">
                    <svg class="h-8 w-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                      <p class="text-sm font-semibold text-gray-900">{{ facturaDetalle.documento.nombre_archivo }}</p>
                      <p class="text-xs text-gray-500">{{ formatFileSize(facturaDetalle.documento.tamanio) }}</p>
                    </div>
                  </div>
                  <div class="flex gap-2">
                    <button @click="descargarDocumento(facturaDetalle.id)"
                      class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-all duration-200">
                      Descargar
                    </button>
                    <button @click="confirmarEliminarDocumento(facturaDetalle)"
                      class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-all duration-200">
                      Eliminar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import facturaService from '@/services/facturaService'

// Estado reactivo
const loading = ref(false)
const guardando = ref(false)
const facturas = ref([])
const proveedores = ref([])
const renglones = ref([])
const paginacion = ref({})

// Modales
const mostrarModal = ref(false)
const mostrarModalDetalle = ref(false)
const modalReportesAbierto = ref(false)
const modoEdicion = ref(false)
const facturaDetalle = ref(null)

// Filtros
const filtros = ref({
  proveedor_id: '',
  tipo: '',
  fecha_desde: '',
  fecha_hasta: '',
  folio: '',
  per_page: 25
})

// Filtros para reportes
const filtroReporte = ref({
  fechaInicio: '',
  fechaFin: '',
  tipoReporte: 'resumen'
})

// Formulario
const formulario = ref({
  proveedor_id: '',
  folio: '',
  fecha: new Date().toISOString().split('T')[0],
  tipo: '',
  documento: null,
  detalles: []
})

const fileInput = ref(null)
const erroresArchivo = ref([])

// Alertas
const alert = ref({
  show: false,
  type: 'success',
  message: ''
})

// Computadas
const facturasFiltradas = computed(() => {
  return facturas.value
})

const totalFactura = computed(() => {
  return formulario.value.detalles.reduce((total, detalle) => {
    return total + (detalle.subtotal || 0)
  }, 0)
})

// Estado para reportes
const facturasReporte = ref([])
const loadingReporte = ref(false)

// Computadas para reportes
const facturasParaReporte = computed(() => {
  return facturasReporte.value
})

const totalFacturasFiltradas = computed(() => {
  return facturasParaReporte.value.reduce((total, factura) => {
    return total + parseFloat(factura.total || 0)
  }, 0)
})

// Funciones de carga
const cargarFacturas = async () => {
  try {
    loading.value = true
    const response = await facturaService.getFacturas(filtros.value)
    
    if (response.success) {
      facturas.value = response.data.data || response.data
      paginacion.value = response.data
    }
  } catch (error) {
    mostrarAlerta('error', 'Error al cargar facturas')
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

const cargarProveedores = async () => {
  try {
    const response = await facturaService.getProveedores()
    if (response.success) {
      proveedores.value = response.data
    }
  } catch (error) {
    console.error('Error al cargar proveedores:', error)
  }
}

const cargarRenglones = async () => {
  try {
    const response = await facturaService.getRenglones()
    if (response.success) {
      renglones.value = response.data
    }
  } catch (error) {
    console.error('Error al cargar renglones:', error)
  }
}

// Funciones de filtros
const aplicarFiltros = () => {
  cargarFacturas()
}

const buscarPorFolio = () => {
  // Debounce para búsqueda
  clearTimeout(window.searchTimeout)
  window.searchTimeout = setTimeout(() => {
    cargarFacturas()
  }, 500)
}

const limpiarFiltros = () => {
  filtros.value = {
    proveedor_id: '',
    tipo: '',
    fecha_desde: '',
    fecha_hasta: '',
    folio: '',
    per_page: 25
  }
  cargarFacturas()
}

// Funciones de modal
const abrirModalCrear = () => {
  modoEdicion.value = false
  formulario.value = {
    proveedor_id: '',
    folio: '',
    fecha: new Date().toISOString().split('T')[0],
    tipo: '',
    documento: null,
    detalles: []
  }
  erroresArchivo.value = []
  mostrarModal.value = true
}

const cerrarModal = () => {
  mostrarModal.value = false
  formulario.value = {
    proveedor_id: '',
    folio: '',
    fecha: new Date().toISOString().split('T')[0],
    tipo: '',
    documento: null,
    detalles: []
  }
  erroresArchivo.value = []
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const editarFactura = async (factura) => {
  try {
    const response = await facturaService.getFactura(factura.id)
    if (response.success) {
      const facturaCompleta = response.data
      
      modoEdicion.value = true
      formulario.value = {
        id: facturaCompleta.id,
        proveedor_id: facturaCompleta.proveedor_id,
        folio: facturaCompleta.folio,
        fecha: facturaCompleta.fecha,
        tipo: facturaCompleta.tipo,
        documento: null,
        detalles: facturaCompleta.detalles.map(detalle => ({
          renglon_id: detalle.renglon_id,
          item: detalle.item,
          cantidad: detalle.cantidad,
          precio_unitario: parseFloat(detalle.precio_unitario),
          subtotal: parseFloat(detalle.subtotal)
        }))
      }
      mostrarModal.value = true
    }
  } catch (error) {
    mostrarAlerta('error', 'Error al cargar factura para edición')
    console.error('Error:', error)
  }
}

const verFactura = async (id) => {
  try {
    const response = await facturaService.getFactura(id)
    if (response.success) {
      facturaDetalle.value = response.data
      mostrarModalDetalle.value = true
    }
  } catch (error) {
    mostrarAlerta('error', 'Error al cargar detalle de factura')
    console.error('Error:', error)
  }
}

// Funciones de detalles
const agregarDetalle = () => {
  formulario.value.detalles.push({
    renglon_id: '',
    item: '',
    cantidad: 1,
    precio_unitario: 0,
    subtotal: 0
  })
}

const eliminarDetalle = (index) => {
  formulario.value.detalles.splice(index, 1)
}

const calcularSubtotal = (index) => {
  const detalle = formulario.value.detalles[index]
  detalle.subtotal = (detalle.cantidad || 0) * (detalle.precio_unitario || 0)
}

// Funciones de archivo
const onFileChange = (event) => {
  const file = event.target.files[0]
  erroresArchivo.value = []
  
  if (file) {
    const errores = facturaService.validarArchivoPDF(file)
    if (errores.length > 0) {
      erroresArchivo.value = errores
      event.target.value = ''
      formulario.value.documento = null
    } else {
      formulario.value.documento = file
    }
  } else {
    formulario.value.documento = null
  }
}

// Funciones CRUD
const guardarFactura = async () => {
  try {
    guardando.value = true
    
    let response
    if (modoEdicion.value) {
      response = await facturaService.actualizarFactura(formulario.value.id, formulario.value)
    } else {
      response = await facturaService.crearFactura(formulario.value)
    }
    
    if (response.success) {
      mostrarAlerta('success', response.message)
      cerrarModal()
      cargarFacturas()
    }
  } catch (error) {
    mostrarAlerta('error', `Error al ${modoEdicion.value ? 'actualizar' : 'crear'} factura`)
    console.error('Error:', error)
  } finally {
    guardando.value = false
  }
}

const eliminarFactura = async (id) => {
  if (!confirm('¿Estás seguro de que quieres eliminar esta factura?')) {
    return
  }
  
  try {
    const response = await facturaService.eliminarFactura(id)
    if (response.success) {
      mostrarAlerta('success', 'Factura eliminada exitosamente')
      cargarFacturas()
    }
  } catch (error) {
    mostrarAlerta('error', 'Error al eliminar factura')
    console.error('Error:', error)
  }
}

const descargarDocumento = async (id) => {
  try {
    await facturaService.descargarDocumento(id)
  } catch (error) {
    mostrarAlerta('error', 'Error al descargar documento')
    console.error('Error:', error)
  }
}

const confirmarEliminarDocumento = (factura) => {
  if (confirm(`¿Estás seguro de que deseas eliminar el documento PDF de la factura ${factura.folio}?`)) {
    eliminarDocumentoDeFatura(factura.id)
  }
}

const eliminarDocumentoDeFatura = async (facturaId) => {
  try {
    loading.value = true
    
    // Llamar al backend para eliminar solo el documento
    await facturaService.eliminarDocumento(facturaId)
    
    mostrarAlerta('success', 'Documento eliminado exitosamente')
    
    // Cerrar el modal de detalles y recargar la lista
    cerrarModal()
    await cargarFacturas()
    
  } catch (error) {
    console.error('Error al eliminar documento:', error)
    mostrarAlerta('error', 'Error al eliminar el documento')
  } finally {
    loading.value = false
  }
}

// Funciones de paginación
const cargarPagina = (url) => {
  if (!url) return
  
  const urlObj = new URL(url)
  const page = urlObj.searchParams.get('page')
  filtros.value.page = page
  cargarFacturas()
}

// Funciones de utilidad
const mostrarAlerta = (type, message) => {
  alert.value = { show: true, type, message }
  setTimeout(() => {
    alert.value.show = false
  }, 5000)
}

const formatMoney = (amount) => {
  return facturaService.formatMoney(amount)
}

const formatDate = (date) => {
  return facturaService.formatDate(date)
}

const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getTipoLabel = (tipo) => {
  const tipos = {
    inventario: 'Inventario',
    bodega: 'Bodega',
    despensa: 'Despensa'
  }
  return tipos[tipo] || tipo
}

const getTipoClasses = (tipo) => {
  const clases = {
    inventario: 'bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-800 border-blue-200',
    bodega: 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border-green-200',
    despensa: 'bg-gradient-to-r from-purple-100 to-violet-100 text-purple-800 border-purple-200'
  }
  return clases[tipo] || 'bg-gradient-to-r from-gray-100 to-slate-100 text-gray-800 border-gray-200'
}

// Funciones del modal de reportes
const abrirModalReportes = async () => {
  // Configurar fechas por defecto (desde el 1 de enero del año actual hasta hoy)
  const hoy = new Date()
  const primeroEnero = new Date(hoy.getFullYear(), 0, 1) // 1 de enero del año actual
  
  filtroReporte.value.fechaInicio = primeroEnero.toISOString().split('T')[0]
  filtroReporte.value.fechaFin = hoy.toISOString().split('T')[0]
  filtroReporte.value.tipoReporte = 'resumen'
  
  modalReportesAbierto.value = true
  
  // Cargar datos iniciales
  await cargarFacturasParaReporte()
}

const cerrarModalReportes = () => {
  modalReportesAbierto.value = false
  filtroReporte.value = {
    fechaInicio: '',
    fechaFin: '',
    tipoReporte: 'resumen'
  }
  facturasReporte.value = []
}

const cargarFacturasParaReporte = async () => {
  try {
    loadingReporte.value = true
    
    const filtros = {
      fecha_inicio: filtroReporte.value.fechaInicio,
      fecha_fin: filtroReporte.value.fechaFin
    }
    
    const response = await facturaService.getFacturasParaReporte(filtros)
    if (response.success) {
      facturasReporte.value = response.data
    }
  } catch (error) {
    console.error('Error al cargar facturas para reporte:', error)
    mostrarAlerta('error', 'Error al cargar las facturas para el reporte')
  } finally {
    loadingReporte.value = false
  }
}

const generarReportePDF = async () => {
  try {
    loading.value = true
    
    // Importar jsPDF dinámicamente
    const { jsPDF } = await import('jspdf')
    await import('jspdf-autotable')
    
    const doc = new jsPDF()
    
    // Configurar título
    doc.setFontSize(18)
    doc.text('Reporte de Facturas', 20, 20)
    
    // Información del reporte
    doc.setFontSize(12)
    const fechaInicio = filtroReporte.value.fechaInicio || 'No especificada'
    const fechaFin = filtroReporte.value.fechaFin || 'No especificada'
    doc.text(`Período: ${fechaInicio} - ${fechaFin}`, 20, 35)
    doc.text(`Tipo: ${filtroReporte.value.tipoReporte === 'resumen' ? 'Resumen' : 'Detallado'}`, 20, 45)
    doc.text(`Total de facturas: ${facturasParaReporte.value.length}`, 20, 55)
    doc.text(`Total general: Q${totalFacturasFiltradas.value.toFixed(2)}`, 20, 65)
    
    if (filtroReporte.value.tipoReporte === 'resumen') {
      // Reporte resumen - solo encabezados
      const columns = ['Folio', 'Proveedor', 'Fecha', 'Tipo', 'Total']
      const rows = facturasParaReporte.value.map(factura => [
        factura.folio,
        factura.proveedor?.nombre || 'N/A',
        formatDate(factura.fecha),
        getTipoLabel(factura.tipo),
        `Q${parseFloat(factura.total || 0).toFixed(2)}`
      ])
      
      doc.autoTable({
        head: [columns],
        body: rows,
        startY: 75,
        styles: { fontSize: 10 },
        headStyles: { fillColor: [59, 130, 246] }
      })
    } else {
      // Reporte detallado - con detalles de cada factura
      let currentY = 75
      
      for (const factura of facturasParaReporte.value) {
        // Título de la factura
        doc.setFontSize(14)
        doc.text(`Factura ${factura.folio}`, 20, currentY)
        currentY += 10
        
        // Información del encabezado
        doc.setFontSize(10)
        doc.text(`Proveedor: ${factura.proveedor?.nombre || 'N/A'}`, 20, currentY)
        doc.text(`Fecha: ${formatDate(factura.fecha)}`, 120, currentY)
        currentY += 10
        doc.text(`Tipo: ${getTipoLabel(factura.tipo)}`, 20, currentY)
        doc.text(`Total: Q${parseFloat(factura.total || 0).toFixed(2)}`, 120, currentY)
        currentY += 15
        
        // Tabla de detalles si existen
        if (factura.detalles && factura.detalles.length > 0) {
          const detailColumns = ['Renglón', 'Item', 'Cantidad', 'P. Unit.', 'Subtotal']
          const detailRows = factura.detalles.map(detalle => [
            detalle.renglon?.nombre || 'N/A',
            detalle.item,
            detalle.cantidad.toString(),
            `Q${parseFloat(detalle.precio_unitario || 0).toFixed(2)}`,
            `Q${parseFloat(detalle.subtotal || 0).toFixed(2)}`
          ])
          
          doc.autoTable({
            head: [detailColumns],
            body: detailRows,
            startY: currentY,
            styles: { fontSize: 8 },
            headStyles: { fillColor: [34, 197, 94] },
            margin: { left: 20, right: 20 }
          })
          
          currentY = doc.lastAutoTable.finalY + 20
        } else {
          currentY += 10
        }
        
        // Nueva página si es necesario
        if (currentY > 250) {
          doc.addPage()
          currentY = 20
        }
      }
    }
    
    // Guardar el PDF
    const fileName = `reporte_facturas_${new Date().toISOString().split('T')[0]}.pdf`
    doc.save(fileName)
    
    mostrarAlerta('success', 'Reporte PDF generado exitosamente')
    
  } catch (error) {
    console.error('Error generando PDF:', error)
    mostrarAlerta('error', 'Error al generar el reporte PDF')
  } finally {
    loading.value = false
  }
}

const generarReporteExcel = async () => {
  try {
    loading.value = true
    
    // Importar XLSX dinámicamente
    const XLSX = await import('xlsx')
    
    const workbook = XLSX.utils.book_new()
    
    if (filtroReporte.value.tipoReporte === 'resumen') {
      // Hoja de resumen
      const resumenData = [
        ['Reporte de Facturas - Resumen'],
        [`Período: ${filtroReporte.value.fechaInicio || 'No especificada'} - ${filtroReporte.value.fechaFin || 'No especificada'}`],
        [`Total de facturas: ${facturasParaReporte.value.length}`],
        [`Total general: Q${totalFacturasFiltradas.value.toFixed(2)}`],
        [], // Fila vacía
        ['Folio', 'Proveedor', 'Fecha', 'Tipo', 'Total'],
        ...facturasParaReporte.value.map(factura => [
          factura.folio,
          factura.proveedor?.nombre || 'N/A',
          formatDate(factura.fecha),
          getTipoLabel(factura.tipo),
          parseFloat(factura.total || 0)
        ])
      ]
      
      const worksheet = XLSX.utils.aoa_to_sheet(resumenData)
      XLSX.utils.book_append_sheet(workbook, worksheet, 'Resumen')
      
    } else {
      // Hoja de encabezados
      const encabezadosData = [
        ['Reporte de Facturas - Encabezados'],
        [`Período: ${filtroReporte.value.fechaInicio || 'No especificada'} - ${filtroReporte.value.fechaFin || 'No especificada'}`],
        [], // Fila vacía
        ['Folio', 'Proveedor', 'Fecha', 'Tipo', 'Total'],
        ...facturasParaReporte.value.map(factura => [
          factura.folio,
          factura.proveedor?.nombre || 'N/A',
          formatDate(factura.fecha),
          getTipoLabel(factura.tipo),
          parseFloat(factura.total || 0)
        ])
      ]
      
      const worksheetEncabezados = XLSX.utils.aoa_to_sheet(encabezadosData)
      XLSX.utils.book_append_sheet(workbook, worksheetEncabezados, 'Encabezados')
      
      // Hoja de detalles
      const detallesData = [
        ['Reporte de Facturas - Detalles'],
        [],
        ['Folio Factura', 'Renglón', 'Item', 'Cantidad', 'Precio Unitario', 'Subtotal']
      ]
      
      facturasParaReporte.value.forEach(factura => {
        if (factura.detalles && factura.detalles.length > 0) {
          factura.detalles.forEach(detalle => {
            detallesData.push([
              factura.folio,
              detalle.renglon?.nombre || 'N/A',
              detalle.item,
              detalle.cantidad,
              parseFloat(detalle.precio_unitario || 0),
              parseFloat(detalle.subtotal || 0)
            ])
          })
        }
      })
      
      const worksheetDetalles = XLSX.utils.aoa_to_sheet(detallesData)
      XLSX.utils.book_append_sheet(workbook, worksheetDetalles, 'Detalles')
    }
    
    // Guardar el archivo Excel
    const fileName = `reporte_facturas_${new Date().toISOString().split('T')[0]}.xlsx`
    XLSX.writeFile(workbook, fileName)
    
    mostrarAlerta('success', 'Reporte Excel generado exitosamente')
    
  } catch (error) {
    console.error('Error generando Excel:', error)
    mostrarAlerta('error', 'Error al generar el reporte Excel')
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  cargarFacturas()
  cargarProveedores()
  cargarRenglones()
})
</script>