<template>
  <AppLayout>
    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-black text-gray-900 mb-2 flex items-center">
          <span class="bg-gradient-cfag bg-clip-text text-transparent">Dashboard</span>
          <svg class="h-10 w-10 ml-3 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </h1>
        <p class="text-gray-600 text-lg">Bienvenido al Sistema de Administración Presupuestaria</p>
      </div>

      <!-- Cards de estadísticas -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 px-4 sm:px-0">
        <!-- Card 1 -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-200 hover:shadow-2xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 text-sm font-semibold mb-1">Total Usuarios</p>
              <p class="text-4xl font-black">-</p>
              <p class="text-blue-200 text-xs mt-2">Activos en el sistema</p>
            </div>
            <div class="bg-white/20 rounded-xl p-3 backdrop-blur-sm">
              <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div @click="$router.push('/renglones')" class="bg-gradient-to-br from-green-500 to-green-700 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-200 hover:shadow-2xl cursor-pointer">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100 text-sm font-semibold mb-1">Renglones</p>
              <p class="text-4xl font-black">-</p>
              <p class="text-green-200 text-xs mt-2">Presupuestarios</p>
            </div>
            <div class="bg-white/20 rounded-xl p-3 backdrop-blur-sm">
              <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-gradient-gold rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-200 hover:shadow-2xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-yellow-100 text-sm font-semibold mb-1">Presupuestos</p>
              <p class="text-4xl font-black">-</p>
              <p class="text-yellow-200 text-xs mt-2">En ejercicio fiscal</p>
            </div>
            <div class="bg-white/20 rounded-xl p-3 backdrop-blur-sm">
              <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-200 hover:shadow-2xl">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-100 text-sm font-semibold mb-1">Movimientos</p>
              <p class="text-4xl font-black">-</p>
              <p class="text-purple-200 text-xs mt-2">Registrados este mes</p>
            </div>
            <div class="bg-white/20 rounded-xl p-3 backdrop-blur-sm">
              <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Card de Bienvenida -->
      <div class="mt-8 px-4 sm:px-0">
        <div class="bg-gradient-cfag rounded-2xl shadow-2xl p-8 text-white relative overflow-hidden">
          <!-- Patrón decorativo -->
          <div class="absolute top-0 right-0 w-64 h-64 bg-secondary-500 rounded-full blur-3xl opacity-20"></div>
          <div class="absolute bottom-0 left-0 w-64 h-64 bg-white rounded-full blur-3xl opacity-10"></div>
          
          <div class="relative z-10 flex items-center justify-between">
            <div class="flex-1">
              <h2 class="text-3xl font-black mb-3">¡Bienvenido, {{ authStore.nombreUsuario }}!</h2>
              <p class="text-blue-100 text-lg mb-6">
                Has iniciado sesión correctamente. El sistema está listo para usar.
              </p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white/10 rounded-xl p-4 backdrop-blur-sm border border-white/20">
                  <div class="flex items-center space-x-3">
                    <div class="bg-secondary-500 rounded-lg p-2">
                      <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-blue-100">Rol</p>
                      <p class="text-lg font-bold">{{ authStore.rolUsuario }}</p>
                    </div>
                  </div>
                </div>
                <div class="bg-white/10 rounded-xl p-4 backdrop-blur-sm border border-white/20">
                  <div class="flex items-center space-x-3">
                    <div class="bg-green-500 rounded-lg p-2">
                      <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-blue-100">Estado</p>
                      <p class="text-lg font-bold">Sesión Activa ✓</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="hidden lg:block ml-8">
              <svg class="h-48 w-48 text-white opacity-20" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Información de SessionStorage -->
      <div class="mt-8 px-4 sm:px-0">
        <div class="bg-white rounded-2xl shadow-lg border-l-4 border-secondary-500 p-6">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div class="bg-secondary-100 rounded-xl p-3">
                <svg class="h-8 w-8 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-4 flex-1">
              <h3 class="text-xl font-bold text-gray-900 mb-2">Información de Sesión</h3>
              <p class="text-gray-600">
                Tu sesión se mantiene activa usando <code class="bg-primary-100 text-primary-700 px-2 py-1 rounded text-sm font-mono">sessionStorage</code>.
                La sesión permanecerá activa mientras mantengas la pestaña abierta.
              </p>
              <div class="mt-4 flex items-center space-x-2">
                <div class="h-3 w-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-semibold text-green-700">Conectado al servidor</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/components/AppLayout.vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>
