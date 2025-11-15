<template>
  <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-200">
    <!-- Header con filtros -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h3 class="text-xl font-black text-gray-900">
          <span class="bg-gradient-cfag bg-clip-text text-transparent">Evolución Mensual</span>
        </h3>
        <p class="text-gray-600 text-sm">Análisis de presupuesto vs ejecución por mes</p>
      </div>
      
      <!-- Filtros -->
      <div class="flex space-x-3">
        <select 
          v-model="selectedYear" 
          @change="updateChart"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm"
        >
          <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
        </select>
        
        <select 
          v-model="chartType" 
          @change="updateChart"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 text-sm"
        >
          <option value="line">Líneas</option>
          <option value="bar">Barras</option>
          <option value="area">Área</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-4 border-primary-600"></div>
    </div>

    <!-- Gráfica -->
    <div v-else class="relative">
      <canvas ref="chartCanvas" class="w-full" style="max-height: 400px;"></canvas>
    </div>

    <!-- Leyenda personalizada -->
    <div class="flex justify-center space-x-6 mt-4">
      <div class="flex items-center">
        <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
        <span class="text-sm text-gray-600">Presupuestado</span>
      </div>
      <div class="flex items-center">
        <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
        <span class="text-sm text-gray-600">Ejecutado</span>
      </div>
      <div class="flex items-center">
        <div class="w-4 h-4 bg-orange-500 rounded mr-2"></div>
        <span class="text-sm text-gray-600">Pendiente</span>
      </div>
    </div>

    <!-- Estadísticas resumidas -->
    <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-200">
      <div class="text-center">
        <div class="text-2xl font-bold text-blue-600">Q{{ formatMoney(stats.totalPresupuestado) }}</div>
        <div class="text-sm text-gray-500">Total Presupuestado</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-green-600">Q{{ formatMoney(stats.totalEjecutado) }}</div>
        <div class="text-sm text-gray-500">Total Ejecutado</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-gray-600">{{ stats.promedioEjecucion }}%</div>
        <div class="text-sm text-gray-500">Promedio Ejecución</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
import presupuestoService from '@/services/presupuestoService'

// Registrar todos los componentes de Chart.js
Chart.register(...registerables)

// Props
const props = defineProps({
  initialYear: {
    type: Number,
    default: () => new Date().getFullYear()
  }
})

// Variables reactivas
const chartCanvas = ref(null)
const chart = ref(null)
const loading = ref(true)
const selectedYear = ref(props.initialYear)
const chartType = ref('line')
const chartData = ref([])
const availableYears = ref([])
const stats = ref({
  totalPresupuestado: 0,
  totalEjecutado: 0,
  promedioEjecucion: 0
})

// Datos de ejemplo para los meses
const monthNames = [
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
]

// Función para formatear dinero
const formatMoney = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toLocaleString('es-GT', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

// Cargar datos de la gráfica
const loadChartData = async () => {
  loading.value = true
  try {
    // Obtener datos mensuales del año seleccionado
    const response = await presupuestoService.getAll()
    const presupuestos = response.data.data || response.data || []
    
    // Filtrar por año y agrupar por mes
    const monthlyData = {}
    presupuestos.forEach(presupuesto => {
      if (presupuesto.anio === selectedYear.value) {
        const mes = presupuesto.mes
        if (!monthlyData[mes]) {
          monthlyData[mes] = {
            presupuestado: 0,
            ejecutado: 0,
            pendiente: 0
          }
        }
        monthlyData[mes].presupuestado += parseFloat(presupuesto.total_presupuestado || 0)
        monthlyData[mes].ejecutado += parseFloat(presupuesto.total_ejecutado || 0)
        monthlyData[mes].pendiente += parseFloat(presupuesto.total_pendiente || 0)
      }
    })
    
    // Preparar datos para la gráfica
    const presupuestadoData = []
    const ejecutadoData = []
    const pendienteData = []
    
    for (let i = 1; i <= 12; i++) {
      const data = monthlyData[i] || { presupuestado: 0, ejecutado: 0, pendiente: 0 }
      presupuestadoData.push(data.presupuestado)
      ejecutadoData.push(data.ejecutado)
      pendienteData.push(data.pendiente)
    }
    
    chartData.value = [
      {
        label: 'Presupuestado',
        data: presupuestadoData,
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4
      },
      {
        label: 'Ejecutado',
        data: ejecutadoData,
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        tension: 0.4
      },
      {
        label: 'Pendiente',
        data: pendienteData,
        borderColor: 'rgb(249, 115, 22)',
        backgroundColor: 'rgba(249, 115, 22, 0.1)',
        tension: 0.4
      }
    ]
    
    // Calcular estadísticas
    const totalPresupuestado = presupuestadoData.reduce((a, b) => a + b, 0)
    const totalEjecutado = ejecutadoData.reduce((a, b) => a + b, 0)
    const promedioEjecucion = totalPresupuestado > 0 ? Math.round((totalEjecutado / totalPresupuestado) * 100) : 0
    
    stats.value = {
      totalPresupuestado,
      totalEjecutado,
      promedioEjecucion
    }
    
  } catch (error) {
    console.error('Error al cargar datos de la gráfica:', error)
  } finally {
    loading.value = false
  }
}

// Cargar años disponibles
const loadAvailableYears = async () => {
  try {
    const response = await presupuestoService.getAll()
    const presupuestos = response.data.data || response.data || []
    
    const years = [...new Set(presupuestos.map(p => p.anio))].sort((a, b) => b - a)
    availableYears.value = years.length > 0 ? years : [new Date().getFullYear()]
    
    if (years.length > 0 && !years.includes(selectedYear.value)) {
      selectedYear.value = years[0]
    }
  } catch (error) {
    console.error('Error al cargar años disponibles:', error)
    availableYears.value = [new Date().getFullYear()]
  }
}

// Crear/actualizar gráfica
const createChart = () => {
  if (!chartCanvas.value) return
  
  // Destruir gráfica anterior si existe
  if (chart.value) {
    chart.value.destroy()
  }
  
  const ctx = chartCanvas.value.getContext('2d')
  
  chart.value = new Chart(ctx, {
    type: chartType.value === 'area' ? 'line' : chartType.value,
    data: {
      labels: monthNames,
      datasets: chartData.value.map(dataset => ({
        ...dataset,
        fill: chartType.value === 'area',
        borderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6
      }))
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false // Usamos leyenda personalizada
        },
        tooltip: {
          mode: 'index',
          intersect: false,
          callbacks: {
            label: function(context) {
              return `${context.dataset.label}: Q${formatMoney(context.parsed.y)}`
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          }
        },
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) {
              return 'Q' + formatMoney(value)
            }
          }
        }
      },
      interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
      }
    }
  })
}

// Actualizar gráfica
const updateChart = async () => {
  await loadChartData()
  await nextTick()
  createChart()
}

// Watchers
watch([selectedYear, chartType], () => {
  updateChart()
})

// Lifecycle
onMounted(async () => {
  await loadAvailableYears()
  await loadChartData()
  await nextTick()
  createChart()
})
</script>