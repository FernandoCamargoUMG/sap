<template>
    <AppLayout>
        <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 flex items-center">
                        <span class="bg-gradient-cfag bg-clip-text text-transparent">Usuarios</span>
                    </h1>
                    <p class="text-gray-600 mt-2">Gestiona los usuarios del sistema</p>
                </div>
                <button @click="openCreateModal"
                    class="bg-gradient-cfag text-white px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Usuario
                </button>
            </div>

            <!-- Alertas -->
            <div v-if="alert.show" :class="[
                'mb-6 p-4 rounded-xl border-l-4 flex items-center justify-between animate-pulse',
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
                <button @click="alert.show = false" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-primary-600"></div>
            </div>

            <!-- Tabla -->
            <div v-else class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-cfag">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Usuario</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Correo</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">
                                    Rol</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Estado</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Última Conexión</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-white uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="usuario in usuarios" :key="usuario.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div
                                                class="h-10 w-10 rounded-full bg-gradient-cfag flex items-center justify-center">
                                                <span class="text-white font-bold text-sm">{{
                                                    getInitials(usuario.nombre) }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ usuario.nombre }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600">{{ usuario.correo }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-bold',
                                        getRoleBadgeClass(usuario.rol?.nombre)
                                    ]">
                                        {{ usuario.rol?.nombre || 'Sin rol' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-xs font-bold',
                                        usuario.estado === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                    ]">
                                        {{ usuario.estado === 1 ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="text-sm text-gray-600">
                                        {{ usuario.updated_at ? formatDate(usuario.updated_at) : 'Nunca' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button @click="openEditModal(usuario)"
                                            class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors"
                                            title="Editar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button v-if="usuario.estado === 1" @click="confirmDeactivate(usuario)"
                                            class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                            title="Desactivar Usuario">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                            </svg>
                                        </button>
                                        <button v-else @click="confirmActivate(usuario)"
                                            class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                            title="Activar Usuario">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="usuarios.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="h-20 w-20 text-gray-300 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="text-xl font-semibold text-gray-600 mb-2">No hay usuarios</p>
                                        <p class="text-gray-500">Crea el primer usuario para comenzar</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Modal Crear/Editar -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full max-h-[90vh] overflow-y-auto shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-900">
                        {{ isEditing ? 'Editar Usuario' : 'Nuevo Usuario' }}
                    </h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nombre completo *</label>
                        <input v-model="form.nombre" type="text" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            placeholder="Ingresa el nombre completo">
                    </div>

                    <!-- Correo -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Correo electrónico *</label>
                        <input v-model="form.correo" type="email" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            placeholder="usuario@ejemplo.com">
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            {{ isEditing ? 'Nueva contraseña (opcional)' : 'Contraseña *' }}
                        </label>
                        <input v-model="form.contraseña" type="password" :required="!isEditing"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            :placeholder="isEditing ? 'Dejar vacío para mantener la actual' : 'Mínimo 6 caracteres'">
                    </div>

                    <!-- Rol -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Rol *</label>
                        <select v-model="form.rol_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                            <option value="">Selecciona un rol</option>
                            <option v-for="rol in roles" :key="rol.id" :value="rol.id">
                                {{ rol.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Estado
                    <div v-if="isEditing">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Estado</label>
                        <select v-model="form.estado"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                            <option :value="1">Activo</option>
                            <option :value="0">Inactivo</option>
                        </select>
                    </div>-->

                    <!-- Botones -->
                    <div class="flex justify-end space-x-3 pt-6">
                        <button type="button" @click="closeModal"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="px-6 py-3 bg-gradient-cfag text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 transition-all">
                            {{ submitting ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Confirmar Cambio de Estado -->
        <div v-if="showStatusModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
                <div class="text-center">
                    <div :class="[
                        'mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-6',
                        statusAction === 'deactivate' ? 'bg-red-100' : 'bg-green-100'
                    ]">
                        <svg v-if="statusAction === 'deactivate'" class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                        </svg>
                        <svg v-else class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-900 mb-3">
                        {{ statusAction === 'deactivate' ? 'Confirmar Desactivación' : 'Confirmar Activación' }}
                    </h3>
                    <p class="text-gray-600 mb-6">
                        ¿Estás seguro de que deseas {{ statusAction === 'deactivate' ? 'desactivar' : 'activar' }} 
                        el usuario <strong>{{ userToChangeStatus?.nombre }}</strong>?
                        <span v-if="statusAction === 'deactivate'" class="block mt-2 text-sm text-red-600">
                            El usuario no podrá acceder al sistema mientras esté inactivo.
                        </span>
                    </p>
                    <div class="flex justify-center space-x-3">
                        <button @click="showStatusModal = false"
                            class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button @click="changeUserStatus" :disabled="changingStatus"
                            :class="[
                                'px-6 py-3 rounded-xl font-semibold disabled:opacity-50 transition-colors',
                                statusAction === 'deactivate' 
                                    ? 'bg-red-600 text-white hover:bg-red-700' 
                                    : 'bg-green-600 text-white hover:bg-green-700'
                            ]">
                            {{ changingStatus ? 'Procesando...' : (statusAction === 'deactivate' ? 'Desactivar' : 'Activar') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/components/AppLayout.vue'
import usuarioService from '@/services/usuarioService'

// Estado principal
const loading = ref(true)
const usuarios = ref([])
const roles = ref([])

// Estado del modal
const showModal = ref(false)
const isEditing = ref(false)
const submitting = ref(false)

// Estado del modal de cambio de estado
const showStatusModal = ref(false)
const userToChangeStatus = ref(null)
const statusAction = ref('') // 'activate' o 'deactivate'
const changingStatus = ref(false)

// Formulario
const form = ref({
    id: null,
    nombre: '',
    correo: '',
    contraseña: '',
    rol_id: '',
    estado: 1
})

// Alertas
const alert = ref({
    show: false,
    type: 'success',
    message: ''
})

// Cargar datos iniciales
onMounted(async () => {
    await Promise.all([
        loadUsuarios(),
        loadRoles()
    ])
})

// Cargar usuarios
const loadUsuarios = async () => {
    try {
        loading.value = true
        const response = await usuarioService.getAll()
        console.log('Respuesta usuarios:', response.data)
        usuarios.value = response.data.data || response.data
    } catch (error) {
        console.error('Error al cargar usuarios:', error)
        showAlert('error', 'Error al cargar usuarios')
    } finally {
        loading.value = false
    }
}

// Cargar roles
const loadRoles = async () => {
    try {
        const response = await usuarioService.getRoles()
        roles.value = response.data.data || response.data || []
    } catch (error) {
        console.error('Error al cargar roles:', error)
        showAlert('error', 'Error al cargar roles')
    }
}

// Abrir modal para crear
const openCreateModal = async () => {
    isEditing.value = false
    form.value = {
        id: null,
        nombre: '',
        correo: '',
        contraseña: '',
        rol_id: '',
        estado: 1
    }
    // Asegurar que los roles estén cargados
    if (roles.value.length === 0) {
        await loadRoles()
    }
    showModal.value = true
}

// Abrir modal para editar
const openEditModal = async (usuario) => {
    isEditing.value = true
    form.value = {
        id: usuario.id,
        nombre: usuario.nombre,
        correo: usuario.correo,
        contraseña: '',
        rol_id: usuario.rol_id,
        estado: usuario.estado
    }
    // Asegurar que los roles estén cargados
    if (roles.value.length === 0) {
        await loadRoles()
    }
    showModal.value = true
}

// Cerrar modal
const closeModal = () => {
    showModal.value = false
    isEditing.value = false
    form.value = {
        id: null,
        nombre: '',
        correo: '',
        contraseña: '',
        rol_id: '',
        estado: 1
    }
}

// Enviar formulario
const submitForm = async () => {
    try {
        submitting.value = true

        const formData = { ...form.value }

        // Si estamos editando y no hay contraseña, la eliminamos del objeto
        if (isEditing.value && !formData.contraseña) {
            delete formData.contraseña
        }

        if (isEditing.value) {
            if (!form.value.id) {
                throw new Error('ID del usuario no encontrado')
            }
            await usuarioService.update(form.value.id, formData)
            showAlert('success', 'Usuario actualizado correctamente')
        } else {
            await usuarioService.create(formData)
            showAlert('success', 'Usuario creado correctamente')
        }

        closeModal()
        await loadUsuarios()
    } catch (error) {
        console.error('Error al guardar usuario:', error)
        const message = error.response?.data?.message || 'Error al guardar usuario'
        showAlert('error', message)
    } finally {
        submitting.value = false
    }
}

// Confirmar desactivación
const confirmDeactivate = (usuario) => {
    userToChangeStatus.value = usuario
    statusAction.value = 'deactivate'
    showStatusModal.value = true
}

// Confirmar activación
const confirmActivate = (usuario) => {
    userToChangeStatus.value = usuario
    statusAction.value = 'activate'
    showStatusModal.value = true
}

// Cambiar estado del usuario
const changeUserStatus = async () => {
    try {
        changingStatus.value = true
        
        if (statusAction.value === 'deactivate') {
            await usuarioService.delete(userToChangeStatus.value.id)
            showAlert('success', 'Usuario desactivado correctamente')
        } else {
            await usuarioService.activate(userToChangeStatus.value.id)
            showAlert('success', 'Usuario activado correctamente')
        }
        
        showStatusModal.value = false
        userToChangeStatus.value = null
        statusAction.value = ''
        await loadUsuarios()
    } catch (error) {
        console.error('Error al cambiar estado del usuario:', error)
        const message = statusAction.value === 'deactivate' 
            ? 'Error al desactivar usuario' 
            : 'Error al activar usuario'
        showAlert('error', message)
    } finally {
        changingStatus.value = false
    }
}

// Mostrar alerta
const showAlert = (type, message) => {
    alert.value = { show: true, type, message }
    setTimeout(() => {
        alert.value.show = false
    }, 5000)
}

// Utilidades
const getInitials = (nombre) => {
    if (!nombre) return '??'
    return nombre.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2)
}

const getRoleBadgeClass = (roleName) => {
    if (!roleName) return 'bg-gray-100 text-gray-800'

    switch (roleName.toLowerCase()) {
        case 'admin':
        case 'administrador':
            return 'bg-red-100 text-red-800'
        case 'contador':
            return 'bg-blue-100 text-blue-800'
        case 'auditor':
            return 'bg-purple-100 text-purple-800'
        default:
            return 'bg-gray-100 text-gray-800'
    }
}

const formatDate = (dateString) => {
    if (!dateString) return 'Nunca'
    return new Date(dateString).toLocaleDateString('es-GT', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>