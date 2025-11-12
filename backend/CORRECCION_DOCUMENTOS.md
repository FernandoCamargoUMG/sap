# 🔧 Corrección del Módulo de Documentos Polimórficos

## 📋 Resumen de Cambios

Se corrigieron inconsistencias entre la migración, el modelo y el controlador del módulo de documentos para implementar correctamente las **relaciones polimórficas** de Laravel.

---

## ✅ Archivos Corregidos

### 1. **Migración: `2025_01_01_000014_create_documentos_table.php`**

#### ❌ Antes (Incorrecto)
```php
$table->string('ruta', 255);           // ❌ Nombre inconsistente
$table->string('tipo', 50);            // ❌ Nombre inconsistente
$table->foreignId('subido_por');       // ❌ Nombre inconsistente
$table->timestamp('fecha_subida');     // ❌ Campo innecesario (ya existe created_at)
// ❌ FALTABAN campos polimórficos
```

#### ✅ Después (Correcto)
```php
// Campos polimórficos de Laravel
$table->string('documentable_type', 50);
$table->unsignedBigInteger('documentable_id');

// Información del archivo
$table->string('nombre_archivo', 255);
$table->string('ruta_archivo', 500);
$table->string('tipo_archivo', 100)->nullable();
$table->bigInteger('tamanio')->nullable();
$table->text('descripcion')->nullable();

// Usuario y estado
$table->foreignId('usuario_id')->constrained('usuarios');
$table->tinyInteger('estado')->default(1);
$table->timestamps();
$table->softDeletes();

// Índice compuesto para búsquedas polimórficas
$table->index(['documentable_type', 'documentable_id'], 'idx_documentable');
```

---

### 2. **Modelo: `app/Models/Documento.php`**

#### ✅ Estado: YA ESTABA CORRECTO

El modelo ya usaba los nombres correctos y tenía `morphTo()` configurado:

```php
protected $fillable = [
    'documentable_type',
    'documentable_id',
    'nombre_archivo',
    'ruta_archivo',
    'tipo_archivo',
    'tamanio',
    'descripcion',
    'usuario_id',
    'estado'
];

public function documentable()
{
    return $this->morphTo();
}
```

---

### 3. **Controlador: `app/Http/Controllers/DocumentoController.php`**

#### ❌ Antes (Incorrecto)
```php
// Validaciones incorrectas
'entidad_tipo' => 'required|string|max:50',
'entidad_id' => 'required|integer',

// Creación incorrecta
'entidad_tipo' => $validated['entidad_tipo'],
'entidad_id' => $validated['entidad_id'],

// Filtros incorrectos
$request->has('entidad_tipo')
$request->has('entidad_id')

// Método byEntity con parámetros incorrectos
public function byEntity($entidadTipo, $entidadId)
```

#### ✅ Después (Correcto)
```php
// Validaciones correctas
'documentable_type' => 'required|string|max:50',
'documentable_id' => 'required|integer',

// Creación correcta
'documentable_type' => $validated['documentable_type'],
'documentable_id' => $validated['documentable_id'],

// Filtros correctos
$request->has('documentable_type')
$request->has('documentable_id')

// Método byEntity con parámetros correctos
public function byEntity($documentableType, $documentableId)
{
    $documentos = Documento::with('usuario')
        ->where('documentable_type', $documentableType)
        ->where('documentable_id', $documentableId)
        ->where('estado', 1)
        ->orderBy('created_at', 'desc')
        ->get();
}
```

---

### 4. **Modelos Padres: Agregar `morphMany`**

#### ✅ `app/Models/PresupuestoCab.php`

```php
/**
 * Relación polimórfica con documentos
 */
public function documentos()
{
    return $this->morphMany(Documento::class, 'documentable');
}
```

#### ✅ `app/Models/MovimientoCab.php`

```php
/**
 * Relación polimórfica con documentos
 */
public function documentos()
{
    return $this->morphMany(Documento::class, 'documentable');
}
```

**Nota:** Los siguientes modelos YA TENÍAN la relación:
- ✅ `FacturaCab`
- ✅ `Cur`
- ✅ `Intra`

---

### 5. **Rutas: `routes/api.php`**

#### ❌ Antes (Incorrecto)
```php
Route::get('/{entidadTipo}/{entidadId}', [DocumentoController::class, 'byEntity']);
```

#### ✅ Después (Correcto)
```php
Route::get('/{documentableType}/{documentableId}', [DocumentoController::class, 'byEntity']);
```

---

## 🔍 ¿Qué es una Relación Polimórfica?

Permite que la tabla `documentos` se relacione con **múltiples entidades diferentes** usando solo dos campos:

```
documentable_type = 'App\\Models\\FacturaCab'
documentable_id = 5
↓
Este documento pertenece a la Factura #5
```

```
documentable_type = 'App\\Models\\PresupuestoCab'
documentable_id = 12
↓
Este documento pertenece al Presupuesto #12
```

---

## 📊 Diagrama de Relaciones Correcto

```
PresupuestoCab (1) ──< documentos (N) [documentable_type='App\\Models\\PresupuestoCab']
MovimientoCab (1)  ──< documentos (N) [documentable_type='App\\Models\\MovimientoCab']
FacturaCab (1)     ──< documentos (N) [documentable_type='App\\Models\\FacturaCab']
Cur (1)            ──< documentos (N) [documentable_type='App\\Models\\Cur']
Intra (1)          ──< documentos (N) [documentable_type='App\\Models\\Intra']
```

---

## 🚀 Uso desde el Frontend

### **Crear un documento para una factura:**

```json
POST /api/documentos
{
  "documentable_type": "App\\Models\\FacturaCab",
  "documentable_id": 5,
  "nombre_archivo": "factura_123.pdf",
  "ruta_archivo": "documentos/facturas/factura_123.pdf",
  "tipo_archivo": "application/pdf",
  "tamanio": 245680,
  "descripcion": "Factura original del proveedor XYZ"
}
```

### **Obtener documentos de un presupuesto:**

```
GET /api/documentos/App%5CModels%5CPresupuestoCab/12
```

O usando filtros en el index:

```
GET /api/documentos?documentable_type=App\Models\PresupuestoCab&documentable_id=12
```

---

## 🧪 Cómo Usar desde Eloquent

### **Obtener documentos de una entidad:**

```php
$factura = FacturaCab::find(5);
$documentos = $factura->documentos; // Todos los documentos de esta factura

$presupuesto = PresupuestoCab::find(12);
$documentos = $presupuesto->documentos()->activos()->get(); // Solo activos
```

### **Obtener la entidad padre desde un documento:**

```php
$documento = Documento::find(1);
$padre = $documento->documentable; // Puede ser FacturaCab, Cur, PresupuestoCab, etc.

// Verificar el tipo
if ($documento->documentable_type === 'App\\Models\\FacturaCab') {
    // Es una factura
    $factura = $documento->documentable;
}
```

---

## ⚠️ Acciones Requeridas

### **1. Regenerar la base de datos**

Como cambiamos la estructura de la tabla `documentos`, debes:

```bash
# Opción 1: Refresh completo (BORRA TODOS LOS DATOS)
php artisan migrate:fresh --seed

# Opción 2: Rollback específico y re-migrar
php artisan migrate:rollback --step=1
php artisan migrate
```

### **2. Actualizar Postman Collection**

Cambiar en todas las peticiones de documentos:

```diff
- "entidad_tipo": "factura_cab"
- "entidad_id": 5
+ "documentable_type": "App\\Models\\FacturaCab"
+ "documentable_id": 5
```

### **3. Actualizar test-api.ps1**

Si el script de pruebas incluye documentos, cambiar los nombres de campos.

---

## ✅ Checklist de Verificación

- [x] Migración corregida con campos polimórficos
- [x] Modelo Documento con `morphTo()`
- [x] Controlador con validaciones correctas
- [x] PresupuestoCab con `morphMany()`
- [x] MovimientoCab con `morphMany()`
- [x] Rutas API actualizadas
- [ ] Base de datos regenerada
- [ ] Postman collection actualizada
- [ ] Pruebas de endpoints ejecutadas

---

## 🎯 Resultado Final

Ahora el sistema de documentos:

✅ Usa correctamente relaciones polimórficas de Laravel  
✅ Permite adjuntar documentos a cualquier entidad (Facturas, CUR, Presupuestos, Movimientos, Intras)  
✅ Mantiene consistencia entre migración, modelo y controlador  
✅ Soporta múltiples documentos por entidad  
✅ Optimizado con índices compuestos para búsquedas rápidas  

---

**Fecha de corrección:** 12 de noviembre de 2025  
**Archivos modificados:** 5  
**Estado:** ✅ COMPLETADO
