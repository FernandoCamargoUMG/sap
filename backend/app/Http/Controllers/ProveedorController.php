<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Bitacora;
use App\Http\Requests\ProveedorRequest;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Listar todos los proveedores activos
     */
    public function index()
    {
        $proveedores = Proveedor::activos()
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $proveedores
        ], 200);
    }

    /**
     * Crear nuevo proveedor
     */
    public function store(ProveedorRequest $request)
    {
        $proveedor = Proveedor::create($request->validated());

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'proveedores',
                $proveedor->id,
                'creado',
                session('usuario_id'),
                "Proveedor {$proveedor->nombre} creado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Proveedor creado exitosamente',
            'data' => $proveedor
        ], 201);
    }

    /**
     * Mostrar un proveedor específico
     */
    public function show($id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $proveedor
        ], 200);
    }

    /**
     * Actualizar proveedor
     */
    public function update(ProveedorRequest $request, $id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        }

        $proveedor->update($request->validated());

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'proveedores',
                $proveedor->id,
                'modificado',
                session('usuario_id'),
                "Proveedor {$proveedor->nombre} actualizado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Proveedor actualizado exitosamente',
            'data' => $proveedor
        ], 200);
    }

    /**
     * Eliminar proveedor (soft delete)
     */
    public function destroy($id)
    {
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        }

        $proveedor->estado = 0;
        $proveedor->save();
        $proveedor->delete();

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'proveedores',
                $proveedor->id,
                'eliminado',
                session('usuario_id'),
                "Proveedor {$proveedor->nombre} eliminado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Proveedor eliminado exitosamente'
        ], 200);
    }

    /**
     * Restaurar proveedor eliminado
     */
    public function restore($id)
    {
        $proveedor = Proveedor::withTrashed()->find($id);

        if (!$proveedor) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        }

        $proveedor->restore();
        $proveedor->estado = 1;
        $proveedor->save();

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'proveedores',
                $proveedor->id,
                'restaurado',
                session('usuario_id'),
                "Proveedor {$proveedor->nombre} restaurado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Proveedor restaurado exitosamente',
            'data' => $proveedor
        ], 200);
    }

    /**
     * Listar proveedores eliminados
     */
    public function deleted()
    {
        $proveedores = Proveedor::onlyTrashed()
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $proveedores
        ], 200);
    }
}
