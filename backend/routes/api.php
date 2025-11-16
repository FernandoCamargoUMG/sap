<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RenglonController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\IntraController;
use App\Http\Controllers\CurController;
use App\Http\Controllers\DocumentoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas API del Sistema de Administración Presupuestaria (SAP)
|
*/

// Rutas públicas (sin autenticación)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
});

// Rutas protegidas (requieren sesión activa)
Route::middleware('web')->group(function () {
    
    // ========== AUTENTICACIÓN ==========
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('auth.me');
    });

    // ========== MÓDULO DE SEGURIDAD ==========
    
    // Usuarios CRUD
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/deleted/list', [UsuarioController::class, 'deleted'])->name('usuarios.deleted');
        Route::get('/{id}', [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::put('/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        Route::post('/{id}/restore', [UsuarioController::class, 'restore'])->name('usuarios.restore');
    });

    // ========== MÓDULO PRESUPUESTARIO ==========
    
    // Renglones presupuestarios
    Route::prefix('renglones')->group(function () {
        Route::get('/', [RenglonController::class, 'index'])->name('renglones.index');
        Route::post('/', [RenglonController::class, 'store'])->name('renglones.store');
        Route::get('/deleted/list', [RenglonController::class, 'deleted'])->name('renglones.deleted');
        Route::get('/{id}', [RenglonController::class, 'show'])->name('renglones.show');
        Route::get('/{id}/saldo', [RenglonController::class, 'saldo'])->name('renglones.saldo');
        Route::put('/{id}', [RenglonController::class, 'update'])->name('renglones.update');
        Route::delete('/{id}', [RenglonController::class, 'destroy'])->name('renglones.destroy');
        Route::post('/{id}/restore', [RenglonController::class, 'restore'])->name('renglones.restore');
    });

    // Presupuestos (cabecera y detalle)
    Route::prefix('presupuestos')->group(function () {
        Route::get('/', [PresupuestoController::class, 'index'])->name('presupuestos.index'); // Dashboard
        Route::get('/lista', [PresupuestoController::class, 'listarPresupuestos'])->name('presupuestos.lista'); // Lista individual
        Route::post('/', [PresupuestoController::class, 'store'])->name('presupuestos.store');
        Route::post('/ejecutar-gasto', [PresupuestoController::class, 'ejecutarGasto'])->name('presupuestos.ejecutar-gasto');
        Route::get('/disponibles', [PresupuestoController::class, 'getPresupuestosDisponibles'])->name('presupuestos.disponibles');
        Route::get('/deleted/list', [PresupuestoController::class, 'deleted'])->name('presupuestos.deleted');
        Route::get('/{id}', [PresupuestoController::class, 'show'])->name('presupuestos.show');
        Route::put('/{id}', [PresupuestoController::class, 'update'])->name('presupuestos.update');
        Route::delete('/{id}', [PresupuestoController::class, 'destroy'])->name('presupuestos.destroy');
        Route::post('/{id}/restore', [PresupuestoController::class, 'restore'])->name('presupuestos.restore');
    });

    // ========== MÓDULO DE MOVIMIENTOS ==========
    
    // Movimientos presupuestarios (nueva arquitectura)
    Route::prefix('movimientos')->group(function () {
        Route::get('/', [MovimientoController::class, 'index'])->name('movimientos.index');
        Route::post('/', [MovimientoController::class, 'store'])->name('movimientos.store');
        Route::get('/resumen', [MovimientoController::class, 'resumen'])->name('movimientos.resumen');
        Route::get('/{id}', [MovimientoController::class, 'show'])->name('movimientos.show');
        Route::put('/{id}', [MovimientoController::class, 'update'])->name('movimientos.update');
        Route::delete('/{id}', [MovimientoController::class, 'destroy'])->name('movimientos.destroy');
        Route::put('/{id}/anular', [MovimientoController::class, 'anular'])->name('movimientos.anular');
    });

    // ========== MÓDULO DE PROVEEDORES ==========
    
    // Proveedores
    Route::prefix('proveedores')->group(function () {
        Route::get('/', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::post('/', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::get('/deleted/list', [ProveedorController::class, 'deleted'])->name('proveedores.deleted');
        Route::get('/{id}', [ProveedorController::class, 'show'])->name('proveedores.show');
        Route::put('/{id}', [ProveedorController::class, 'update'])->name('proveedores.update');
        Route::delete('/{id}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');
        Route::post('/{id}/restore', [ProveedorController::class, 'restore'])->name('proveedores.restore');
    });

    // Facturas (cabecera y detalle)
    Route::prefix('facturas')->group(function () {
        Route::get('/', [FacturaController::class, 'index'])->name('facturas.index');
        Route::post('/', [FacturaController::class, 'store'])->name('facturas.store');
        Route::get('/reportes', [FacturaController::class, 'facturasParaReporte'])->name('facturas.reportes');
        Route::get('/proveedores', [FacturaController::class, 'getProveedores'])->name('facturas.proveedores');
        Route::get('/renglones', [FacturaController::class, 'getRenglones'])->name('facturas.renglones');
        Route::get('/{id}', [FacturaController::class, 'show'])->name('facturas.show');
        Route::get('/{id}/documento', [FacturaController::class, 'descargarDocumento'])->name('facturas.documento');
        Route::delete('/{id}/documento', [FacturaController::class, 'eliminarDocumento'])->name('facturas.eliminar-documento');
        Route::put('/{id}', [FacturaController::class, 'update'])->name('facturas.update');
        Route::delete('/{id}', [FacturaController::class, 'destroy'])->name('facturas.destroy');
    });

    // ========== MÓDULOS COMPLEMENTARIOS ==========
    
    // Transferencias entre renglones (INTRAS)
    Route::prefix('intras')->group(function () {
        Route::get('/', [IntraController::class, 'index'])->name('intras.index');
        Route::post('/', [IntraController::class, 'store'])->name('intras.store');
        Route::get('/renglones-disponibles', [IntraController::class, 'getRenglonesDisponibles'])->name('intras.renglones');
        Route::get('/{id}', [IntraController::class, 'show'])->name('intras.show');
        Route::post('/{id}/documento', [IntraController::class, 'uploadDocument'])->name('intras.uploadDocument');
        Route::get('/documento/{documentoId}', [IntraController::class, 'downloadDocument'])->name('intras.downloadDocument');
        Route::delete('/documento/{documentoId}', [IntraController::class, 'deleteDocument'])->name('intras.deleteDocument');
        Route::delete('/{id}', [IntraController::class, 'destroy'])->name('intras.destroy');
    });

    // Compromisos de pago (CUR)
    Route::prefix('cur')->group(function () {
        Route::get('/', [CurController::class, 'index'])->name('cur.index');
        Route::post('/', [CurController::class, 'store'])->name('cur.store');
        Route::get('/{id}', [CurController::class, 'show'])->name('cur.show');
        Route::put('/{id}', [CurController::class, 'update'])->name('cur.update');
        Route::get('/{id}/documento', [CurController::class, 'descargarDocumento'])->name('cur.documento');
        Route::post('/{id}/documento', [CurController::class, 'uploadDocument'])->name('cur.uploadDocument');
        Route::delete('/{id}/documento', [CurController::class, 'deleteDocument'])->name('cur.deleteDocument');
        Route::post('/{id}/documentos', [CurController::class, 'addDocuments'])->name('cur.addDocuments');
        Route::delete('/{id}', [CurController::class, 'destroy'])->name('cur.destroy');
    });

    // Documentos adjuntos
    Route::prefix('documentos')->group(function () {
        Route::get('/', [DocumentoController::class, 'index'])->name('documentos.index');
        Route::post('/', [DocumentoController::class, 'store'])->name('documentos.store');
        Route::get('/{id}', [DocumentoController::class, 'show'])->name('documentos.show');
        Route::get('/{id}/download', [DocumentoController::class, 'download'])->name('documentos.download');
        Route::put('/{id}', [DocumentoController::class, 'update'])->name('documentos.update');
        Route::delete('/{id}', [DocumentoController::class, 'destroy'])->name('documentos.destroy');
        Route::get('/{documentableType}/{documentableId}', [DocumentoController::class, 'byEntity'])->name('documentos.byEntity');
    });

    // ========== EJECUCIÓN DE PRESUPUESTOS ==========
    Route::prefix('ejecuciones')->group(function () {
        Route::post('/', [\App\Http\Controllers\EjecucionController::class, 'registrarEjecucion'])->name('ejecuciones.registrar');
        Route::get('/renglon/{renglonId}', [\App\Http\Controllers\EjecucionController::class, 'getEjecucionesPorRenglon'])->name('ejecuciones.porRenglon');
        Route::get('/presupuestos-disponibles/{renglonId}', [\App\Http\Controllers\EjecucionController::class, 'getPresupuestosDisponibles'])->name('ejecuciones.presupuestosDisponibles');
    });
});

