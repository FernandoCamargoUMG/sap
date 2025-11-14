import apiClient from './api'

export default {
  /**
   * NOTA: El backend no tiene BitacoraController, solo registra automáticamente.
   * Estos métodos están comentados hasta que se implemente el endpoint en el backend.
   */
  
  // TODO: Implementar BitacoraController en el backend para estos endpoints:
  
  /**
   * Obtener todos los registros de bitácora
   * PENDIENTE: Necesita endpoint GET /api/bitacora en el backend
   */
  getAll(params = {}) {
    console.warn('⚠️ Endpoint /bitacora no implementado en el backend')
    return Promise.resolve({ data: { bitacora: [] } })
  },

  /**
   * Obtener un registro específico de bitácora
   * PENDIENTE: Necesita endpoint GET /api/bitacora/{id} en el backend
   */
  getById(id) {
    console.warn('⚠️ Endpoint /bitacora/{id} no implementado en el backend')
    return Promise.resolve({ data: {} })
  },

  /**
   * Obtener bitácora por usuario
   * PENDIENTE: Implementar filtros en BitacoraController
   */
  getByUsuario(usuarioId, params = {}) {
    console.warn('⚠️ Filtros de bitácora no implementados en el backend')
    return Promise.resolve({ data: { bitacora: [] } })
  },

  /**
   * Obtener bitácora por tabla afectada
   * PENDIENTE: Implementar filtros en BitacoraController
   */
  getByTabla(tabla, params = {}) {
    console.warn('⚠️ Filtros de bitácora no implementados en el backend')
    return Promise.resolve({ data: { bitacora: [] } })
  },

  /**
   * Obtener bitácora por rango de fechas
   * PENDIENTE: Implementar filtros en BitacoraController
   */
  getByDateRange(fechaInicio, fechaFin, params = {}) {
    console.warn('⚠️ Filtros de bitácora no implementados en el backend')
    return Promise.resolve({ data: { bitacora: [] } })
  },

  /**
   * Obtener bitácora por acción
   * PENDIENTE: Implementar filtros en BitacoraController
   */
  getByAccion(accion, params = {}) {
    console.warn('⚠️ Filtros de bitácora no implementados en el backend')
    return Promise.resolve({ data: { bitacora: [] } })
  },

  /**
   * Acciones disponibles
   */
  getAcciones() {
    return {
      data: [
        { id: 'creado', nombre: 'Creado', descripcion: 'Registro creado' },
        { id: 'modificado', nombre: 'Modificado', descripcion: 'Registro actualizado' },
        { id: 'eliminado', nombre: 'Eliminado', descripcion: 'Registro eliminado (soft delete)' },
        { id: 'anulado', nombre: 'Anulado', descripcion: 'Registro anulado' },
        { id: 'restaurado', nombre: 'Restaurado', descripcion: 'Registro restaurado' }
      ]
    }
  },

  /**
   * Tablas monitoreadas
   */
  getTablas() {
    return {
      data: [
        { id: 'usuarios', nombre: 'Usuarios' },
        { id: 'renglones', nombre: 'Renglones' },
        { id: 'presupuesto_cab', nombre: 'Presupuestos' },
        { id: 'movimiento_cab', nombre: 'Movimientos' },
        { id: 'proveedores', nombre: 'Proveedores' },
        { id: 'factura_cab', nombre: 'Facturas' },
        { id: 'intras', nombre: 'Transferencias (INTRAS)' },
        { id: 'cur', nombre: 'Compromisos (CUR)' },
        { id: 'documentos', nombre: 'Documentos' }
      ]
    }
  }
}