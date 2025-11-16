import axios from 'axios'

const API_BASE_URL = 'http://localhost:8000/api'

// Configuración de axios
const apiClient = axios.create({
    baseURL: API_BASE_URL,
    withCredentials: true,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
})

const actaBajaCuantiaService = {
    
    /**
     * Obtener todas las actas de baja cuantía
     */
    async getAll() {
        try {
            const response = await apiClient.get('/actas-baja-cuantia')
            return response.data
        } catch (error) {
            console.error('Error al obtener actas:', error)
            throw error
        }
    },

    /**
     * Obtener una acta específica por ID
     */
    async getById(id) {
        try {
            const response = await apiClient.get(`/actas-baja-cuantia/${id}`)
            return response.data
        } catch (error) {
            console.error('Error al obtener acta:', error)
            throw error
        }
    },

    /**
     * Crear una nueva acta de baja cuantía
     */
    async create(actaData) {
        try {
            const formData = new FormData()
            
            // Agregar campos del acta
            Object.keys(actaData).forEach(key => {
                if (key !== 'documento' && actaData[key] !== null && actaData[key] !== undefined) {
                    formData.append(key, actaData[key])
                }
            })

            // Agregar documento si existe
            if (actaData.documento) {
                formData.append('documento', actaData.documento)
            }

            const response = await apiClient.post('/actas-baja-cuantia', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error al crear acta:', error)
            throw error
        }
    },

    /**
     * Actualizar una acta existente
     */
    async update(id, actaData) {
        try {
            const formData = new FormData()
            
            // Agregar campos del acta
            Object.keys(actaData).forEach(key => {
                if (key !== 'documento' && actaData[key] !== null && actaData[key] !== undefined) {
                    formData.append(key, actaData[key])
                }
            })

            // Agregar documento si existe
            if (actaData.documento) {
                formData.append('documento', actaData.documento)
            }

            // Usar POST con _method para simular PUT (Laravel requirement para FormData)
            formData.append('_method', 'PUT')

            const response = await apiClient.post(`/actas-baja-cuantia/${id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error al actualizar acta:', error)
            throw error
        }
    },

    /**
     * Eliminar una acta
     */
    async delete(id) {
        try {
            const response = await apiClient.delete(`/actas-baja-cuantia/${id}`)
            return response.data
        } catch (error) {
            console.error('Error al eliminar acta:', error)
            throw error
        }
    },

    /**
     * Subir documento a un acta
     */
    async uploadDocument(id, documento) {
        try {
            const formData = new FormData()
            formData.append('documento', documento)

            const response = await apiClient.post(`/actas-baja-cuantia/${id}/documento`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            return response.data
        } catch (error) {
            console.error('Error al subir documento:', error)
            throw error
        }
    },

    /**
     * Descargar documento
     */
    async downloadDocument(documentoId) {
        try {
            const response = await apiClient.get(`/actas-baja-cuantia/documento/${documentoId}`, {
                responseType: 'blob'
            })
            return response
        } catch (error) {
            console.error('Error al descargar documento:', error)
            throw error
        }
    },

    /**
     * Eliminar documento
     */
    async deleteDocument(documentoId) {
        try {
            const response = await apiClient.delete(`/actas-baja-cuantia/documento/${documentoId}`)
            return response.data
        } catch (error) {
            console.error('Error al eliminar documento:', error)
            throw error
        }
    },

    /**
     * Obtener lista de proveedores
     */
    async getProveedores() {
        try {
            const response = await apiClient.get('/actas-baja-cuantia/proveedores')
            return response.data
        } catch (error) {
            console.error('Error al obtener proveedores:', error)
            throw error
        }
    },

    /**
     * Formatear fecha para mostrar
     */
    formatDate(date) {
        if (!date) return '-'
        return new Date(date).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        })
    },

    /**
     * Formatear moneda
     */
    formatCurrency(amount) {
        if (!amount) return 'Q0.00'
        return new Intl.NumberFormat('es-GT', {
            style: 'currency',
            currency: 'GTQ',
            minimumFractionDigits: 2
        }).format(amount)
    },

    /**
     * Validar archivo PDF
     */
    validatePdfFile(file) {
        if (!file) return { valid: true }

        const maxSize = 10 * 1024 * 1024 // 10MB
        const allowedTypes = ['application/pdf']

        if (!allowedTypes.includes(file.type)) {
            return {
                valid: false,
                message: 'Solo se permiten archivos PDF'
            }
        }

        if (file.size > maxSize) {
            return {
                valid: false,
                message: 'El archivo no debe superar los 10MB'
            }
        }

        return { valid: true }
    }
}

export default actaBajaCuantiaService