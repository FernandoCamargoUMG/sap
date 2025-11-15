<template>
    <AppLayout>
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 flex items-center">
                        <span class="bg-gradient-cfag bg-clip-text text-transparent">Reportes Presupuestarios</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Genera reportes detallados en PDF y Excel</p>
                </div>
                <div class="flex space-x-3">
                    <button @click="$router.push('/dashboard-presupuestario')"
                        class="bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:bg-gray-50 transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Dashboard
                    </button>
                </div>
            </div>

            <!-- Alertas -->
            <div v-if="alert.show" :class="[
                'mb-6 p-4 rounded-xl border-l-4 flex items-center justify-between',
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

            <!-- Panel de Filtros -->
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 border border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-black text-gray-900">
                        <span class="bg-gradient-cfag bg-clip-text text-transparent">Configuración del Reporte</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Filtro Año -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Año *</label>
                        <select v-model="filters.anio" @change="loadPreview"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>

                    <!-- Filtro Mes -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Mes *</label>
                        <select v-model="filters.mes" @change="loadPreview"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option v-for="(mes, index) in meses" :key="index + 1" :value="index + 1">{{ mes }}</option>
                        </select>
                    </div>

                    <!-- Tipo de Reporte -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Reporte</label>
                        <select v-model="filters.tipoReporte" @change="loadPreview"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="completo">Reporte Completo</option>
                            <option value="resumen">Solo Resumen</option>
                            <option value="movimientos">Solo Movimientos</option>
                        </select>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex flex-col space-y-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Generar</label>
                        <button @click="generatePDF" :disabled="loading"
                            class="flex-1 bg-red-600 text-white px-4 py-3 rounded-xl font-semibold hover:bg-red-700 transition-colors disabled:opacity-50 flex items-center justify-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            PDF
                        </button>
                        <button @click="generateExcel" :disabled="loading"
                            class="flex-1 bg-green-600 text-white px-4 py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors disabled:opacity-50 flex items-center justify-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707v11a2 2 0 01-2 2z" />
                            </svg>
                            Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vista Previa -->
            <div v-if="previewData" class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-cfag px-6 py-4">
                    <h3 class="text-xl font-black text-white">Vista Previa del Reporte</h3>
                    <p class="text-gray-100 text-sm">{{ getReportTitle() }}</p>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="flex justify-center items-center py-20">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-primary-600 mx-auto mb-4">
                        </div>
                        <p class="text-gray-600 font-semibold">Cargando datos del reporte...</p>
                    </div>
                </div>

                <!-- Contenido de la Vista Previa -->
                <div v-else class="p-6">
                    <!-- Resumen General -->
                    <div class="mb-8">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Resumen Ejecutivo</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <div class="text-blue-800 text-sm font-semibold">Total Presupuestado</div>
                                <div class="text-2xl font-black text-blue-900">Q{{
                                    formatMoney(previewData.resumen.total_presupuestado) }}</div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="text-green-800 text-sm font-semibold">Total Ejecutado</div>
                                <div class="text-2xl font-black text-green-900">Q{{
                                    formatMoney(previewData.resumen.total_ejecutado) }}</div>
                            </div>
                            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                                <div class="text-orange-800 text-sm font-semibold">Total Pendiente</div>
                                <div class="text-2xl font-black text-orange-900">Q{{
                                    formatMoney(previewData.resumen.total_pendiente) }}</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="text-gray-800 text-sm font-semibold">Variación</div>
                                <div class="text-2xl font-black"
                                    :class="previewData.resumen.variacion >= 0 ? 'text-red-900' : 'text-gray-900'">
                                    {{ previewData.resumen.variacion >= 0 ? '+' : '' }}Q{{
                                        formatMoney(previewData.resumen.variacion) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalle por Renglones -->
                    <div v-if="filters.tipoReporte === 'completo' || filters.tipoReporte === 'resumen'" class="mb-8">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Detalle por Renglones</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Renglón</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Presupuestado</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Ejecutado</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Pendiente</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            % Ejecución</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Variación</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="renglon in previewData.renglones" :key="renglon.id"
                                        class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900">{{ renglon.codigo }} - {{
                                                renglon.nombre }}</div>
                                            <div class="text-xs text-gray-500">{{ renglon.grupo }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-blue-600">Q{{
                                            formatMoney(renglon.presupuestado) }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-green-600">Q{{
                                            formatMoney(renglon.ejecutado) }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-orange-600">Q{{
                                            formatMoney(renglon.pendiente) }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-bold"
                                            :class="getPercentageColor(renglon.porcentaje_ejecucion)">
                                            {{ renglon.porcentaje_ejecucion }}%
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-bold"
                                            :class="renglon.variacion >= 0 ? 'text-red-600' : 'text-gray-600'">
                                            {{ renglon.variacion >= 0 ? '+' : '' }}Q{{ formatMoney(renglon.variacion) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Movimientos -->
                    <div v-if="filters.tipoReporte === 'completo' || filters.tipoReporte === 'movimientos'"
                        class="mb-8">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">Movimientos del Período</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Fecha</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Renglón</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Descripción</th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Monto</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Referencia</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="movimiento in previewData.movimientos" :key="movimiento.id"
                                        class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(movimiento.fecha) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ movimiento.renglon }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ movimiento.descripcion }}</td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-red-600">Q{{
                                            formatMoney(movimiento.monto) }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ movimiento.referencia || '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado vacío -->
            <div v-else class="text-center py-20">
                <div class="bg-white rounded-2xl shadow-xl p-12 border border-gray-200">
                    <svg class="mx-auto h-24 w-24 text-gray-400 mb-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707v11a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Selecciona un período</h3>
                    <p class="text-gray-600 mb-6">Elige el año y mes para generar el reporte presupuestario</p>
                </div>
            </div>
        </main>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import presupuestoService from '@/services/presupuestoService'
import { jsPDF } from 'jspdf'
import 'jspdf-autotable'
import * as XLSX from 'xlsx'
import { saveAs } from 'file-saver'

// Estado reactivo
const loading = ref(false)
const previewData = ref(null)
const availableYears = ref([])

const filters = ref({
    anio: new Date().getFullYear(),
    mes: new Date().getMonth() + 1,
    tipoReporte: 'completo'
})

const alert = ref({
    show: false,
    type: 'success',
    message: ''
})

const meses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
]

// Funciones helper
const formatMoney = (amount) => {
    if (!amount) return '0.00'
    return parseFloat(amount).toLocaleString('es-GT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })
}

const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('es-GT')
}

const getPercentageColor = (percentage) => {
    if (percentage >= 80) return 'text-red-600'
    if (percentage >= 50) return 'text-yellow-600'
    return 'text-green-600'
}

const getReportTitle = () => {
    return `Reporte Presupuestario - ${meses[filters.value.mes - 1]} ${filters.value.anio}`
}

const showAlert = (type, message) => {
    alert.value = { show: true, type, message }
    setTimeout(() => {
        alert.value.show = false
    }, 5000)
}

// Cargar años disponibles
const loadAvailableYears = async () => {
    try {
        const response = await presupuestoService.getAll()
        const presupuestos = response.data.data || response.data || []

        const years = [...new Set(presupuestos.map(p => p.anio))].sort((a, b) => b - a)
        availableYears.value = years.length > 0 ? years : [new Date().getFullYear()]

        if (years.length > 0 && !years.includes(filters.value.anio)) {
            filters.value.anio = years[0]
        }
    } catch (error) {
        console.error('Error al cargar años disponibles:', error)
        availableYears.value = [new Date().getFullYear()]
    }
}

// Cargar vista previa
const loadPreview = async () => {
    loading.value = true
    try {
        // Obtener presupuestos del período seleccionado
        const response = await presupuestoService.getAll()
        const presupuestos = response.data.data || response.data || []

        // Filtrar por año y mes
        const presupuestosFiltrados = presupuestos.filter(p =>
            p.anio === filters.value.anio && p.mes === filters.value.mes
        )

        if (presupuestosFiltrados.length === 0) {
            previewData.value = null
            showAlert('error', 'No se encontraron datos para el período seleccionado')
            return
        }

        // Procesar datos para el reporte
        const presupuesto = presupuestosFiltrados[0] // Tomamos el primer presupuesto

        // Obtener detalles completos
        const detalleResponse = await presupuestoService.getById(presupuesto.id)
        const detalleCompleto = detalleResponse.data.data || detalleResponse.data

        // Preparar datos del reporte
        const renglones = []
        const movimientos = []
        let totalPresupuestado = 0
        let totalEjecutado = 0

        if (detalleCompleto.detalles) {
            detalleCompleto.detalles.forEach(detalle => {
                const presupuestado = parseFloat(detalle.monto_asignado || 0)
                const ejecutado = parseFloat(detalle.monto_ejecutado || 0)
                const pendiente = presupuestado - ejecutado
                const porcentajeEjecucion = presupuestado > 0 ? Math.round((ejecutado / presupuestado) * 100) : 0
                const variacion = ejecutado - presupuestado

                totalPresupuestado += presupuestado
                totalEjecutado += ejecutado

                renglones.push({
                    id: detalle.id,
                    codigo: detalle.renglon?.codigo || 'N/A',
                    nombre: detalle.renglon?.nombre || 'Sin nombre',
                    grupo: detalle.renglon?.grupo || 'Sin grupo',
                    presupuestado,
                    ejecutado,
                    pendiente,
                    porcentajeEjecucion,
                    variacion
                })

                // Agregar movimientos de este detalle
                if (detalle.movimientos) {
                    detalle.movimientos.forEach(mov => {
                        movimientos.push({
                            id: mov.id,
                            fecha: mov.movimiento_cab?.fecha || mov.created_at,
                            renglon: `${detalle.renglon?.codigo || 'N/A'} - ${detalle.renglon?.nombre || 'Sin nombre'}`,
                            descripcion: mov.descripcion_detalle || mov.movimiento_cab?.descripcion || 'Sin descripción',
                            monto: parseFloat(mov.monto || 0),
                            referencia: mov.movimiento_cab?.numero_documento || ''
                        })
                    })
                }
            })
        }

        const totalPendiente = totalPresupuestado - totalEjecutado
        const variacion = totalEjecutado - totalPresupuestado

        previewData.value = {
            resumen: {
                total_presupuestado: totalPresupuestado,
                total_ejecutado: totalEjecutado,
                total_pendiente: totalPendiente,
                variacion
            },
            renglones: renglones.sort((a, b) => a.codigo.localeCompare(b.codigo)),
            movimientos: movimientos.sort((a, b) => new Date(b.fecha) - new Date(a.fecha))
        }

    } catch (error) {
        console.error('Error al cargar vista previa:', error)
        showAlert('error', 'Error al cargar los datos del reporte')
        previewData.value = null
    } finally {
        loading.value = false
    }
}

// Generar PDF
const generatePDF = async () => {
    if (!previewData.value) {
        showAlert('error', 'No hay datos para generar el reporte')
        return
    }

    loading.value = true

    try {
        const doc = new jsPDF()

        // Verificar que autoTable esté disponible
        if (typeof doc.autoTable !== 'function') {
            console.warn('autoTable no está disponible, usando fallback')
            generateSimplePDF()
            return
        }

        // Título
        doc.setFontSize(18)
        doc.text('Sistema de Administración Presupuestaria', 20, 20)
        doc.setFontSize(14)
        doc.text(getReportTitle(), 20, 30)
        doc.setFontSize(10)
        doc.text(`Generado el: ${new Date().toLocaleDateString('es-GT')}`, 20, 40)

        let yPosition = 55

        // Resumen Ejecutivo
        doc.setFontSize(12)
        doc.text('RESUMEN EJECUTIVO', 20, yPosition)
        yPosition += 10

        const resumenData = [
            ['Total Presupuestado', `Q${formatMoney(previewData.value.resumen.total_presupuestado)}`],
            ['Total Ejecutado', `Q${formatMoney(previewData.value.resumen.total_ejecutado)}`],
            ['Total Pendiente', `Q${formatMoney(previewData.value.resumen.total_pendiente)}`],
            ['Variación', `${previewData.value.resumen.variacion >= 0 ? '+' : ''}Q${formatMoney(previewData.value.resumen.variacion)}`]
        ]

        doc.autoTable({
            startY: yPosition,
            head: [['Concepto', 'Monto']],
            body: resumenData,
            theme: 'grid',
            headStyles: { fillColor: [59, 130, 246] }
        })

        yPosition = doc.lastAutoTable.finalY + 15

        // Detalle por Renglones
        if (filters.value.tipoReporte === 'completo' || filters.value.tipoReporte === 'resumen') {
            doc.text('DETALLE POR RENGLONES', 20, yPosition)
            yPosition += 5

            const renglonesData = previewData.value.renglones.map(r => [
                `${r.codigo} - ${r.nombre}`,
                `Q${formatMoney(r.presupuestado)}`,
                `Q${formatMoney(r.ejecutado)}`,
                `Q${formatMoney(r.pendiente)}`,
                `${r.porcentajeEjecucion}%`,
                `${r.variacion >= 0 ? '+' : ''}Q${formatMoney(r.variacion)}`
            ])

            doc.autoTable({
                startY: yPosition,
                head: [['Renglón', 'Presupuestado', 'Ejecutado', 'Pendiente', '% Ejec.', 'Variación']],
                body: renglonesData,
                theme: 'grid',
                headStyles: { fillColor: [59, 130, 246] },
                styles: { fontSize: 8 }
            })

            yPosition = doc.lastAutoTable.finalY + 15
        }

        // Movimientos
        if (filters.value.tipoReporte === 'completo' || filters.value.tipoReporte === 'movimientos') {
            // Nueva página si es necesario
            if (yPosition > 250) {
                doc.addPage()
                yPosition = 20
            }

            doc.text('MOVIMIENTOS DEL PERÍODO', 20, yPosition)
            yPosition += 5

            const movimientosData = previewData.value.movimientos.map(m => [
                formatDate(m.fecha),
                m.renglon,
                m.descripcion,
                `Q${formatMoney(m.monto)}`,
                m.referencia || '-'
            ])

            doc.autoTable({
                startY: yPosition,
                head: [['Fecha', 'Renglón', 'Descripción', 'Monto', 'Referencia']],
                body: movimientosData,
                theme: 'grid',
                headStyles: { fillColor: [59, 130, 246] },
                styles: { fontSize: 8 }
            })
        }

        // Guardar PDF
        const fileName = `Reporte_Presupuesto_${filters.value.anio}_${String(filters.value.mes).padStart(2, '0')}.pdf`
        doc.save(fileName)

        showAlert('success', 'Reporte PDF generado exitosamente')

    } catch (error) {
        console.error('Error al generar PDF:', error)
        showAlert('error', 'Error al generar el reporte PDF')
    } finally {
        loading.value = false
    }
}

// Generar PDF simple (fallback)
const generateSimplePDF = () => {
    try {
        const doc = new jsPDF()

        let yPos = 20

        // Título
        doc.setFontSize(18)
        doc.text('Sistema de Administración Presupuestaria', 20, yPos)
        yPos += 10

        doc.setFontSize(14)
        doc.text(getReportTitle(), 20, yPos)
        yPos += 10

        doc.setFontSize(10)
        doc.text(`Generado el: ${new Date().toLocaleDateString('es-GT')}`, 20, yPos)
        yPos += 20

        // Resumen Ejecutivo
        doc.setFontSize(12)
        doc.text('RESUMEN EJECUTIVO', 20, yPos)
        yPos += 15

        doc.setFontSize(10)
        doc.text(`Total Presupuestado: Q${formatMoney(previewData.value.resumen.total_presupuestado)}`, 20, yPos)
        yPos += 10
        doc.text(`Total Ejecutado: Q${formatMoney(previewData.value.resumen.total_ejecutado)}`, 20, yPos)
        yPos += 10
        doc.text(`Total Pendiente: Q${formatMoney(previewData.value.resumen.total_pendiente)}`, 20, yPos)
        yPos += 10
        doc.text(`Variación: ${previewData.value.resumen.variacion >= 0 ? '+' : ''}Q${formatMoney(previewData.value.resumen.variacion)}`, 20, yPos)
        yPos += 20

        // Detalle por Renglones
        if (filters.value.tipoReporte === 'completo' || filters.value.tipoReporte === 'resumen') {
            doc.setFontSize(12)
            doc.text('DETALLE POR RENGLONES', 20, yPos)
            yPos += 15

            doc.setFontSize(9)
            previewData.value.renglones.forEach(renglon => {
                if (yPos > 270) {
                    doc.addPage()
                    yPos = 20
                }

                doc.text(`${renglon.codigo} - ${renglon.nombre}`, 20, yPos)
                yPos += 7
                doc.text(`  Presupuestado: Q${formatMoney(renglon.presupuestado)} | Ejecutado: Q${formatMoney(renglon.ejecutado)} | Pendiente: Q${formatMoney(renglon.pendiente)}`, 25, yPos)
                yPos += 7
                doc.text(`  Ejecución: ${renglon.porcentajeEjecucion}% | Variación: ${renglon.variacion >= 0 ? '+' : ''}Q${formatMoney(renglon.variacion)}`, 25, yPos)
                yPos += 10
            })
            yPos += 10
        }

        // Movimientos
        if (filters.value.tipoReporte === 'completo' || filters.value.tipoReporte === 'movimientos') {
            if (yPos > 250) {
                doc.addPage()
                yPos = 20
            }

            doc.setFontSize(12)
            doc.text('MOVIMIENTOS DEL PERÍODO', 20, yPos)
            yPos += 15

            doc.setFontSize(9)
            previewData.value.movimientos.forEach(mov => {
                if (yPos > 270) {
                    doc.addPage()
                    yPos = 20
                }

                doc.text(`${formatDate(mov.fecha)} - ${mov.renglon}`, 20, yPos)
                yPos += 7
                doc.text(`  ${mov.descripcion} - Q${formatMoney(mov.monto)}`, 25, yPos)
                if (mov.referencia) {
                    yPos += 7
                    doc.text(`  Ref: ${mov.referencia}`, 25, yPos)
                }
                yPos += 10
            })
        }

        // Guardar PDF
        const fileName = `Reporte_Presupuesto_${filters.value.anio}_${String(filters.value.mes).padStart(2, '0')}.pdf`
        doc.save(fileName)

        showAlert('success', 'Reporte PDF generado exitosamente (versión simple)')

    } catch (error) {
        console.error('Error al generar PDF simple:', error)
        showAlert('error', 'Error al generar el reporte PDF simple')
    } finally {
        loading.value = false
    }
}

// Generar Excel
const generateExcel = async () => {
    if (!previewData.value) {
        showAlert('error', 'No hay datos para generar el reporte')
        return
    }

    loading.value = true
    try {
        const workbook = XLSX.utils.book_new()

        // Hoja de Resumen
        const resumenData = [
            ['REPORTE PRESUPUESTARIO'],
            [getReportTitle()],
            [`Generado el: ${new Date().toLocaleDateString('es-GT')}`],
            [],
            ['RESUMEN EJECUTIVO'],
            ['Concepto', 'Monto'],
            ['Total Presupuestado', previewData.value.resumen.total_presupuestado],
            ['Total Ejecutado', previewData.value.resumen.total_ejecutado],
            ['Total Pendiente', previewData.value.resumen.total_pendiente],
            ['Variación', previewData.value.resumen.variacion]
        ]

        const resumenSheet = XLSX.utils.aoa_to_sheet(resumenData)
        XLSX.utils.book_append_sheet(workbook, resumenSheet, 'Resumen')

        // Hoja de Renglones
        if (filters.value.tipoReporte === 'completo' || filters.value.tipoReporte === 'resumen') {
            const renglonesData = [
                ['Código', 'Nombre', 'Grupo', 'Presupuestado', 'Ejecutado', 'Pendiente', '% Ejecución', 'Variación']
            ]

            previewData.value.renglones.forEach(r => {
                renglonesData.push([
                    r.codigo,
                    r.nombre,
                    r.grupo,
                    r.presupuestado,
                    r.ejecutado,
                    r.pendiente,
                    r.porcentajeEjecucion,
                    r.variacion
                ])
            })

            const renglonesSheet = XLSX.utils.aoa_to_sheet(renglonesData)
            XLSX.utils.book_append_sheet(workbook, renglonesSheet, 'Renglones')
        }

        // Hoja de Movimientos
        if (filters.value.tipoReporte === 'completo' || filters.value.tipoReporte === 'movimientos') {
            const movimientosData = [
                ['Fecha', 'Renglón', 'Descripción', 'Monto', 'Referencia']
            ]

            previewData.value.movimientos.forEach(m => {
                movimientosData.push([
                    formatDate(m.fecha),
                    m.renglon,
                    m.descripcion,
                    m.monto,
                    m.referencia || ''
                ])
            })

            const movimientosSheet = XLSX.utils.aoa_to_sheet(movimientosData)
            XLSX.utils.book_append_sheet(workbook, movimientosSheet, 'Movimientos')
        }

        // Guardar Excel
        const fileName = `Reporte_Presupuesto_${filters.value.anio}_${String(filters.value.mes).padStart(2, '0')}.xlsx`
        const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' })
        const blob = new Blob([excelBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        saveAs(blob, fileName)

        showAlert('success', 'Reporte Excel generado exitosamente')

    } catch (error) {
        console.error('Error al generar Excel:', error)
        showAlert('error', 'Error al generar el reporte Excel')
    } finally {
        loading.value = false
    }
}

// Lifecycle
onMounted(async () => {
    await loadAvailableYears()
    await loadPreview()
})
</script>