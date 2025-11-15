<?php

namespace App\Http\Controllers;

use App\Models\MovimientoCab;
use App\Models\MovimientoDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    /**
     * Listar movimientos con filtros
     */
    public function index(Request $request)
    {
        try {
            $query = MovimientoCab::with(['detalles.renglon', 'presupuestoCab'])
                ->where('estado', 1)
                ->orderBy('fecha', 'desc');

            // Filtros básicos
            if ($request->has('anio')) {
                $query->whereYear('fecha', $request->anio);
            }

            if ($request->has('mes')) {
                $query->whereMonth('fecha', $request->mes);
            }

            $movimientos = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $movimientos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar movimientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo movimiento
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|in:ejecucion_presupuestaria,ajuste,traslado',
            'anio' => 'required|integer',
            'mes' => 'required|integer|min:1|max:12',
            'renglon_id' => 'required|exists:renglones,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:100'
        ]);

        DB::beginTransaction();
        try {
            // Buscar el presupuesto para ese año/mes
            $presupuestoCab = \App\Models\PresupuestoCab::where('anio', $request->anio)
                ->where('mes', $request->mes)
                ->first();

            if (!$presupuestoCab) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe presupuesto para el período especificado'
                ], 422);
            }

            // Buscar el detalle del presupuesto para ese renglón
            $detalle = \App\Models\PresupuestoDet::where('presupuesto_id', $presupuestoCab->id)
                ->where('renglon_id', $request->renglon_id)
                ->first();

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay presupuesto asignado para este renglón en el período especificado'
                ], 422);
            }

            // Para ejecución presupuestaria, validar saldo disponible
            if ($request->tipo === 'ejecucion_presupuestaria') {
                $montoEjecutado = $detalle->movimientos->sum('monto');
                $saldoDisponible = $detalle->monto_asignado - $montoEjecutado;
                
                if ($saldoDisponible < $request->monto) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Saldo insuficiente. Disponible: Q' . number_format($saldoDisponible, 2)
                    ], 422);
                }
            }

            // Crear movimiento
            $movimiento = MovimientoCab::create([
                'tipo_movimiento' => $request->tipo,
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
                'usuario_id' => session('usuario_id', 1),
                'presupuesto_cab_id' => $presupuestoCab->id,
                'numero_documento' => $request->referencia,
                'proveedor' => null,
                'estado' => 1
            ]);

            // Crear detalle del movimiento
            MovimientoDet::create([
                'movimiento_id' => $movimiento->id,
                'renglon_id' => $request->renglon_id,
                'presupuesto_det_id' => $detalle->id,
                'monto' => $request->monto,
                'descripcion_detalle' => $request->descripcion,
                'estado' => 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento creado exitosamente',
                'data' => $movimiento->load(['detalles.renglon'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un movimiento específico
     */
    public function show($id)
    {
        try {
            $movimiento = MovimientoCab::with(['detalles.renglon', 'presupuestoCab'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $movimiento
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Movimiento no encontrado'
            ], 404);
        }
    }

    /**
     * Actualizar un movimiento existente
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:255',
            'referencia' => 'nullable|string|max:100'
        ]);

        DB::beginTransaction();
        try {
            $movimiento = MovimientoCab::with(['detalles.presupuestoDet'])->findOrFail($id);
            
            // Obtener el detalle (asumimos un solo detalle por movimiento)
            $detalle = $movimiento->detalles->first();
            
            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el detalle del movimiento'
                ], 422);
            }

            // Si es ejecución presupuestaria y cambió el monto, validar saldo
            if ($movimiento->tipo_movimiento === 'ejecucion_presupuestaria' && $detalle->monto != $request->monto) {
                // Calcular saldo disponible sin contar este movimiento
                $otrosMovimientos = MovimientoDet::where('presupuesto_det_id', $detalle->presupuesto_det_id)
                    ->where('id', '!=', $detalle->id)
                    ->where('estado', 1)
                    ->sum('monto');
                
                $presupuestoDet = $detalle->presupuestoDet;
                $saldoDisponible = $presupuestoDet->monto_asignado - $otrosMovimientos;
                
                if ($saldoDisponible < $request->monto) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Saldo insuficiente. Disponible: Q' . number_format($saldoDisponible, 2)
                    ], 422);
                }
            }

            // Actualizar movimiento
            $movimiento->update([
                'fecha' => $request->fecha,
                'descripcion' => $request->descripcion,
                'numero_documento' => $request->referencia,
            ]);

            // Actualizar detalle
            $detalle->update([
                'monto' => $request->monto,
                'descripcion_detalle' => $request->descripcion,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento actualizado exitosamente',
                'data' => $movimiento->load(['detalles.renglon'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un movimiento (soft delete)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $movimiento = MovimientoCab::with(['detalles'])->findOrFail($id);

            // Marcar como eliminado (soft delete)
            $movimiento->update(['estado' => 0]);
            
            // También marcar los detalles como eliminados
            foreach ($movimiento->detalles as $detalle) {
                $detalle->update(['estado' => 0]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar movimiento: ' . $e->getMessage()
            ], 500);
        }
    }
}
