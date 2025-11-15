import apiClient from './api'

export default {
  /**
   * Obtener todos los documentos
   */
  getAll() {
    return apiClient.get('/documentos')
  },

  /**
   * Obtener un documento específico
   */
  getById(id) {
    return apiClient.get(`/documentos/${id}`)
  },

  /**
   * Subir nuevo documento
   */
  create(data) {
    const formData = new FormData()
    
    // Agregar archivos al FormData (el backend espera 'file')
    if (data.archivo) {
      formData.append('file', data.archivo)
    }
    
    // Agregar otros campos
    Object.keys(data).forEach(key => {
      if (key !== 'archivo') {
        formData.append(key, data[key])
      }
    })

    return apiClient.post('/documentos', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  /**
   * Subir documento usando método upload específico
   */
  upload(formData) {
    return apiClient.post('/documentos', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },

  /**
   * Actualizar documento
   */
  update(id, data) {
    return apiClient.put(`/documentos/${id}`, data)
  },

  /**
   * Eliminar documento
   */
  delete(id) {
    return apiClient.delete(`/documentos/${id}`)
  },

  /**
   * Descargar archivo
   */
  download(id) {
    return apiClient.get(`/documentos/${id}/download`, {
      responseType: 'blob'
    })
  },

  /**
   * Obtener documentos por entidad (relación polimórfica)
   */
  getByEntity(documentableType, documentableId) {
    // Codificar correctamente el tipo de entidad con doble encoding para barras invertidas
    const encodedType = encodeURIComponent(encodeURIComponent(documentableType))
    return apiClient.get(`/documentos/${encodedType}/${documentableId}`)
  },

  /**
   * Tipos de entidades soportadas (datos estáticos del frontend)
   * NOTA: Estos tipos corresponden a los modelos Laravel con relaciones polimórficas
   * implementadas en el backend según CORRECCION_DOCUMENTOS.md
   */
  getEntityTypes() {
    return {
      data: [
        { id: 'App\\Models\\FacturaCab', nombre: 'Facturas' },
        { id: 'App\\Models\\Cur', nombre: 'Compromisos (CUR)' },
        { id: 'App\\Models\\PresupuestoCab', nombre: 'Presupuestos' },
        { id: 'App\\Models\\MovimientoCab', nombre: 'Movimientos' },
        { id: 'App\\Models\\Intra', nombre: 'Transferencias (INTRAS)' },
        { id: 'App\\Models\\Proveedor', nombre: 'Proveedores' },
        { id: 'App\\Models\\Renglon', nombre: 'Renglones' }
      ]
    }
  },

  /**
   * Validar archivo PDF
   */
  validatePDF(file) {
    const errors = []
    
    // Validar tipo de archivo
    if (file.type !== 'application/pdf') {
      errors.push('El archivo debe ser un PDF')
    }
    
    // Validar tamaño (10MB máximo)
    const maxSize = 10 * 1024 * 1024 // 10MB en bytes
    if (file.size > maxSize) {
      errors.push('El archivo no puede ser mayor a 10MB')
    }
    
    return {
      isValid: errors.length === 0,
      errors
    }
  },

  /**
   * Formatear tamaño de archivo
   */
  formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes'
    
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
  },

  /**
   * Obtener URL de descarga
   */
  getDownloadUrl(documento) {
    return documento.url_descarga || `${apiClient.defaults.baseURL}/documentos/${documento.id}/download`
  }
}