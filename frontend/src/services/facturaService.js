import axios from 'axios'

const API_URL = 'http://localhost:8000/api'

const facturaService = {
  // Obtener todas las facturas con filtros
  async getFacturas(filtros = {}) {
    try {
      const params = new URLSearchParams()
      
      Object.keys(filtros).forEach(key => {
        if (filtros[key] !== '' && filtros[key] !== null && filtros[key] !== undefined) {
          params.append(key, filtros[key])
        }
      })
      
      const response = await axios.get(`${API_URL}/facturas?${params.toString()}`)
      return response.data
    } catch (error) {
      console.error('Error al obtener facturas:', error)
      throw error
    }
  },

  // Obtener una factura específica
  async getFactura(id) {
    try {
      const response = await axios.get(`${API_URL}/facturas/${id}`)
      return response.data
    } catch (error) {
      console.error('Error al obtener factura:', error)
      throw error
    }
  },

  // Crear nueva factura
  async crearFactura(facturaData) {
    try {
      const formData = new FormData()
      
      // Agregar datos básicos
      formData.append('proveedor_id', facturaData.proveedor_id)
      formData.append('folio', facturaData.folio)
      formData.append('fecha', facturaData.fecha)
      formData.append('tipo', facturaData.tipo)
      
      // Agregar detalles como JSON
      formData.append('detalles', JSON.stringify(facturaData.detalles))
      
      // Agregar archivo si existe
      if (facturaData.documento) {
        formData.append('documento', facturaData.documento)
      }
      
      const response = await axios.post(`${API_URL}/facturas`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      return response.data
    } catch (error) {
      console.error('Error al crear factura:', error)
      throw error
    }
  },

  // Actualizar factura
  async actualizarFactura(id, facturaData) {
    try {
      const formData = new FormData()
      
      // Agregar datos básicos
      formData.append('proveedor_id', facturaData.proveedor_id)
      formData.append('folio', facturaData.folio)
      formData.append('fecha', facturaData.fecha)
      formData.append('tipo', facturaData.tipo)
      formData.append('_method', 'PUT')
      
      // Agregar detalles como JSON
      formData.append('detalles', JSON.stringify(facturaData.detalles))
      
      // Agregar archivo si existe
      if (facturaData.documento) {
        formData.append('documento', facturaData.documento)
      }
      
      // Agregar parámetro para eliminar documento
      if (facturaData.eliminar_documento) {
        formData.append('eliminar_documento', 'true')
      }
      
      const response = await axios.post(`${API_URL}/facturas/${id}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      return response.data
    } catch (error) {
      console.error('Error al actualizar factura:', error)
      throw error
    }
  },

  // Eliminar factura
  async eliminarFactura(id) {
    try {
      const response = await axios.delete(`${API_URL}/facturas/${id}`)
      return response.data
    } catch (error) {
      console.error('Error al eliminar factura:', error)
      throw error
    }
  },

  // Obtener proveedores
  async getProveedores() {
    try {
      const response = await axios.get(`${API_URL}/facturas/proveedores`)
      return response.data
    } catch (error) {
      console.error('Error al obtener proveedores:', error)
      throw error
    }
  },

  // Obtener renglones
  async getRenglones() {
    try {
      const response = await axios.get(`${API_URL}/facturas/renglones`)
      return response.data
    } catch (error) {
      console.error('Error al obtener renglones:', error)
      throw error
    }
  },

  // Descargar documento de factura
  async descargarDocumento(id) {
    try {
      const response = await axios.get(`${API_URL}/facturas/${id}/documento`, {
        responseType: 'blob'
      })
      
      // Crear URL para descarga
      const url = window.URL.createObjectURL(new Blob([response.data]))
      const link = document.createElement('a')
      link.href = url
      
      // Obtener nombre del archivo del header
      const contentDisposition = response.headers['content-disposition']
      let filename = 'documento.pdf'
      if (contentDisposition) {
        const filenameMatch = contentDisposition.match(/filename="?([^"]+)"?/)
        if (filenameMatch) {
          filename = filenameMatch[1]
        }
      }
      
      link.setAttribute('download', filename)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
      
      return { success: true, message: 'Documento descargado exitosamente' }
    } catch (error) {
      console.error('Error al descargar documento:', error)
      throw error
    }
  },

  // Formatear moneda
  formatMoney(amount) {
    return new Intl.NumberFormat('es-GT', {
      style: 'currency',
      currency: 'GTQ',
      minimumFractionDigits: 2
    }).format(amount || 0)
  },

  // Formatear fecha
  formatDate(date) {
    if (!date) return ''
    
    // Asegurar que la fecha se interprete correctamente
    let dateObj
    if (typeof date === 'string') {
      // Si viene como string (ejemplo: "2025-11-15"), crear fecha sin conversión de zona horaria
      if (date.includes('T')) {
        // Si tiene tiempo, usar la fecha directamente
        dateObj = new Date(date)
      } else {
        // Si es solo fecha (YYYY-MM-DD), agregar tiempo local para evitar problemas de zona horaria
        dateObj = new Date(date + 'T00:00:00')
      }
    } else {
      dateObj = new Date(date)
    }
    
    return dateObj.toLocaleDateString('es-GT', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    })
  },

  // Eliminar documento de factura
  async eliminarDocumento(facturaId) {
    try {
      const response = await axios.delete(`${API_URL}/facturas/${facturaId}/documento`)
      return response.data
    } catch (error) {
      console.error('Error al eliminar documento:', error)
      throw error
    }
  },

  // Obtener facturas con detalles para reportes
  async getFacturasParaReporte(filtros = {}) {
    try {
      const params = new URLSearchParams()
      
      Object.keys(filtros).forEach(key => {
        if (filtros[key] !== '' && filtros[key] !== null && filtros[key] !== undefined) {
          params.append(key, filtros[key])
        }
      })
      
      const response = await axios.get(`${API_URL}/facturas/reportes?${params.toString()}`)
      return response.data
    } catch (error) {
      console.error('Error al obtener facturas para reporte:', error)
      throw error
    }
  },

  // Validar archivo PDF
  validarArchivoPDF(file) {
    const errors = []
    
    if (!file) return errors
    
    // Validar tipo
    if (file.type !== 'application/pdf') {
      errors.push('El archivo debe ser un PDF')
    }
    
    // Validar tamaño (10MB máximo)
    if (file.size > 10 * 1024 * 1024) {
      errors.push('El archivo no debe superar los 10MB')
    }
    
    return errors
  }
}

export default facturaService