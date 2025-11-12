# 💰 Sistema de Administración Presupuestaria (SAP)
**Proyecto Académico — Universidad Mariano Gálvez de Guatemala**  
**Autor:** Jenry Emanuel Teletor Rosales  
**Asesor:** Ing. Carlos Eduardo Hernández Herrera  
**Fecha:** 2025  

---

## 🧭 Descripción General
El **Sistema de Administración Presupuestaria (SAP)** es una aplicación web financiera desarrollada en **Laravel 12 (Backend)** y **Vue.js (Frontend)**.  
Su objetivo es optimizar la **planificación, ejecución y control del presupuesto anual** mediante módulos financieros interconectados.  

El sistema utiliza **Soft Delete** y **control de estado lógico (1=activo / 0=inactivo)** para mantener integridad histórica y cumplimiento con auditorías.

---

## ⚙️ Tecnologías

| Componente | Tecnología | Versión | Descripción |
|-------------|-------------|----------|--------------|
| 🧩 Backend | Laravel | 12.x | API RESTful |
| 🎨 Frontend | Vue.js | 3.x | SPA (Single Page Application) |
| 🗄️ Base de Datos | MySQL | 8.x | Sistema relacional |
| 🔐 Autenticación | Sesiones Laravel | — | Autenticación basada en sesiones |
| 📄 Reportes | DomPDF / Laravel-Excel | — | Exportación PDF y Excel |

---

## 🧱 Estructura General

### 🔹 Backend
```
/backend
 ├── app/
 │   ├── Models/        # Modelos Eloquent
 │   ├── Http/
 │   │   ├── Controllers/ # Lógica de negocio
 │   │   ├── Requests/    # Validaciones
 │   │   └── Middleware/  # Roles, Auditoría
 │   └── Services/      # Reportes y cálculos financieros
 ├── database/
 │   ├── migrations/    # Estructura de tablas
 │   ├── seeders/       # Datos iniciales
 │   └── factories/
 └── routes/
     ├── api.php        # Endpoints REST
```

### 🔹 Frontend
```
/frontend
 ├── src/
 │   ├── views/         # Pantallas principales
 │   ├── components/    # Componentes reutilizables
 │   ├── store/         # Vuex/Pinia (estado global)
 │   ├── router/        # Rutas protegidas por rol
 │   └── services/      # Consumo de API
```

---

## 🏗️ Creación de Migraciones (Prompts)

Cada tabla se crea con su respectiva migración.  
Laravel generará las estructuras con el comando:

```bash
php artisan make:migration create_nombre_tabla_table
```

Ejemplo para crear la tabla `usuarios`:

```bash
php artisan make:migration create_usuarios_table
```

### 📦 Migración ejemplo (usuarios)

```php
Schema::create('usuarios', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 100);
    $table->string('correo', 100)->unique();
    $table->string('contraseña');
    $table->foreignId('rol_id')->constrained('roles');
    $table->tinyInteger('estado')->default(1);
    $table->timestamps();
    $table->softDeletes(); // crea 'deleted_at'
});
```

Luego ejecuta todas las migraciones:
```bash
php artisan migrate
```

Y carga datos iniciales (roles, usuario admin, etc.):
```bash
php artisan db:seed
```

---

## 🔄 Flujo de Acciones del Sistema

### 🔹 1. Creación de registros
- El usuario con permisos **“editor” o “administrador”** crea el registro.  
- Laravel lo guarda con `estado = 1` y `deleted_at = NULL`.  
- Se genera una entrada en la **bitácora** con acción `creado`.

### 🔹 2. Modificación de registros
- Se actualiza el registro (`updated_at` cambia).  
- Se registra en la **bitácora** con acción `modificado`.

### 🔹 3. Eliminación lógica (Soft Delete)
- El registro **no se borra físicamente**, sino que:
  - `estado = 0`
  - `deleted_at = NOW()`
- El registro **desaparece del front**, pero sigue disponible para auditoría.  
- Se genera un registro en **bitácora** con acción `eliminado`.

### 🔹 4. Restauración de registros
- El administrador puede restaurar registros:
  ```php
  $registro->update(['estado' => 1, 'deleted_at' => null]);
  ```
- La acción se registra en bitácora como `restaurado`.

---

## 🧾 Relación entre Entidades

### Diagrama lógico resumido:

```
usuarios (1)──< roles
usuarios (1)──< bitacora
renglones (1)──< presupuesto_det
presupuesto_cab (1)──< presupuesto_det
movimiento_cab (1)──< movimiento_det
proveedores (1)──< factura_cab
factura_cab (1)──< factura_det
renglones (1)──< factura_det
renglones (1)──< cur
documentos (1)──< factura_cab / cur / presupuesto_det / movimiento_cab
```

---

## 🧩 Flujo del Sistema (UX + API)

| Acción | Backend | Frontend | Resultado |
|--------|----------|-----------|------------|
| Crear presupuesto | POST `/api/presupuestos` | Formulario de presupuesto | Se crea encabezado + detalle |
| Registrar movimiento | POST `/api/movimientos` | Formulario transacción | Se actualizan saldos en renglones |
| Crear factura | POST `/api/facturas` | Formulario factura + PDF | Se vincula a proveedor y renglón |
| Transferencia (Intras) | POST `/api/intras` | Formulario de transferencia | Afecta renglones origen/destino |
| CUR | POST `/api/cur` | Asignación a proveedor | Se guarda con documento soporte |
| Eliminar registro | PATCH `/api/{modulo}/{id}/soft-delete` | Botón “Eliminar” | `estado=0`, `deleted_at` actualizado |
| Restaurar registro | PATCH `/api/{modulo}/{id}/restore` | Botón “Restaurar” | `estado=1`, `deleted_at=NULL` |
| Consultar bitácora | GET `/api/bitacora` | Vista “Historial” | Auditoría por usuario y fecha |

---

## 🧠 Buenas Prácticas

1. **Usar SoftDeletes en todos los modelos transaccionales:**
   ```php
   use Illuminate\Database\Eloquent\SoftDeletes;
   ```
2. **Validar datos con FormRequest (Request personalizado).**
3. **Registrar todas las acciones en Bitácora.**
4. **Scopes personalizados** para obtener solo activos:
   ```php
   public function scopeActivos($query) {
       return $query->where('estado', 1)->whereNull('deleted_at');
   }
   ```
5. **Middleware de Autenticación + Roles:**
   - `/admin/*` → solo administradores  
   - `/finanzas/*` → contadores y administradores  
   - `/lectura/*` → acceso restringido solo lectura  

---

## 📊 Reportes y Descargas

El módulo de reportes genera información consolidada de todos los módulos:
- **Presupuestos ejecutados por mes/año.**
- **Movimientos por renglón.**
- **Facturas por proveedor.**
- **Transferencias (Intras) registradas.**

Exportaciones disponibles:
- 📄 PDF → `DomPDF`
- 📊 Excel → `Laravel-Excel`

---

## 🗄️ Bitácora y Auditoría

Cada acción del sistema genera un registro en la tabla `bitacora`:
| Campo | Descripción |
|--------|--------------|
| tabla_afectada | Módulo afectado |
| registro_id | ID del registro |
| accion | creado, modificado, eliminado |
| usuario_id | Usuario responsable |
| fecha_accion | Fecha/Hora |
| detalle | Descripción contextual |

**Ejemplo:**  
> El usuario “admin” modificó el renglón “110 Gasto Operativo” el 2025-05-12 09:34:11

---

## 🔐 Seguridad
- Autenticación basada en sesiones de Laravel.  
- Validación de roles y permisos.  
- Protección de rutas API con middleware.  
- Contraseñas hasheadas con MD5 (requerimiento del cliente).  
- Cifrado de contraseñas (`bcrypt`).  
- Control de sesión por tiempo.  

---

## ⚙️ Comandos útiles

| Acción | Comando |
|--------|----------|
| Crear migración | `php artisan make:migration create_x_table` |
| Ejecutar migraciones | `php artisan migrate` |
| Ejecutar seeders | `php artisan db:seed` |
| Limpiar caché | `php artisan optimize:clear` |
| Levantar servidor | `php artisan serve` |

---

## 🧩 Flujo de Desarrollo

1. **Crear migraciones y modelos.**  
2. **Agregar SoftDeletes y estado en cada modelo.**  
3. **Crear controladores RESTful.**  
4. **Agregar validaciones con FormRequest.**  
5. **Conectar endpoints a Vue mediante axios.**  
6. **Proteger rutas con middleware de autenticación y roles.**  
7. **Registrar eventos en Bitácora.**  
8. **Implementar reportes PDF/Excel.**  

---

## 📄 Licencia
Proyecto académico desarrollado con fines educativos  
**Universidad Mariano Gálvez de Guatemala — 2025**  
© **Jenry Emanuel Teletor Rosales**
