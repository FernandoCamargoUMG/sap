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

        // Verificar que no tenga presupuestos o movimientos asociados
        if ($renglon->presupuestosDetalle()->count() > 0 || $renglon->movimientosDetalle()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un renglón que tiene presupuestos o movimientos asociados'
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
            ->whereHas('presupuestosDetalle', function($query) {
                $query->whereRaw('monto_asignado > monto_ejecutado');
            })
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

    /**
     * Obtener saldo detallado de un renglón específico
     */
    public function saldo($id)
    {
        $renglon = Renglon::with(['presupuestosDetalle.presupuestoCab'])
            ->find($id);

        if (!$renglon) {
            return response()->json([
                'success' => false,
                'message' => 'Renglón no encontrado'
            ], 404);
        }

        $saldoDetalle = [
            'renglon' => [
                'id' => $renglon->id,
                'codigo' => $renglon->codigo,
                'nombre' => $renglon->nombre,
                'grupo' => $renglon->grupo,
                'descripcion' => $renglon->descripcion
            ],
            'resumen' => [
                'monto_asignado' => (float) $renglon->monto_asignado,
                'monto_ejecutado' => (float) $renglon->monto_ejecutado,
                'saldo_disponible' => (float) $renglon->saldo_disponible,
                'saldo_por_ejecutar' => (float) $renglon->saldo_por_ejecutar,
                'porcentaje_ejecutado' => $renglon->monto_asignado > 0 ? 
                    round(($renglon->monto_ejecutado / $renglon->monto_asignado) * 100, 2) : 0
            ],
            'presupuestos' => $renglon->presupuestosDetalle->map(function ($detalle) {
                return [
                    'presupuesto_id' => $detalle->presupuesto_id,
                    'presupuesto_anio' => $detalle->presupuestoCab->anio ?? null,
                    'presupuesto_mes' => $detalle->presupuestoCab->mes ?? null,
                    'monto_asignado' => (float) $detalle->monto_asignado,
                    'monto_ejecutado' => (float) $detalle->monto_ejecutado,
                    'saldo_por_ejecutar' => (float) $detalle->getSaldoPorEjecutar(),
                    'porcentaje_ejecucion' => round($detalle->getPorcentajeEjecucion(), 2)
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $saldoDetalle
        ], 200);
    }
}
