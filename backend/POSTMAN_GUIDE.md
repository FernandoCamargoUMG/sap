# 📋 Colección Postman - SAP (Sistema de Administración Presupuestaria)

## 🚀 Importación de la Colección

### Paso 1: Importar Colección
1. Abre **Postman**
2. Haz clic en **Import** (botón superior izquierdo)
3. Selecciona el archivo `SAP_API_Collection.postman_collection.json`
4. Haz clic en **Import**

### Paso 2: Importar Entorno
1. Haz clic en **Import** nuevamente
2. Selecciona el archivo `SAP_Local_Environment.postman_environment.json`
3. Haz clic en **Import**
4. En la esquina superior derecha, selecciona el entorno **"SAP - Local Development"**

## 📚 Estructura de la Colección

La colección contiene **10 módulos principales** con un total de **70+ endpoints**:

### 1️⃣ Autenticación (3 endpoints)
- `POST /api/auth/login` - Iniciar sesión
- `GET /api/auth/me` - Usuario actual
- `POST /api/auth/logout` - Cerrar sesión

### 2️⃣ Usuarios (7 endpoints)
- Listar usuarios activos
- Crear usuario
- Ver usuario específico
- Actualizar usuario
- Eliminar usuario (soft delete)
- Listar usuarios eliminados
- Restaurar usuario

### 3️⃣ Renglones Presupuestarios (7 endpoints)
- Listar renglones
- Crear renglón
- Ver renglón
- **Consultar saldo disponible** ⭐
- Actualizar renglón
- Eliminar renglón
- Restaurar renglón

### 4️⃣ Presupuestos (5 endpoints)
- Listar presupuestos con detalles
- Crear presupuesto (cabecera + detalles)
- Ver presupuesto específico
- Actualizar presupuesto
- Eliminar presupuesto

### 5️⃣ Movimientos Presupuestarios (7 endpoints)
- Listar movimientos
- Crear movimiento - **Ampliación** 📈
- Crear movimiento - **Compromiso** 🔒
- Crear movimiento - **Egreso** 💸
- Ver movimiento
- Anular movimiento (reversa saldos)
- Listar movimientos anulados

**Tipos de movimientos soportados:**
- `ampliacion` - Incrementa presupuesto y saldo
- `reduccion` - Reduce presupuesto y saldo
- `compromiso` - Reserva recursos (reduce saldo)
- `devengado` - Ejecuta gasto (reduce saldo)
- `egreso` - Pago efectivo (reduce saldo)
- `liberacion` - Libera recursos comprometidos
- `reintegro` - Devuelve fondos

### 6️⃣ Proveedores (6 endpoints)
- Listar proveedores
- Crear proveedor
- Ver proveedor
- Actualizar proveedor
- Eliminar proveedor
- Restaurar proveedor

### 7️⃣ Facturas (6 endpoints)
- Listar facturas con detalles
- Crear factura (cabecera + múltiples detalles)
- Ver factura específica
- Actualizar factura
- Eliminar factura
- Restaurar factura

### 8️⃣ Transferencias - INTRAS (4 endpoints)
- Listar transferencias entre renglones
- Crear transferencia (afecta saldos origen/destino)
- Ver transferencia específica
- Anular transferencia (reversa saldos)

### 9️⃣ Compromisos - CUR (4 endpoints)
- Listar compromisos de pago
- Crear compromiso (reserva recursos)
- Ver compromiso específico
- Anular compromiso (libera recursos)

### 🔟 Documentos (8 endpoints)
- Listar todos los documentos
- Listar documentos filtrados por entidad
- Subir documento
- Ver documento
- Descargar archivo
- Actualizar metadatos
- Eliminar documento
- Obtener documentos de entidad específica

## 🎯 Flujo de Prueba Recomendado

### 1. Autenticación
```
1. Login con admin (administrador@contabilidad.com / admin123)
2. Verificar sesión con "Me"
```

### 2. Configuración Inicial
```
3. Crear Renglones Presupuestarios
   - Ejemplo: 1.1.21.1.011 (Sueldos) con Q150,000
   - Ejemplo: 1.1.24.1.001 (Materiales) con Q100,000

4. Crear Presupuesto con Detalles
   - Asignar montos a cada renglón creado
```

### 3. Gestión de Proveedores
```
5. Crear Proveedor (NIT, nombre, contacto)
6. Crear Factura con Detalles
   - Vincular a proveedor
   - Asociar líneas de detalle a renglones
```

### 4. Movimientos Presupuestarios
```
7. Crear Movimiento de Ampliación
   - Aumenta presupuesto de un renglón
   
8. Crear Movimiento de Compromiso
   - Reserva fondos (reduce saldo disponible)
   
9. Crear Movimiento de Egreso
   - Ejecuta gasto (reduce saldo)
   
10. Consultar Saldo del Renglón
    - Verificar afectaciones
```

### 5. Transferencias y Compromisos
```
11. Crear Transferencia (INTRA)
    - Mover fondos entre renglones
    
12. Crear Compromiso (CUR)
    - Generar compromiso de pago
```

### 6. Gestión Documental
```
13. Subir Documentos
    - Adjuntar PDFs a facturas, movimientos, etc.
```

## 🔐 Autenticación

El sistema usa **autenticación basada en sesiones de Laravel**. Después del login exitoso:
- La sesión se mantiene automáticamente via cookies
- No se requiere token JWT
- Todas las rutas bajo `/api/*` están excluidas de CSRF

### Credenciales Predeterminadas

**Administrador:**
- Correo: `administrador@contabilidad.com`
- Contraseña: `admin123`
- Rol: Administrador (ID: 1)

## 📊 Variables de Entorno

| Variable | Valor | Descripción |
|----------|-------|-------------|
| `base_url` | `http://localhost:8000` | URL base de la API |
| `usuario_id` | Auto-guardado | ID del usuario autenticado |
| `admin_correo` | `administrador@contabilidad.com` | Correo admin |
| `admin_password` | `admin123` | Contraseña admin |

## 🧪 Scripts de Prueba Automáticos

La colección incluye scripts de prueba que:
- ✅ Guardan automáticamente el `usuario_id` después del login
- ✅ Validan códigos de respuesta HTTP
- ✅ Facilitan el flujo de pruebas

## 📝 Ejemplos de JSON

### Crear Renglón
```json
{
    "codigo": "1.1.21.1.011",
    "descripcion": "Sueldos Personal Administrativo",
    "presupuesto_vigente": 150000.00,
    "saldo_disponible": 150000.00,
    "estado": 1
}
```

### Crear Movimiento de Egreso
```json
{
    "tipo_movimiento": "egreso",
    "fecha_movimiento": "2025-11-12",
    "descripcion": "Pago de salarios quincenales",
    "monto_total": 30000.00,
    "estado": 1,
    "detalles": [
        {
            "renglon_id": 1,
            "descripcion": "Pago quincenal noviembre",
            "monto": 30000.00,
            "estado": 1
        }
    ]
}
```

### Crear Factura con Detalles
```json
{
    "proveedor_id": 1,
    "numero_factura": "FAC-001-2025",
    "serie_factura": "A",
    "fecha_factura": "2025-11-10",
    "descripcion": "Compra de materiales de oficina",
    "total": 5500.00,
    "estado": 1,
    "detalles": [
        {
            "renglon_id": 2,
            "descripcion": "Resmas de papel",
            "cantidad": 100,
            "precio_unitario": 35.00,
            "subtotal": 3500.00,
            "estado": 1
        },
        {
            "renglon_id": 2,
            "descripcion": "Lapiceros",
            "cantidad": 200,
            "precio_unitario": 10.00,
            "subtotal": 2000.00,
            "estado": 1
        }
    ]
}
```

### Crear Transferencia (INTRA)
```json
{
    "renglon_origen_id": 1,
    "renglon_destino_id": 2,
    "monto": 10000.00,
    "descripcion": "Transferencia de fondos no utilizados",
    "fecha_transferencia": "2025-11-12",
    "estado": 1
}
```

## ⚠️ Validaciones Importantes

### Movimientos
- Los movimientos de tipo `egreso`, `compromiso`, `devengado` **validan saldo disponible**
- Si el saldo es insuficiente, retorna error 500 con mensaje descriptivo
- Al anular un movimiento, se **reversan automáticamente** los saldos

### Transferencias (INTRAS)
- Valida que el renglón origen tenga saldo suficiente
- No permite transferir al mismo renglón (origen ≠ destino)
- Al anular, se reversan los saldos en ambos renglones

### Compromisos (CUR)
- El número de CUR debe ser único
- Valida saldo disponible antes de comprometer
- Al anular, libera los recursos comprometidos

### Facturas
- Si se actualizan los detalles, se eliminan los anteriores y se crean nuevos
- El total debe coincidir con la suma de subtotales de los detalles
- Al eliminar factura, también se marcan como eliminados sus detalles

## 🔄 Soft Delete

Todas las entidades principales usan **Soft Delete**:
- `usuarios`
- `renglones`
- `presupuestos`
- `proveedores`
- `facturas`
- `movimientos`
- `documentos`

Los registros eliminados:
- ✅ Se pueden listar con `/deleted/list`
- ✅ Se pueden restaurar con `/{id}/restore`
- ✅ Mantienen integridad referencial

## 📈 Bitácora Automática

Cuando hay sesión activa, **todas las operaciones CRUD se registran automáticamente** en la tabla `bitacora`:
- Usuario que realizó la acción
- Tabla afectada
- ID del registro
- Tipo de acción (creado, modificado, eliminado, restaurado, anulado)
- Descripción detallada
- Fecha y hora

## 🛠️ Solución de Problemas

### Error 419 - Page Expired
**Causa:** Token CSRF faltante
**Solución:** Ya está configurado. Las rutas `/api/*` están excluidas de CSRF

### Error 500 - Saldo insuficiente
**Causa:** El renglón no tiene saldo disponible suficiente
**Solución:** 
1. Consultar saldo del renglón (`GET /renglones/{id}/saldo`)
2. Crear movimiento de ampliación si es necesario
3. Reintentar la operación

### Error 401 - Unauthorized
**Causa:** No hay sesión activa
**Solución:** Ejecutar primero el endpoint `POST /auth/login`

### Error 404 - Not Found
**Causa:** El ID del recurso no existe
**Solución:** Verificar que el ID existe usando los endpoints de listado

## 📞 Soporte

Para dudas o problemas:
- Revisar los logs de Laravel en `storage/logs/laravel.log`
- Verificar que el servidor está corriendo: `php artisan serve`
- Confirmar que la base de datos está configurada correctamente

---

**Desarrollado para:** Universidad Mariano Gálvez de Guatemala  
**Sistema:** SAP - Sistema de Administración Presupuestaria  
**Versión:** 1.0  
**Fecha:** Noviembre 2025
