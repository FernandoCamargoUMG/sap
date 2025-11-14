<?php

namespace App\Http\Controllers;

use App\Http\Requests\EjecucionRequest;
use App\Models\PresupuestoDet;
use App\Models\Renglon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EjecucionController extends Controller
{
    /**
     * Registrar una ejecución de presupuesto
     */
    public function registrarEjecucion(EjecucionRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $data = $request->validated();
            
            // Buscar el detalle de presupuesto
            $presupuestoDet = PresupuestoDet::with(['renglon', 'presupuestoCab'])
                ->findOrFail($data['presupuesto_det_id']);
            
            // Verificar que hay saldo disponible para ejecutar
            $saldoPorEjecutar = $presupuestoDet->getSaldoPorEjecutar();
            if ($data['monto'] > $saldoPorEjecutar) {
                return response()->json([
                    'success' => false,
                    'message' => 'El monto a ejecutar excede el saldo disponible',
                    'data' => [
                        'saldo_disponible' => $saldoPorEjecutar,
                        'monto_solicitado' => $data['monto']
                    ]
                ], 422);
            }
            
            // Ejecutar el monto
            $presupuestoDet->ejecutarMonto($data['monto'], $data['descripcion'] ?? '');
            
            // Crear registro en bitácora si es necesario
            if (class_exists('App\Models\Bitacora')) {
                \App\Models\Bitacora::create([
                    'tabla' => 'presupuesto_det',
                    'registro_id' => $presupuestoDet->id,
                    'accion' => 'ejecucion',
                    'datos_anteriores' => json_encode([
                        'monto_ejecutado_anterior' => $presupuestoDet->monto_ejecutado - $data['monto']
                    ]),
                    'datos_nuevos' => json_encode([
                        'monto_ejecutado' => $presupuestoDet->monto_ejecutado,
                        'monto_ejecutado_nuevo' => $data['monto'],
                        'descripcion' => $data['descripcion'] ?? ''
                    ]),
                    'usuario_id' => auth()->id(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Ejecución registrada exitosamente',
                'data' => [
                    'presupuesto_det' => [
                        'id' => $presupuestoDet->id,
                        'monto_asignado' => $presupuestoDet->monto_asignado,
                        'monto_ejecutado' => $presupuestoDet->monto_ejecutado,
                        'saldo_por_ejecutar' => $presupuestoDet->getSaldoPorEjecutar(),
                        'porcentaje_ejecucion' => $presupuestoDet->getPorcentajeEjecucion()
                    ],
                    'renglon' => [
                        'id' => $presupuestoDet->renglon->id,
                        'saldo_actual' => $presupuestoDet->renglon->saldo_actual,
                        'monto_ejecutado_total' => $presupuestoDet->renglon->monto_ejecutado
                    ]
                ]
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la ejecución: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener las ejecuciones de un renglón
     */
    public function getEjecucionesPorRenglon($renglonId)
    {
        $renglon = Renglon::with([
            'presupuestosDetalle.presupuestoCab',
            'presupuestosDetalle' => function($query) {
                $query->where('monto_ejecutado', '>', 0);
            }
        ])->findOrFail($renglonId);
        
        $ejecuciones = $renglon->presupuestosDetalle->map(function($detalle) {
            return [
                'id' => $detalle->id,
                'presupuesto_id' => $detalle->presupuesto_id,
                'presupuesto_anio' => $detalle->presupuestoCab->anio ?? null,
                'presupuesto_mes' => $detalle->presupuestoCab->mes ?? null,
                'monto_asignado' => (float) $detalle->monto_asignado,
                'monto_ejecutado' => (float) $detalle->monto_ejecutado,
                'fecha_creacion' => $detalle->created_at,
                'fecha_actualizacion' => $detalle->updated_at
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'renglon' => [
                    'id' => $renglon->id,
                    'codigo' => $renglon->codigo,
                    'nombre' => $renglon->nombre,
                    'monto_inicial' => $renglon->monto_inicial,
                    'monto_ejecutado_total' => $renglon->monto_ejecutado
                ],
                'ejecuciones' => $ejecuciones,
                'resumen' => [
                    'total_ejecuciones' => $ejecuciones->count(),
                    'monto_total_ejecutado' => $ejecuciones->sum('monto_ejecutado')
                ]
            ]
        ], 200);
    }
    
    /**
     * Obtener los presupuestos disponibles para ejecutar de un renglón
     */
    public function getPresupuestosDisponibles($renglonId)
    {
        $presupuestos = PresupuestoDet::with(['presupuestoCab', 'renglon'])
            ->where('renglon_id', $renglonId)
            ->whereRaw('monto_asignado > monto_ejecutado')
            ->get()
            ->map(function($detalle) {
                return [
                    'id' => $detalle->id,
                    'presupuesto_id' => $detalle->presupuesto_id,
                    'presupuesto_anio' => $detalle->presupuestoCab->anio ?? null,
                    'presupuesto_mes' => $detalle->presupuestoCab->mes ?? null,
                    'monto_asignado' => (float) $detalle->monto_asignado,
                    'monto_ejecutado' => (float) $detalle->monto_ejecutado,
                    'saldo_por_ejecutar' => (float) $detalle->getSaldoPorEjecutar(),
                    'porcentaje_ejecucion' => round($detalle->getPorcentajeEjecucion(), 2)
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $presupuestos
        ], 200);
    }
}