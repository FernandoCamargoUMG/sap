import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/renglones',
    name: 'Renglones',
    component: () => import('@/views/Renglones.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/proveedores',
    name: 'Proveedores',
    component: () => import('@/views/Proveedores.vue'),
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Guard de navegación
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Inicializar auth desde sessionStorage
  if (!authStore.usuario) {
    authStore.initializeAuth()
  }

  const requiresAuth = to.meta.requiresAuth !== false
  const isAuthenticated = authStore.isAuthenticated

  if (requiresAuth && !isAuthenticated) {
    // Ruta protegida y usuario no autenticado
    next('/login')
  } else if (to.path === '/login' && isAuthenticated) {
    // Usuario autenticado intentando ir al login
    next('/dashboard')
  } else {
    next()
  }
})

export default router
