<?php

namespace App\Http\Controllers;

use App\Models\Renglon;
use App\Models\Bitacora;
use App\Http\Requests\RenglonRequest;
use Illuminate\Http\Request;

class RenglonController extends Controller
{
    /**
     * Listar todos los renglones activos
     */
    public function index()
    {
        $renglones = Renglon::activos()
            ->orderBy('codigo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $renglones
        ], 200);
    }

    /**
     * Crear nuevo renglón
     */
    public function store(RenglonRequest $request)
    {
        $data = $request->validated();
        
        // El saldo actual inicia igual al monto inicial
        $data['saldo_actual'] = $data['monto_inicial'];

        $renglon = Renglon::create($data);

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'renglones',
                $renglon->id,
                'creado',
                session('usuario_id'),
                "Renglón {$renglon->codigo} - {$renglon->nombre} creado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Renglón creado exitosamente',
            'data' => $renglon
        ], 201);
    }

    /**
     * Mostrar un renglón específico
     */
    public function show($id)
    {
        $renglon = Renglon::with(['presupuestosDetalle', 'movimientosDetalle', 'facturasDetalle'])->find($id);

        if (!$renglon) {
            return response()->json([
                'success' => false,
                'message' => 'Renglón no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $renglon
        ], 200);
    }

    /**
     * Actualizar renglón
     */
    public function update(RenglonRequest $request, $id)
    {
        $renglon = Renglon::find($id);

        if (!$renglon) {
            return response()->json([
                'success' => false,
                'message' => 'Renglón no encontrado'
            ], 404);
        }

        $renglon->update($request->validated());
        
        // El saldo_actual se mantendrá o se calculará desde presupuestos_det
        // No llamamos actualizarSaldo() aquí porque no recalculamos en cada update

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'renglones',
                $renglon->id,
                'modificado',
                session('usuario_id'),
                "Renglón {$renglon->codigo} actualizado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Renglón actualizado exitosamente',
            'data' => $renglon
        ], 200);
    }

    /**
     * Eliminar renglón (soft delete)
     */
    public function destroy($id)
    {
        $renglon = Renglon::find($id);

        if (!$renglon) {
            return response()->json([
                'success' => false,
                'message' => 'Renglón no encontrado'
            ], 404);
        }

        // Verificar que no tenga movimientos activos
        if ($renglon->monto_comprometido > 0 || $renglon->monto_ejecutado > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un renglón con movimientos activos'
            ], 400);
        }

        $renglon->estado = 0;
        $renglon->save();
        $renglon->delete();

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'renglones',
                $renglon->id,
                'eliminado',
                session('usuario_id'),
                "Renglón {$renglon->codigo} eliminado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Renglón eliminado exitosamente'
        ], 200);
    }

    /**
     * Listar renglones con saldo disponible
     */
    public function conSaldo()
    {
        $renglones = Renglon::activos()
            ->conSaldo()
            ->orderBy('codigo')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $renglones
        ], 200);
    }

    /**
     * Restaurar renglón eliminado
     */
    public function restore($id)
    {
        $renglon = Renglon::withTrashed()->find($id);

        if (!$renglon) {
            return response()->json([
                'success' => false,
                'message' => 'Renglón no encontrado'
            ], 404);
        }

        $renglon->restore();
        $renglon->estado = 1;
        $renglon->save();

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'renglones',
                $renglon->id,
                'restaurado',
                session('usuario_id'),
                "Renglón {$renglon->codigo} restaurado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Renglón restaurado exitosamente',
            'data' => $renglon
        ], 200);
    }
}
