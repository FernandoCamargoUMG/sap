<template>
  <div class="space-y-6">
    <!-- Sección de subida de documento -->
    <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-bold text-gray-900">{{ title }}</h4>
        <div class="text-sm text-gray-500">
          PDF • Máximo 10MB
        </div>
      </div>

      <!-- Zona de arrastrar y soltar -->
      <div
        @drop="handleDrop"
        @dragover.prevent
        @dragenter.prevent
        :class="[
          'border-2 border-dashed rounded-xl p-8 text-center transition-all duration-200',
          isDragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-gray-400'
        ]"
        @dragenter="isDragging = true"
        @dragleave="isDragging = false"
      >
        <input
          ref="fileInput"
          type="file"
          accept=".pdf"
          multiple
          @change="handleFileSelect"
          class="hidden"
        />

        <div v-if="selectedFiles.length === 0">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Subir documentos PDF</h3>
          <p class="text-gray-600 mb-4">Arrastra y suelta tus archivos aquí o</p>
          <button
            type="button"
            @click="$refs.fileInput.click()"
            class="bg-primary-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-primary-700 transition-colors"
          >
            Seleccionar archivos
          </button>
          <p class="text-xs text-gray-500 mt-2">Solo archivos PDF, máximo 10MB cada uno</p>
        </div>

        <!-- Vista previa de los archivos seleccionados -->
        <div v-else class="space-y-4">
          <div v-for="(file, index) in selectedFiles" :key="index" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center space-x-3">
              <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
              </svg>
              <div class="text-left">
                <p class="font-semibold text-gray-900">{{ file.name }}</p>
                <p class="text-sm text-gray-500">{{ formatFileSize(file.size) }}</p>
              </div>
            </div>
            <button
              @click="removeFile(index)"
              class="p-1 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
              title="Eliminar archivo"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="flex justify-between mt-4">
            <button
              type="button"
              @click="$refs.fileInput.click()"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Agregar más archivos
            </button>
            <button
              type="button"
              @click="clearFile"
              class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors"
            >
              Limpiar todo
            </button>
          </div>
        </div>
      </div>

      <!-- Campo de descripción -->
      <div class="mt-4">
        <label class="block text-sm font-bold text-gray-700 mb-2">Descripción (opcional)</label>
        <textarea
          v-model="descripcion"
          rows="3"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
          placeholder="Descripción del documento..."
        ></textarea>
      </div>

      <!-- Botón para subir archivos -->
      <div v-if="selectedFiles.length > 0 && documentableId" class="mt-4">
        <button
          @click="uploadFile"
          :disabled="loading"
          class="w-full bg-primary-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center space-x-2"
        >
          <svg v-if="loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ loading ? 'Subiendo...' : `Subir ${selectedFiles.length} documento${selectedFiles.length > 1 ? 's' : ''}` }}</span>
        </button>
      </div>

      <!-- Errores de validación -->
      <div v-if="validationErrors.length > 0" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-center mb-2">
          <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-semibold text-red-800">Errores de validación:</span>
        </div>
        <ul class="text-sm text-red-700 list-disc list-inside">
          <li v-for="error in validationErrors" :key="error">{{ error }}</li>
        </ul>
      </div>
    </div>

    <!-- Lista de documentos existentes -->
    <div v-if="documentos.length > 0" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-bold text-gray-900">Documentos adjuntos</h4>
      </div>
      <div class="divide-y divide-gray-200">
        <div
          v-for="documento in documentos"
          :key="documento.id"
          class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-center space-x-3">
            <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
            </svg>
            <div>
              <p class="font-semibold text-gray-900">{{ documento.nombre_archivo }}</p>
              <p class="text-sm text-gray-500">
                {{ formatFileSize(documento.tamanio) }} • 
                Subido {{ formatDate(documento.created_at) }}
                <span v-if="documento.usuario">por {{ documento.usuario.nombre }}</span>
              </p>
              <p v-if="documento.descripcion" class="text-sm text-gray-600 mt-1">{{ documento.descripcion }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <button
              @click="downloadDocument(documento)"
              class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
              title="Descargar"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m5-2v8a2 2 0 01-2 2H5a2 2 0 01-2-2v-8a2 2 0 012-2h2.5" />
              </svg>
            </button>
            <button
              @click="deleteDocument(documento)"
              class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
              title="Eliminar"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import documentoService from '@/services/documentoService'

export default {
  name: 'DocumentUploader',
  props: {
    title: {
      type: String,
      default: 'Documentos adjuntos'
    },
    documentableType: {
      type: String,
      required: true
    },
    documentableId: {
      type: [String, Number],
      default: null
    },
    usuarioId: {
      type: [String, Number],
      required: true
    }
  },
  emits: ['uploaded', 'deleted', 'error'],
  data() {
    return {
      selectedFiles: [], // Cambiado para múltiples archivos
      descripcion: '',
      isDragging: false,
      validationErrors: [],
      documentos: [],
      loading: false
    }
  },
  async mounted() {
    if (this.documentableId) {
      await this.loadDocuments()
    }
  },
  watch: {
    documentableId: {
      handler(newId) {
        if (newId) {
          this.loadDocuments()
        }
      },
      immediate: false
    }
  },
  methods: {
    handleDrop(event) {
      event.preventDefault()
      this.isDragging = false
      
      const files = event.dataTransfer.files
      if (files.length > 0) {
        this.handleFile(files[0])
      }
    },

    handleFileSelect(event) {
      const files = Array.from(event.target.files)
      if (files.length > 0) {
        this.handleFiles(files)
      }
    },

    handleFiles(files) {
      this.validationErrors = []
      const validFiles = []
      
      files.forEach(file => {
        const validation = documentoService.validatePDF(file)
        if (!validation.isValid) {
          this.validationErrors.push(...validation.errors.map(error => `${file.name}: ${error}`))
        } else {
          validFiles.push(file)
        }
      })

      if (validFiles.length > 0) {
        this.selectedFiles = [...this.selectedFiles, ...validFiles]
      }
    },

    handleFile(file) {
      this.handleFiles([file])
    },

    clearFile() {
      this.selectedFiles = []
      this.descripcion = ''
      this.validationErrors = []
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = ''
      }
    },

    removeFile(index) {
      this.selectedFiles.splice(index, 1)
    },

    async uploadFile() {
      if (this.selectedFiles.length === 0 || !this.documentableId) {
        return false
      }

      try {
        this.loading = true
        const uploadedFiles = []
        
        // Subir archivos uno por uno
        for (const file of this.selectedFiles) {
          console.log('Subiendo archivo:', {
            name: file.name,
            size: file.size,
            type: file.type,
            documentableType: this.documentableType,
            documentableId: this.documentableId
          })
          
          const formData = new FormData()
          formData.append('file', file) // Backend espera 'file'
          formData.append('nombre_documento', file.name) // Agregar nombre requerido
          formData.append('documentable_type', this.documentableType)
          formData.append('documentable_id', this.documentableId)
          if (this.descripcion) {
            formData.append('descripcion', this.descripcion)
          }

          console.log('FormData creado, enviando a API...')
          const response = await documentoService.upload(formData)
          
          if (response.data.success) {
            uploadedFiles.push(response.data.documento)
          }
        }
        
        if (uploadedFiles.length > 0) {
          this.$emit('uploaded', uploadedFiles)
          this.clearFile()
          await this.loadDocuments()
          return true
        }
        
        return false
      } catch (error) {
        console.error('Error completo al subir documento:', {
          error: error,
          response: error.response,
          data: error.response?.data,
          status: error.response?.status
        })
        this.$emit('error', error.response?.data?.message || 'Error al subir documento')
        return false
      } finally {
        this.loading = false
      }
    },

    async loadDocuments() {
      if (!this.documentableId) return

      try {
        const response = await documentoService.getByEntity(this.documentableType, this.documentableId)
        if (response.data.success) {
          this.documentos = response.data.data
        }
      } catch (error) {
        console.error('Error al cargar documentos:', error)
      }
    },

    async downloadDocument(documento) {
      try {
        const response = await documentoService.download(documento.id)
        
        // Crear URL para descarga
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', documento.nombre_archivo)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
      } catch (error) {
        console.error('Error al descargar documento:', error)
        this.$emit('error', 'Error al descargar documento')
      }
    },

    async deleteDocument(documento) {
      if (!confirm('¿Estás seguro de que deseas eliminar este documento?')) {
        return
      }

      try {
        const response = await documentoService.delete(documento.id)
        if (response.data.success) {
          this.$emit('deleted', documento)
          await this.loadDocuments()
        }
      } catch (error) {
        console.error('Error al eliminar documento:', error)
        this.$emit('error', 'Error al eliminar documento')
      }
    },

    formatFileSize(bytes) {
      return documentoService.formatFileSize(bytes)
    },

    formatDate(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('es-GT', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },

    // Método público para subir archivo (llamado desde el componente padre)
    async submit() {
      return await this.uploadFile()
    },

    // Método público para obtener el archivo seleccionado
    getSelectedFile() {
      return this.selectedFile
    },

    // Método público para obtener la descripción
    getDescription() {
      return this.descripcion
    },

    // Método público para verificar si hay archivo seleccionado
    hasSelectedFile() {
      return !!this.selectedFile
    }
  }
}
</script>