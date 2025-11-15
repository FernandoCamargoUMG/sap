<template>
    <div class="space-y-6">
        <!-- Resumen General -->
        <div v-if="resumen" class="bg-white rounded-2xl shadow-xl p-6 border border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-gray-900">
                    <span class="bg-gradient-cfag bg-clip-text text-transparent">Resumen Presupuestario</span>
                </h2>
                <div class="text-sm text-gray-500">
                    {{ resumen.periodo }}
                </div>
            </div>

            <!-- Tarjetas de Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Presupuestado -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-semibold">Total Presupuestado</p>
                            <p class="text-2xl font-black">Q{{ formatMoney(resumen.total_presupuestado) }}</p>
                        </div>
                        <div class="bg-white/20 rounded-lg p-2">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Ejecutado -->
                <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-semibold">Total Ejecutado</p>
                            <p class="text-2xl font-black">Q{{ formatMoney(resumen.total_ejecutado) }}</p>
                            <p class="text-green-200 text-xs">{{ resumen.porcentaje_ejecutado }}% del total</p>
                        </div>
                        <div class="bg-white/20 rounded-lg p-2">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Pendiente -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-semibold">Total Pendiente</p>
                            <p class="text-2xl font-black">Q{{ formatMoney(resumen.total_pendiente) }}</p>
                            <p class="text-orange-200 text-xs">{{ (100 - resumen.porcentaje_ejecutado).toFixed(1) }}%
                                disponible</p>
                        </div>
                        <div class="bg-white/20 rounded-lg p-2">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Variación -->
                <div :class="[
                    'rounded-xl p-6 text-white',
                    resumen.total_variacion >= 0 ? 'bg-gradient-to-br from-red-500 to-red-700' : 'bg-gradient-to-br from-gray-500 to-gray-700'
                ]">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-100 text-sm font-semibold">Variación</p>
                            <p class="text-2xl font-black">
                                {{ resumen.total_variacion >= 0 ? '+' : '-' }}Q{{
                                    formatMoney(Math.abs(resumen.total_variacion)) }}
                            </p>
                            <p class="text-gray-200 text-xs">
                                {{ resumen.total_variacion >= 0 ? 'Sobre-ejecuzione' : 'Sub-ejecución' }}
                            </p>
                        </div>
                        <div class="bg-white/20 rounded-lg p-2">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de Progreso General -->
            <div class="bg-gray-100 rounded-xl p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-bold text-gray-700">Progreso de Ejecución</span>
                    <span class="text-sm font-bold text-gray-900">{{ resumen.porcentaje_ejecutado }}%</span>
                </div>
                <div class="w-full bg-gray-300 rounded-full h-4">
                    <div :class="[
                        'h-4 rounded-full transition-all duration-500',
                        resumen.porcentaje_ejecutado >= 80 ? 'bg-red-500' :
                            resumen.porcentaje_ejecutado >= 50 ? 'bg-yellow-500' : 'bg-green-500'
                    ]" :style="`width: ${Math.min(resumen.porcentaje_ejecutado, 100)}%`"></div>
                </div>
            </div>
        </div>

        <!-- Detalle por Renglones -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="bg-gradient-cfag px-6 py-4">
                <h3 class="text-xl font-black text-white">Detalle por Renglones</h3>
                <p class="text-gray-100 text-sm">Ejecución presupuestaria por clasificador</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Renglón</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Presupuestado</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Ejecutado</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Pendiente</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">%
                                Ejecución</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Progreso</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="renglon in renglones" :key="renglon.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 rounded-full mr-3"
                                        :class="getColorClass(renglon.porcentaje_ejecutado)"></div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ renglon.codigo }}</div>
                                        <div class="text-sm text-gray-500">{{ renglon.nombre }}</div>
                                        <div class="text-xs text-gray-400">{{ renglon.grupo }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-blue-600">Q{{
                                    formatMoney(renglon.monto_presupuestado) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-green-600">Q{{ formatMoney(renglon.monto_ejecutado)
                                    }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-orange-600">Q{{ formatMoney(renglon.monto_pendiente)
                                    }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="[
                                    'text-sm font-bold',
                                    renglon.porcentaje_ejecutado >= 80 ? 'text-red-600' :
                                        renglon.porcentaje_ejecutado >= 50 ? 'text-yellow-600' : 'text-green-600'
                                ]">
                                    {{ renglon.porcentaje_ejecutado }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="w-20 bg-gray-200 rounded-full h-3 mx-auto">
                                    <div :class="[
                                        'h-3 rounded-full transition-all duration-300',
                                        renglon.porcentaje_ejecutado >= 80 ? 'bg-red-500' :
                                            renglon.porcentaje_ejecutado >= 50 ? 'bg-yellow-500' : 'bg-green-500'
                                    ]" :style="`width: ${Math.min(renglon.porcentaje_ejecutado, 100)}%`"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Gráfica de Evolución Mensual -->
        <PresupuestoChart :initial-year="getCurrentYear()" />

        <!-- Acciones Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                <h4 class="text-lg font-bold text-gray-900 mb-4">Estadísticas</h4>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Renglones:</span>
                        <span class="font-bold">{{ resumen?.cantidad_renglones || 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Renglones Activos:</span>
                        <span class="font-bold text-green-600">{{renglones?.filter(r => r.porcentaje_ejecutado >
                            0).length || 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ejecución Promedio:</span>
                        <span class="font-bold text-blue-600">{{ resumen?.porcentaje_ejecutado || 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import PresupuestoChart from './PresupuestoChart.vue'

export default {
    name: 'PresupuestoDashboard',
    components: {
        PresupuestoChart
    },
    props: {
        resumen: {
            type: Object,
            default: null
        },
        renglones: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    emits: ['crear-movimiento', 'ver-movimientos'],
    methods: {
        formatMoney(amount) {
            if (!amount) return '0.00'
            return parseFloat(amount).toLocaleString('es-GT', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            })
        },
        getColorClass(porcentaje) {
            if (porcentaje >= 80) return 'bg-red-500'
            if (porcentaje >= 50) return 'bg-yellow-500'
            return 'bg-green-500'
        },
        getCurrentYear() {
            return new Date().getFullYear()
        }
    }
}
</script>