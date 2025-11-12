# 💼 SAP - Sistema de Administración Presupuestaria

> Sistema de gestión presupuestaria desarrollado para la **Universidad Mariano Gálvez de Guatemala**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

> **📌 ÚLTIMA ACTUALIZACIÓN:** 12 de noviembre de 2025 - Corrección de migración y relaciones polimórficas de documentos. Ver [CORRECCION_DOCUMENTOS.md](CORRECCION_DOCUMENTOS.md)

## 📋 Descripción

Sistema integral para la administración y control presupuestario institucional que incluye:

- ✅ Gestión de renglones presupuestarios
- ✅ Control de presupuestos por ejercicio fiscal
- ✅ Movimientos presupuestarios con afectación automática de saldos
- ✅ Gestión de proveedores y facturas
- ✅ Transferencias entre renglones (INTRAS)
- ✅ Compromisos de pago (CUR)
- ✅ Sistema de documentos adjuntos polimórfico
- ✅ Bitácora automática de auditoría
- ✅ Autenticación basada en sesiones Laravel
- ✅ Soft Delete en todas las entidades
- ✅ API RESTful completa

## 🚀 Instalación Rápida

### Prerrequisitos

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Node.js >= 18 (opcional, para frontend)

### Pasos de Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/FernandoCamargoUMG/sap.git
cd sap/backend

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sap
DB_USERNAME=root
DB_PASSWORD=

# 5. Ejecutar migraciones
php artisan migrate

# 6. Ejecutar seeders (datos iniciales)
php artisan db:seed

# 7. Iniciar servidor de desarrollo
php artisan serve
```

El sistema estará disponible en: `http://localhost:8000`

## 📚 Documentación API

### Credenciales Predeterminadas

**Administrador:**
- Email: `administrador@contabilidad.com`
- Contraseña: `admin123`

### Colección de Postman

Importa los archivos de Postman para probar la API:

1. **Colección:** `SAP_API_Collection.postman_collection.json`
2. **Entorno:** `SAP_Local_Environment.postman_environment.json`

📖 **Guía completa:** Ver [POSTMAN_GUIDE.md](POSTMAN_GUIDE.md)

### Script de Prueba Rápida

```powershell
# Ejecutar desde PowerShell
.\test-api.ps1
```

## 🏗️ Estructura del Proyecto

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controladores API
│   │   │   ├── AuthController.php
│   │   │   ├── UsuarioController.php
│   │   │   ├── RenglonController.php
│   │   │   ├── PresupuestoController.php
│   │   │   ├── MovimientoController.php
│   │   │   ├── ProveedorController.php
│   │   │   ├── FacturaController.php
│   │   │   ├── IntraController.php
│   │   │   ├── CurController.php
│   │   │   └── DocumentoController.php
│   │   └── Requests/        # Validaciones
│   │       ├── UsuarioRequest.php
│   │       ├── RenglonRequest.php
│   │       ├── PresupuestoRequest.php
│   │       ├── MovimientoRequest.php
│   │       ├── ProveedorRequest.php
│   │       └── FacturaRequest.php
│   └── Models/              # Modelos Eloquent
│       ├── Usuario.php
│       ├── Rol.php
│       ├── Bitacora.php
│       ├── Renglon.php
│       ├── PresupuestoCab.php
│       ├── PresupuestoDet.php
│       ├── MovimientoCab.php
│       ├── MovimientoDet.php
│       ├── Proveedor.php
│       ├── FacturaCab.php
│       ├── FacturaDet.php
│       ├── Intra.php
│       ├── Cur.php
│       └── Documento.php
├── database/
│   ├── migrations/          # 15 migraciones
│   └── seeders/            # Datos iniciales
├── routes/
│   └── api.php             # 70+ endpoints
└── storage/
    └── logs/               # Logs de la aplicación
```

## 🔌 Endpoints Principales

### Autenticación
```
POST   /api/auth/login      - Iniciar sesión
GET    /api/auth/me         - Usuario actual
POST   /api/auth/logout     - Cerrar sesión
```

### Usuarios
```
GET    /api/usuarios        - Listar usuarios
POST   /api/usuarios        - Crear usuario
GET    /api/usuarios/{id}   - Ver usuario
PUT    /api/usuarios/{id}   - Actualizar usuario
DELETE /api/usuarios/{id}   - Eliminar usuario
POST   /api/usuarios/{id}/restore - Restaurar usuario
```

### Renglones Presupuestarios
```
GET    /api/renglones           - Listar renglones
POST   /api/renglones           - Crear renglón
GET    /api/renglones/{id}      - Ver renglón
GET    /api/renglones/{id}/saldo - Consultar saldo
PUT    /api/renglones/{id}      - Actualizar renglón
DELETE /api/renglones/{id}      - Eliminar renglón
```

### Movimientos Presupuestarios
```
GET    /api/movimientos     - Listar movimientos
POST   /api/movimientos     - Crear movimiento (afecta saldos)
GET    /api/movimientos/{id} - Ver movimiento
DELETE /api/movimientos/{id} - Anular movimiento (reversa saldos)
```

**Tipos de movimiento soportados:**
- `ampliacion` - Incrementa presupuesto y saldo disponible
- `reduccion` - Reduce presupuesto y saldo disponible
- `compromiso` - Reserva recursos (reduce saldo disponible)
- `devengado` - Ejecuta gasto
- `egreso` - Pago efectivo
- `liberacion` - Libera recursos comprometidos
- `reintegro` - Devuelve fondos al renglón

### Proveedores y Facturas
```
GET    /api/proveedores     - Listar proveedores
POST   /api/proveedores     - Crear proveedor
GET    /api/facturas        - Listar facturas
POST   /api/facturas        - Crear factura con detalles
```

### Transferencias (INTRAS)
```
GET    /api/intras          - Listar transferencias
POST   /api/intras          - Crear transferencia entre renglones
DELETE /api/intras/{id}     - Anular transferencia
```

### Compromisos (CUR)
```
GET    /api/cur             - Listar compromisos
POST   /api/cur             - Crear compromiso de pago
DELETE /api/cur/{id}        - Anular compromiso
```

### Documentos
```
GET    /api/documentos                      - Listar documentos
POST   /api/documentos                      - Subir documento
GET    /api/documentos/{id}                 - Ver documento
GET    /api/documentos/{id}/download        - Descargar archivo
PUT    /api/documentos/{id}                 - Actualizar documento
DELETE /api/documentos/{id}                 - Eliminar documento
GET    /api/documentos/entity/{tipo}/{id}   - Documentos por entidad
```

**Relación Polimórfica:**
Los documentos pueden adjuntarse a cualquier entidad usando `documentable_type` y `documentable_id`:
- `FacturaCab` → Facturas (PDF, XML, comprobantes)
- `Cur` → Compromisos (solicitudes, autorizaciones)
- `PresupuestoCab` → Presupuestos (resoluciones, actas)
- `MovimientoCab` → Movimientos (oficios, memos)
- `Intra` → Transferencias (documentos soporte)

## 💡 Características Técnicas

### Transacciones y Validaciones
- Todas las operaciones críticas usan transacciones de base de datos (`DB::beginTransaction()`)
- Validación de saldos disponibles antes de movimientos/compromisos
- Reversión automática de saldos al anular operaciones
- Integridad referencial con foreign keys

### Soft Delete
Todas las entidades principales implementan Soft Delete:
- Los registros eliminados se pueden recuperar
- No se pierde el historial de operaciones
- Mantiene integridad referencial

### Relaciones Polimórficas
El sistema usa **relaciones polimórficas** para documentos adjuntos:
- Una entidad (factura, CUR, presupuesto, etc.) puede tener **múltiples documentos**
- Tabla `documentos` con campos `documentable_type` y `documentable_id`
- Método `morphMany()` en modelos padre (`FacturaCab`, `Cur`, etc.)
- Método `morphTo()` en modelo `Documento`
- Permite adjuntar archivos PDF, Excel, imágenes, etc. a cualquier entidad

### Bitácora de Auditoría
Registro automático de todas las operaciones CRUD cuando hay sesión activa:
- Usuario que ejecuta la acción
- Tabla y registro afectado
- Tipo de operación (creado, modificado, eliminado, anulado, restaurado)
- Fecha y hora
- Descripción detallada

### Seguridad
- Autenticación basada en sesiones de Laravel
- Contraseñas hasheadas con MD5 (requerimiento del cliente)
- Rutas API excluidas de verificación CSRF
- Validación de entrada con FormRequests

## 🗄️ Base de Datos

### Tablas Principales

| Tabla | Descripción |
|-------|-------------|
| `roles` | Roles de usuario (Admin, Contador, Auditor) |
| `usuarios` | Usuarios del sistema |
| `bitacora` | Auditoría de operaciones |
| `renglones` | Renglones presupuestarios |
| `presupuesto_cab` | Presupuestos (cabecera) |
| `presupuesto_det` | Detalles de presupuesto |
| `movimiento_cab` | Movimientos (cabecera) |
| `movimiento_det` | Detalles de movimiento |
| `proveedores` | Proveedores |
| `factura_cab` | Facturas (cabecera) |
| `factura_det` | Detalles de factura |
| `intras` | Transferencias entre renglones |
| `cur` | Compromisos de pago |
| `documentos` | Documentos adjuntos polimórficos |

### Diagrama de Relaciones

```
// MÓDULO DE SEGURIDAD
roles (1) ──< usuarios (N)
usuarios (1) ──< bitacora (N)

// MÓDULO PRESUPUESTARIO
presupuesto_cab (1) ──< presupuesto_det (N) ──> renglones (1)
movimiento_cab (1) ──< movimiento_det (N) ──> renglones (1)

// MÓDULO DE PROVEEDORES
proveedores (1) ──< factura_cab (N)
factura_cab (1) ──< factura_det (N) ──> renglones (1)

// MÓDULO COMPLEMENTARIO
renglones (1) ──< cur (N) ──> proveedores (1)
renglones (1) ──< intras (N) [origen/destino]

// DOCUMENTOS POLIMÓRFICOS (Una entidad tiene MUCHOS documentos)
factura_cab (1) ──< documentos (N) [documentable_type='App\Models\FacturaCab']
cur (1) ──< documentos (N) [documentable_type='App\Models\Cur']
presupuesto_cab (1) ──< documentos (N) [documentable_type='App\Models\PresupuestoCab']
movimiento_cab (1) ──< documentos (N) [documentable_type='App\Models\MovimientoCab']
intra (1) ──< documentos (N) [documentable_type='App\Models\Intra']
```

## 🧪 Testing

### Prueba Manual
```bash
# Script de prueba automático
.\test-api.ps1
```

### Prueba con Postman
1. Importar colección `SAP_API_Collection.postman_collection.json`
2. Importar entorno `SAP_Local_Environment.postman_environment.json`
3. Ejecutar el folder "1. Autenticación" → Login
4. Probar otros endpoints

## 📖 Documentación Adicional

- [POSTMAN_GUIDE.md](POSTMAN_GUIDE.md) - Guía completa de uso de Postman
- [CORRECCION_DOCUMENTOS.md](CORRECCION_DOCUMENTOS.md) - Corrección de relaciones polimórficas (12/nov/2025)
- [README_SAP_PROYECTO.md](README_SAP_PROYECTO.md) - Documentación detallada del proyecto

## 🤝 Contribución

Este es un proyecto académico para la Universidad Mariano Gálvez de Guatemala.

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

## 👥 Autores

- **Fernando Camargo** - Universidad Mariano Gálvez de Guatemala

## 📞 Soporte

Para problemas o dudas:
1. Revisar logs en `storage/logs/laravel.log`
2. Verificar configuración de `.env`
3. Confirmar que las migraciones se ejecutaron correctamente

---

**Desarrollado con ❤️ para la Universidad Mariano Gálvez de Guatemala**


In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
