<?php

namespace App\Http\Controllers;

use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use App\Models\Renglon;
use App\Models\MovimientoCab;
use App\Models\MovimientoDet;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresupuestoController extends Controller
{
    /**
     * Dashboard principal - Vista del sistema presupuestario
     * Nueva arquitectura: cálculos desde movimientos
     */
    public function index(Request $request)
    {
        try {
            $anio = $request->get('anio', date('Y'));
            $mes = $request->get('mes'); // null = anual, número = mensual específico
            
            // Obtener todos los renglones con sus cálculos desde la nueva arquitectura
            $renglones = Renglon::where('estado', 1)
                ->with([
                    'presupuestosDetalle' => function($query) use ($anio, $mes) {
                        $query->whereHas('presupuestoCab', function($q) use ($anio, $mes) {
                            $q->where('anio', $anio);
                            if ($mes) {
                                $q->where('mes', $mes);
                            }
                        });
                    },
                    'presupuestosDetalle.movimientos' => function($query) {
                        $query->with('movimiento');
                    }
                ])
                ->get()
                ->map(function($renglon) use ($anio, $mes) {
                    // Cálculos por renglón usando la nueva arquitectura
                    $montoPresupuestado = $renglon->presupuestosDetalle->sum('monto_asignado');
                    
                    // Calcular ejecutado desde movimientos
                    $montoEjecutado = $renglon->presupuestosDetalle->sum(function($detalle) {
                        return $detalle->movimientos->sum('monto');
                    });
                    
                    $montoPendiente = $montoPresupuestado - $montoEjecutado;
                    $variacion = $montoEjecutado - $montoPresupuestado;
                    
                    // Porcentajes
                    $porcentajeEjecutado = $montoPresupuestado > 0 
                        ? round(($montoEjecutado / $montoPresupuestado) * 100, 2) 
                        : 0;
                    $porcentajePendiente = 100 - $porcentajeEjecutado;
                    
                    return [
                        'id' => $renglon->id,
                        'codigo' => $renglon->codigo,
                        'nombre' => $renglon->nombre,
                        'descripcion' => $renglon->descripcion,
                        'grupo' => $renglon->grupo,
                        
                        // Montos (nueva arquitectura)
                        'monto_presupuestado' => $montoPresupuestado,
                        'monto_ejecutado' => $montoEjecutado,
                        'monto_pendiente' => $montoPendiente,
                        'variacion' => $variacion,
                        
                        // Porcentajes
                        'porcentaje_ejecutado' => $porcentajeEjecutado,
                        'porcentaje_pendiente' => $porcentajePendiente,
                        
                        // Estado
                        'estado' => $renglon->estado
                    ];
                });

            // Resumen general
            $resumen = [
                'anio' => $anio,
                'mes' => $mes,
                'periodo' => $mes ? $this->getNombreMes($mes) . ' ' . $anio : 'Año ' . $anio,
                'total_presupuestado' => $renglones->sum('monto_presupuestado'),
                'total_ejecutado' => $renglones->sum('monto_ejecutado'),
                'total_pendiente' => $renglones->sum('monto_pendiente'),
                'total_variacion' => $renglones->sum('variacion'),
                'porcentaje_ejecutado' => $renglones->avg('porcentaje_ejecutado'),
                'cantidad_renglones' => $renglones->count()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'resumen' => $resumen,
                    'renglones' => $renglones
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear presupuesto mensual o asignar montos a renglones
     */
    public function store(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|min:2020|max:2030',
            'mes' => 'required|integer|min:1|max:12',
            'descripcion' => 'nullable|string|max:255',
            'renglones' => 'required|array|min:1',
            'renglones.*.renglon_id' => 'required|exists:renglones,id',
            'renglones.*.monto_asignado' => 'required|numeric|min:0.01',
            'renglones.*.descripcion' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            // Verificar si ya existe presupuesto para ese mes/año
            $existePresupuesto = PresupuestoCab::where('anio', $request->anio)
                ->where('mes', $request->mes)
                ->exists();

            if ($existePresupuesto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un presupuesto para ' . $this->getNombreMes($request->mes) . ' de ' . $request->anio
                ], 422);
            }

            // Crear presupuesto
            $presupuesto = PresupuestoCab::create([
                'anio' => $request->anio,
                'mes' => $request->mes,
                'descripcion' => $request->descripcion ?? 'Presupuesto ' . $this->getNombreMes($request->mes) . ' ' . $request->anio,
                'creado_por' => session('usuario_id', 1),
                'fecha_creacion' => now(),
                'estado' => 1
            ]);

            // Crear detalles del presupuesto con montos asignados
            foreach ($request->renglones as $renglonData) {
                PresupuestoDet::create([
                    'presupuesto_id' => $presupuesto->id,
                    'renglon_id' => $renglonData['renglon_id'],
                    'monto_asignado' => $renglonData['monto_asignado'],
                    'descripcion' => $renglonData['descripcion'] ?? null,
                    'estado' => 1
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto creado exitosamente',
                'data' => $presupuesto->load(['detalles.renglon'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un presupuesto específico
     */
    public function show($id)
    {
        try {
            $presupuesto = PresupuestoCab::with([
                'usuario', 
                'detalles.renglon',
                'detalles.movimientos' => function($query) {
                    $query->where('estado', 1)->orderBy('created_at', 'desc');
                }
            ])->find($id);

            if (!$presupuesto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Presupuesto no encontrado'
                ], 404);
            }

            // Agregar información calculada de movimientos para cada detalle
            $presupuesto->detalles->map(function($detalle) {
                $montoEjecutado = $detalle->movimientos->sum('monto');
                $detalle->monto_ejecutado = $montoEjecutado;
                $detalle->saldo_disponible = $detalle->monto_asignado - $montoEjecutado;
                
                // Formatear movimientos para la UI
                $detalle->movimientos->map(function($movimientoDet) {
                    $movimientoDet->movimiento_cab = $movimientoDet->movimiento;
                    return $movimientoDet;
                });
                
                return $detalle;
            });

            return response()->json([
                'success' => true,
                'data' => $presupuesto
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener presupuesto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar presupuesto
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'anio' => 'required|integer|min:2020|max:2030',
            'mes' => 'required|integer|min:1|max:12',
            'descripcion' => 'nullable|string|max:255',
            'renglones' => 'nullable|array',
            'renglones.*.renglon_id' => 'required_with:renglones|exists:renglones,id',
            'renglones.*.monto_asignado' => 'required_with:renglones|numeric|min:0.01',
            'renglones.*.descripcion' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        
        try {
            $presupuesto = PresupuestoCab::find($id);

            if (!$presupuesto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Presupuesto no encontrado'
                ], 404);
            }
            
            // Actualizar encabezado
            $presupuesto->update([
                'anio' => $request->anio,
                'mes' => $request->mes ?? $presupuesto->mes,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado ?? $presupuesto->estado
            ]);

            // Actualizar detalles si se enviaron
            if ($request->has('renglones')) {
                // Obtener detalles existentes
                $detallesExistentes = $presupuesto->detalles()->get()->keyBy('renglon_id');
                $renglonesEnviados = collect($request->renglones)->keyBy('renglon_id');

                // Actualizar o crear detalles
                foreach ($request->renglones as $detalle) {
                    $detalleExistente = $detallesExistentes->get($detalle['renglon_id']);
                    
                    if ($detalleExistente) {
                        // Actualizar existente manteniendo monto_ejecutado
                        $detalleExistente->update([
                            'monto_asignado' => $detalle['monto_asignado'] ?? 0,
                            'descripcion' => $detalle['descripcion'] ?? null,
                        ]);
                    } else {
                        // Crear nuevo detalle
                        PresupuestoDet::create([
                            'presupuesto_id' => $presupuesto->id,
                            'renglon_id' => $detalle['renglon_id'],
                            'monto_asignado' => $detalle['monto_asignado'] ?? 0,
                            'monto_ejecutado' => 0,
                            'descripcion' => $detalle['descripcion'] ?? null,
                            'estado' => 1
                        ]);
                    }
                }

                // Eliminar detalles que ya no están en la request (soft delete)
                $renglonesAEliminar = $detallesExistentes->keys()->diff($renglonesEnviados->keys());
                if ($renglonesAEliminar->isNotEmpty()) {
                    $presupuesto->detalles()
                        ->whereIn('renglon_id', $renglonesAEliminar)
                        ->update(['estado' => 0]); // Soft delete
                }
            }

            // Cargar relaciones actualizadas
            $presupuesto->load(['usuario', 'detalles.renglon']);

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'presupuesto_cab',
                    $presupuesto->id,
                    'modificado',
                    session('usuario_id'),
                    "Presupuesto {$presupuesto->anio}/{$presupuesto->mes} actualizado"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto actualizado exitosamente',
                'data' => $presupuesto
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar presupuesto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar presupuesto (soft delete)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $presupuesto = PresupuestoCab::find($id);

            if (!$presupuesto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Presupuesto no encontrado'
                ], 404);
            }

            // Eliminar detalles
            $presupuesto->detalles()->delete();

            // Eliminar encabezado
            $presupuesto->estado = 0;
            $presupuesto->save();
            $presupuesto->delete();

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'presupuesto_cab',
                    $presupuesto->id,
                    'eliminado',
                    session('usuario_id'),
                    "Presupuesto {$presupuesto->anio}/{$presupuesto->mes} eliminado"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar presupuesto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar presupuestos por año
     */
    public function porAnio($anio)
    {
        $presupuestos = PresupuestoCab::with(['usuario', 'detalles.renglon'])
            ->porAnio($anio)
            ->activos()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $presupuestos
        ], 200);
    }

    /**
     * Listar presupuestos eliminados
     */
    public function deleted()
    {
        $presupuestos = PresupuestoCab::onlyTrashed()
            ->with(['usuario', 'detalles.renglon'])
            ->orderByDesc('anio')
            ->orderByDesc('mes')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $presupuestos
        ], 200);
    }

    /**
     * Restaurar presupuesto eliminado
     */
    public function restore($id)
    {
        $presupuesto = PresupuestoCab::onlyTrashed()->find($id);

        if (!$presupuesto) {
            return response()->json([
                'success' => false,
                'message' => 'Presupuesto no encontrado en elementos eliminados'
            ], 404);
        }

        $presupuesto->restore();
        $presupuesto->estado = 1;
        $presupuesto->save();

        // Restaurar detalles
        $presupuesto->detalles()->onlyTrashed()->restore();

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'presupuesto_cab',
                $presupuesto->id,
                'restaurado',
                session('usuario_id'),
                "Presupuesto {$presupuesto->anio}/{$presupuesto->mes} restaurado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Presupuesto restaurado exitosamente',
            'data' => $presupuesto->load(['usuario', 'detalles.renglon'])
        ], 200);
    }

    /**
     * Ejecutar gasto - Registrar una ejecución presupuestaria
     * Nueva arquitectura: crear movimiento en lugar de actualizar monto_ejecutado
     */
    public function ejecutarGasto(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer',
            'mes' => 'required|integer|min:1|max:12',
            'renglon_id' => 'required|exists:renglones,id',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'required|string|max:255',
            'numero_documento' => 'nullable|string|max:50',
            'proveedor' => 'nullable|string|max:200'
        ]);

        DB::beginTransaction();
        try {
            // Buscar el presupuesto para ese año/mes
            $presupuestoCab = PresupuestoCab::where('anio', $request->anio)
                ->where('mes', $request->mes)
                ->first();

            if (!$presupuestoCab) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe presupuesto para ' . $this->getNombreMes($request->mes) . ' de ' . $request->anio
                ], 422);
            }

            // Buscar el detalle del presupuesto para ese renglón
            $detalle = PresupuestoDet::where('presupuesto_id', $presupuestoCab->id)
                ->where('renglon_id', $request->renglon_id)
                ->first();

            if (!$detalle) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay presupuesto asignado para este renglón en ' . $this->getNombreMes($request->mes) . ' de ' . $request->anio
                ], 422);
            }

            // Calcular saldo disponible desde movimientos existentes
            $montoEjecutado = $detalle->movimientos->sum('monto');
            $saldoDisponible = $detalle->monto_asignado - $montoEjecutado;
            
            if ($saldoDisponible < $request->monto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente. Disponible: Q' . number_format($saldoDisponible, 2)
                ], 422);
            }

            // Crear movimiento de ejecución presupuestaria
            $movimiento = MovimientoCab::create([
                'tipo_movimiento' => 'ejecucion_presupuestaria',
                'fecha' => now(),
                'descripcion' => $request->descripcion,
                'usuario_id' => session('usuario_id', 1),
                'presupuesto_cab_id' => $presupuestoCab->id,
                'numero_documento' => $request->numero_documento,
                'proveedor' => $request->proveedor,
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
                'message' => 'Gasto ejecutado exitosamente por Q' . number_format($request->monto, 2),
                'data' => [
                    'movimiento_id' => $movimiento->id,
                    'presupuesto' => [
                        'id' => $detalle->id,
                        'renglon' => $detalle->renglon->codigo . ' - ' . $detalle->renglon->nombre,
                        'monto_asignado' => $detalle->monto_asignado,
                        'monto_ejecutado' => $detalle->movimientos->sum('monto') + $request->monto,
                        'saldo_disponible' => $detalle->monto_asignado - ($detalle->movimientos->sum('monto') + $request->monto)
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar gasto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener presupuestos disponibles para ejecutar
     */
    public function getPresupuestosDisponibles(Request $request)
    {
        $anio = $request->get('anio', date('Y'));
        $renglonId = $request->get('renglon_id');

        $query = PresupuestoDet::with(['presupuestoCab', 'renglon'])
            ->whereHas('presupuestoCab', function($q) use ($anio) {
                $q->where('anio', $anio)->where('estado', 1);
            })
            ->whereRaw('monto_asignado > monto_ejecutado');

        if ($renglonId) {
            $query->where('renglon_id', $renglonId);
        }

        $presupuestos = $query->get()->map(function($detalle) {
            return [
                'id' => $detalle->id,
                'anio' => $detalle->presupuestoCab->anio,
                'mes' => $detalle->presupuestoCab->mes,
                'nombre_mes' => $this->getNombreMes($detalle->presupuestoCab->mes),
                'renglon_codigo' => $detalle->renglon->codigo,
                'renglon_nombre' => $detalle->renglon->nombre,
                'monto_asignado' => $detalle->monto_asignado,
                'monto_ejecutado' => $detalle->monto_ejecutado,
                'saldo_disponible' => $detalle->monto_asignado - $detalle->monto_ejecutado,
                'descripcion' => $detalle->descripcion
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $presupuestos
        ]);
    }

    /**
     * Listar todos los presupuestos individuales (no dashboard)
     * Nueva arquitectura: cálculos desde movimientos
     */
    public function listarPresupuestos(Request $request)
    {
        try {
            $anio = $request->get('anio');
            $mes = $request->get('mes');
            
            $query = PresupuestoCab::with(['detalles.renglon', 'detalles.movimientos'])
                ->where('estado', 1)
                ->orderBy('anio', 'desc')
                ->orderBy('mes', 'desc');
            
            if ($anio) {
                $query->where('anio', $anio);
            }
            
            if ($mes) {
                $query->where('mes', $mes);
            }
            
            $presupuestos = $query->get()->map(function($presupuesto) {
                // Calcular totales del presupuesto usando nueva arquitectura
                $totalPresupuestado = $presupuesto->detalles->sum('monto_asignado');
                
                // Calcular ejecutado desde movimientos
                $totalEjecutado = $presupuesto->detalles->sum(function($detalle) {
                    return $detalle->movimientos->sum('monto');
                });
                
                $totalPendiente = $totalPresupuestado - $totalEjecutado;
                $porcentajeEjecutado = $totalPresupuestado > 0 
                    ? round(($totalEjecutado / $totalPresupuestado) * 100, 2) 
                    : 0;
                
                return [
                    'id' => $presupuesto->id,
                    'anio' => $presupuesto->anio,
                    'mes' => $presupuesto->mes,
                    'descripcion' => $presupuesto->descripcion,
                    'total_presupuestado' => $totalPresupuestado,
                    'total_ejecutado' => $totalEjecutado,
                    'total_pendiente' => $totalPendiente,
                    'total_variacion' => $totalEjecutado - $totalPresupuestado,
                    'porcentaje_ejecutado' => $porcentajeEjecutado,
                    'porcentaje_pendiente' => 100 - $porcentajeEjecutado,
                    'cantidad_renglones' => $presupuesto->detalles->count(),
                    'fecha_creacion' => $presupuesto->fecha_creacion,
                    'estado' => $presupuesto->estado,
                    'detalles' => $presupuesto->detalles->map(function($detalle) {
                        $montoEjecutado = $detalle->movimientos->sum('monto');
                        return [
                            'id' => $detalle->id,
                            'renglon_id' => $detalle->renglon_id,
                            'monto_asignado' => $detalle->monto_asignado,
                            'monto_ejecutado' => $montoEjecutado,
                            'saldo_disponible' => $detalle->monto_asignado - $montoEjecutado,
                            'descripcion' => $detalle->descripcion,
                            'renglon' => [
                                'id' => $detalle->renglon->id,
                                'codigo' => $detalle->renglon->codigo,
                                'nombre' => $detalle->renglon->nombre
                            ]
                        ];
                    })
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $presupuestos
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar presupuestos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper para obtener nombre del mes en español
     */
    private function getNombreMes($numeroMes)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        return $meses[$numeroMes] ?? 'Mes ' . $numeroMes;
    }
}
