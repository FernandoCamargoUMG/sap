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
    
    // Agregar archivos al FormData
    if (data.archivo) {
      formData.append('archivo', data.archivo)
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
    return apiClient.get(`/documentos/${encodeURIComponent(documentableType)}/${documentableId}`)
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
  }
}