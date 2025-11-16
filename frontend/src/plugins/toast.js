// Sistema simple de notificaciones toast
class ToastService {
  constructor() {
    this.container = null
    this.init()
  }

  init() {
    // Crear contenedor para los toasts
    this.container = document.createElement('div')
    this.container.id = 'toast-container'
    this.container.className = 'fixed top-4 right-4 z-[9999] space-y-2'
    document.body.appendChild(this.container)
  }

  show(message, type = 'info', duration = 4000) {
    const toast = document.createElement('div')
    
    const baseClasses = 'px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 ease-in-out max-w-sm'
    const typeClasses = {
      success: 'bg-green-500 text-white',
      error: 'bg-red-500 text-white',
      warning: 'bg-yellow-500 text-white',
      info: 'bg-blue-500 text-white'
    }

    toast.className = `${baseClasses} ${typeClasses[type] || typeClasses.info} translate-x-full opacity-0`
    
    toast.innerHTML = `
      <div class="flex items-center justify-between">
        <span class="flex-1 text-sm font-medium">${message}</span>
        <button class="ml-3 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    `

    this.container.appendChild(toast)

    // Animación de entrada
    setTimeout(() => {
      toast.classList.remove('translate-x-full', 'opacity-0')
      toast.classList.add('translate-x-0', 'opacity-100')
    }, 100)

    // Auto-eliminar después del tiempo especificado
    setTimeout(() => {
      this.remove(toast)
    }, duration)

    return toast
  }

  remove(toast) {
    if (toast && toast.parentElement) {
      toast.classList.remove('translate-x-0', 'opacity-100')
      toast.classList.add('translate-x-full', 'opacity-0')
      
      setTimeout(() => {
        if (toast.parentElement) {
          toast.remove()
        }
      }, 300)
    }
  }

  success(message, duration) {
    return this.show(message, 'success', duration)
  }

  error(message, duration) {
    return this.show(message, 'error', duration)
  }

  warning(message, duration) {
    return this.show(message, 'warning', duration)
  }

  info(message, duration) {
    return this.show(message, 'info', duration)
  }
}

const toastService = new ToastService()

export default {
  install(app) {
    app.config.globalProperties.$toast = toastService
    app.provide('toast', toastService)
  }
}

export { toastService }