# 📚 API - Sistema de Administración Presupuestaria (SAP)

## 🔐 Autenticación

### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "correo": "administrador@contabilidad.com",
  "contraseña": "admin123"
}
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Inicio de sesión exitoso",
  "data": {
    "usuario": {
      "id": 1,
      "nombre": "Administrador",
      "correo": "administrador@contabilidad.com",
      "rol": "Admin",
      "rol_id": 1
    }
  }
}
```

### Obtener Usuario Actual
```http
GET /api/auth/me
```

### Logout
```http
POST /api/auth/logout
```

---

## 👥 Usuarios CRUD

### Listar Usuarios Activos
```http
GET /api/usuarios
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Administrador",
      "correo": "administrador@contabilidad.com",
      "rol_id": 1,
      "estado": 1,
      "rol": {
        "id": 1,
        "nombre": "Admin"
      }
    }
  ]
}
```

### Crear Usuario
```http
POST /api/usuarios
Content-Type: application/json

{
  "nombre": "Juan Pérez",
  "correo": "juan@contabilidad.com",
  "contraseña": "password123",
  "rol_id": 2,
  "estado": 1
}
```

### Ver Usuario Específico
```http
GET /api/usuarios/{id}
```

### Actualizar Usuario
```http
PUT /api/usuarios/{id}
Content-Type: application/json

{
  "nombre": "Juan Pérez Actualizado",
  "correo": "juan.actualizado@contabilidad.com",
  "rol_id": 2,
  "estado": 1
}
```

**Nota:** La contraseña es opcional en actualización. Si no se envía, no se modifica.

### Eliminar Usuario (Soft Delete)
```http
DELETE /api/usuarios/{id}
```

### Listar Usuarios Eliminados
```http
GET /api/usuarios/deleted/list
```

### Restaurar Usuario Eliminado
```http
POST /api/usuarios/{id}/restore
```

---

## 🧪 Ejemplos con PowerShell

### Login
```powershell
$loginResponse = Invoke-RestMethod -Method Post -Uri "http://localhost:8000/api/auth/login" -Body (@{
    correo = "administrador@contabilidad.com"
    contraseña = "admin123"
} | ConvertTo-Json) -ContentType "application/json" -SessionVariable session

# Guardar la sesión para reutilizarla
$global:session = $session
```

### Listar Usuarios
```powershell
Invoke-RestMethod -Method Get -Uri "http://localhost:8000/api/usuarios" -WebSession $global:session
```

### Crear Usuario
```powershell
Invoke-RestMethod -Method Post -Uri "http://localhost:8000/api/usuarios" -WebSession $global:session -Body (@{
    nombre = "María López"
    correo = "maria@contabilidad.com"
    contraseña = "password123"
    rol_id = 2
    estado = 1
} | ConvertTo-Json) -ContentType "application/json"
```

### Ver Usuario
```powershell
Invoke-RestMethod -Method Get -Uri "http://localhost:8000/api/usuarios/1" -WebSession $global:session
```

### Actualizar Usuario
```powershell
Invoke-RestMethod -Method Put -Uri "http://localhost:8000/api/usuarios/2" -WebSession $global:session -Body (@{
    nombre = "María López Actualizada"
    correo = "maria.actualizada@contabilidad.com"
    rol_id = 3
    estado = 1
} | ConvertTo-Json) -ContentType "application/json"
```

### Eliminar Usuario
```powershell
Invoke-RestMethod -Method Delete -Uri "http://localhost:8000/api/usuarios/2" -WebSession $global:session
```

### Ver Usuarios Eliminados
```powershell
Invoke-RestMethod -Method Get -Uri "http://localhost:8000/api/usuarios/deleted/list" -WebSession $global:session
```

### Restaurar Usuario
```powershell
Invoke-RestMethod -Method Post -Uri "http://localhost:8000/api/usuarios/2/restore" -WebSession $global:session
```

### Logout
```powershell
Invoke-RestMethod -Method Post -Uri "http://localhost:8000/api/auth/logout" -WebSession $global:session
```

---

## 📝 Notas Importantes

### Autenticación por Sesión
- El sistema usa sesiones de Laravel (no JWT)
- Debes usar `-SessionVariable` en el primer request y `-WebSession` en los siguientes
- Las sesiones se guardan en archivos (`storage/framework/sessions/`)

### Soft Delete
- Los usuarios eliminados NO se borran físicamente
- Se marca `estado = 0` y `deleted_at = NOW()`
- Pueden ser restaurados con el endpoint `/restore`

### Bitácora
- Todas las acciones CRUD se registran automáticamente en la tabla `bitacora`
- Se guarda: tabla, registro_id, acción, usuario_id, fecha, detalle

### Contraseñas
- Se usa hash MD5 (configurable en el modelo)
- El mutator aplica MD5 automáticamente al crear/actualizar
- En actualización, si no se envía contraseña, no se modifica

### Validaciones
- `nombre`: requerido, máx 100 caracteres
- `correo`: requerido, email válido, único (ignora soft deleted)
- `contraseña`: requerida en creación, mín 6 caracteres
- `rol_id`: requerido, debe existir en tabla `roles`
- `estado`: opcional, booleano (0 o 1)

---

## 🚀 Iniciar Servidor

```bash
php artisan serve
```

El servidor estará disponible en: `http://localhost:8000`

---

## 🔍 Ver Rutas Disponibles

```bash
php artisan route:list --path=api
```
