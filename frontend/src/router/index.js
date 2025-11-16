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
    path: '/dashboard-presupuestario',
    name: 'PresupuestoDashboard',
    component: () => import('@/views/PresupuestoDashboard.vue'),
    meta: { requiresAuth: true }
  },
  // Catálogos
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
  },
  {
    path: '/documentos',
    name: 'Documentos',
    component: () => import('@/views/Documentos.vue'),
    meta: { requiresAuth: true }
  },
  // Operaciones
  {
    path: '/presupuestos',
    name: 'Presupuestos',
    component: () => import('@/views/Presupuestos.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/reportes',
    name: 'Reportes',
    component: () => import('@/views/Reportes.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/saldos-renglones',
    name: 'SaldosRenglones',
    component: () => import('@/views/SaldosRenglones.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/movimientos',
    name: 'Movimientos',
    component: () => import('@/views/Movimientos.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/facturas',
    name: 'Facturas',
    component: () => import('@/views/FacturasNuevo.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/intras',
    name: 'Intras',
    component: () => import('@/views/Intras.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/cur',
    name: 'CUR',
    component: () => import('@/views/CUR.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/actas-baja-cuantia',
    name: 'ActasBajaCuantia',
    component: () => import('@/views/ActasBajaCuantia.vue'),
    meta: { requiresAuth: true }
  },
  // Administración
  {
    path: '/usuarios',
    name: 'Usuarios',
    component: () => import('@/views/Usuarios.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/bitacora',
    name: 'Bitacora',
    component: () => import('@/views/Bitacora.vue'),
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
