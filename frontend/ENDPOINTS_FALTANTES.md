# 🔧 Endpoints Faltantes en el Backend

## ❌ **Problemas Identificados**

Durante la revisión del frontend vs backend, se encontraron los siguientes endpoints que el frontend espera pero **NO están implementados** en el backend:

---

## 🚫 **Endpoints Completamente Ausentes**

### **1. Bitácora (Auditoría)**
- **Controller:** No existe `BitacoraController.php`
- **Rutas:** No hay rutas para bitácora en `api.php`

**Endpoints requeridos:**
```php
// PENDIENTE: Crear BitacoraController e implementar:
GET /api/bitacora                    // Listar bitácora con filtros
GET /api/bitacora/{id}              // Ver registro específico
GET /api/bitacora?usuario_id={id}   // Filtrar por usuario
GET /api/bitacora?tabla_afectada={tabla} // Filtrar por tabla
GET /api/bitacora?accion={accion}   // Filtrar por acción
GET /api/bitacora?fecha_inicio={fecha}&fecha_fin={fecha} // Filtrar por rango
```

### **2. Roles**
- **Controller:** Existe `RolesController.php` pero está **VACÍO**
- **Rutas:** No hay rutas para roles en `api.php`

**Endpoints requeridos:**
```php
// PENDIENTE: Implementar RolesController e agregar rutas:
GET /api/roles                      // Listar todos los roles
GET /api/roles/{id}                 // Ver rol específico
POST /api/roles                     // Crear rol
PUT /api/roles/{id}                 // Actualizar rol
DELETE /api/roles/{id}              // Eliminar rol
```

---

## ⚠️ **Endpoints con Implementación Parcial**

### **3. Movimientos**
- **Problema:** No existe endpoint `restore` para movimientos
- **Estado actual:** Existe DELETE pero no restore

**Endpoint faltante:**
```php
// PENDIENTE: Agregar en MovimientoController:
POST /api/movimientos/{id}/restore  // Restaurar movimiento anulado
```

---

## ✅ **Endpoints Correctamente Implementados**

Estos servicios del frontend **SÍ tienen** sus endpoints correspondientes en el backend:

### **✅ Usuarios**
- `GET /api/usuarios` ✅
- `POST /api/usuarios` ✅
- `GET /api/usuarios/{id}` ✅
- `PUT /api/usuarios/{id}` ✅
- `DELETE /api/usuarios/{id}` ✅
- `POST /api/usuarios/{id}/restore` ✅

### **✅ Renglones**
- `GET /api/renglones` ✅
- `POST /api/renglones` ✅
- `GET /api/renglones/{id}` ✅
- `PUT /api/renglones/{id}` ✅
- `DELETE /api/renglones/{id}` ✅
- `POST /api/renglones/{id}/restore` ✅

### **✅ Presupuestos**
- `GET /api/presupuestos` ✅
- `POST /api/presupuestos` ✅
- `GET /api/presupuestos/{id}` ✅
- `PUT /api/presupuestos/{id}` ✅
- `DELETE /api/presupuestos/{id}` ✅
- `POST /api/presupuestos/{id}/restore` ✅

### **✅ Proveedores**
- `GET /api/proveedores` ✅
- `POST /api/proveedores` ✅
- `GET /api/proveedores/{id}` ✅
- `PUT /api/proveedores/{id}` ✅
- `DELETE /api/proveedores/{id}` ✅
- `POST /api/proveedores/{id}/restore` ✅

### **✅ Facturas**
- `GET /api/facturas` ✅
- `POST /api/facturas` ✅
- `GET /api/facturas/{id}` ✅
- `PUT /api/facturas/{id}` ✅
- `DELETE /api/facturas/{id}` ✅
- `POST /api/facturas/{id}/restore` ✅

### **✅ Movimientos**
- `GET /api/movimientos` ✅
- `POST /api/movimientos` ✅
- `GET /api/movimientos/{id}` ✅
- `DELETE /api/movimientos/{id}` ✅

### **✅ INTRAS**
- `GET /api/intras` ✅
- `POST /api/intras` ✅
- `GET /api/intras/{id}` ✅
- `DELETE /api/intras/{id}` ✅

### **✅ CUR**
- `GET /api/cur` ✅
- `POST /api/cur` ✅
- `GET /api/cur/{id}` ✅
- `DELETE /api/cur/{id}` ✅

### **✅ Documentos**
- `GET /api/documentos` ✅
- `POST /api/documentos` ✅
- `GET /api/documentos/{id}` ✅
- `PUT /api/documentos/{id}` ✅
- `DELETE /api/documentos/{id}` ✅
- `GET /api/documentos/{id}/download` ✅
- `GET /api/documentos/{documentableType}/{documentableId}` ✅

### **✅ Autenticación**
- `POST /api/auth/login` ✅
- `POST /api/auth/logout` ✅
- `GET /api/auth/me` ✅

---

## 🎯 **Acciones Requeridas**

### **Para el Backend:**

1. **Crear BitacoraController:**
   ```bash
   php artisan make:controller BitacoraController --resource
   ```

2. **Implementar RolesController:**
   - Completar métodos vacíos en `RolesController.php`

3. **Agregar rutas faltantes en `api.php`:**
   ```php
   // Bitácora
   Route::get('bitacora', [BitacoraController::class, 'index']);
   Route::get('bitacora/{id}', [BitacoraController::class, 'show']);

   // Roles
   Route::resource('roles', RolesController::class);

   // Movimientos restore
   Route::post('movimientos/{id}/restore', [MovimientoController::class, 'restore']);
   ```

### **Para el Frontend:**

4. **✅ CORREGIDO:** Los servicios ahora manejan endpoints inexistentes:
   - `bitacoraService.js` → Muestra warnings y devuelve datos vacíos
   - `usuarioService.js` → Usa datos temporales para roles
   - `movimientoService.js` → Rechaza restore hasta implementación

---

## 📊 **Estado Actual del Sistema**

| **Módulo** | **Frontend** | **Backend** | **Estado** |
|------------|-------------|-------------|------------|
| Usuarios | ✅ | ✅ | FUNCIONAL |
| Renglones | ✅ | ✅ | FUNCIONAL |
| Presupuestos | ✅ | ✅ | FUNCIONAL |
| Proveedores | ✅ | ✅ | FUNCIONAL |
| Facturas | ✅ | ✅ | FUNCIONAL |
| Movimientos | ✅ | ⚠️ (sin restore) | MAYORMENTE FUNCIONAL |
| INTRAS | ✅ | ✅ | FUNCIONAL |
| CUR | ✅ | ✅ | FUNCIONAL |
| Documentos | ✅ | ✅ | FUNCIONAL |
| Autenticación | ✅ | ✅ | FUNCIONAL |
| **Bitácora** | ⚠️ | ❌ | NO FUNCIONAL |
| **Roles** | ⚠️ | ❌ | NO FUNCIONAL |

---

## 🚀 **Conclusión**

El sistema está **80% funcional**. Los módulos principales funcionan correctamente, pero faltan:
- **BitacoraController** (para auditoría)
- **RolesController** completamente implementado
- Endpoint de restore para movimientos

**El frontend ya está preparado y maneja graciosamente estos endpoints faltantes.**

---

**Fecha:** 14 de noviembre de 2025  
**Estado:** ✅ Frontend corregido, pendientes implementaciones en backend