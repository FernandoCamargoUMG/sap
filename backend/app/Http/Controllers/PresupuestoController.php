<?php

namespace App\Http\Controllers;

use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use App\Models\Bitacora;
use App\Http\Requests\PresupuestoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresupuestoController extends Controller
{
    /**
     * Listar todos los presupuestos activos
     */
    public function index()
    {
        $presupuestos = PresupuestoCab::with(['usuario', 'detalles.renglon'])
            ->activos()
            ->orderByDesc('anio')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $presupuestos
        ], 200);
    }

    /**
     * Crear nuevo presupuesto con detalles
     */
    public function store(PresupuestoRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $data = $request->validated();
            
            // Crear encabezado
            $presupuesto = PresupuestoCab::create([
                'anio' => $data['anio'],
                'descripcion' => $data['descripcion'],
                'fecha_aprobacion' => $data['fecha_aprobacion'],
                'monto_total' => 0,
                'usuario_id' => $data['usuario_id'],
                'estado' => $data['estado']
            ]);

            // Crear detalles si existen
            if (isset($data['detalles']) && is_array($data['detalles'])) {
                foreach ($data['detalles'] as $detalle) {
                    PresupuestoDet::create([
                        'presupuesto_cab_id' => $presupuesto->id,
                        'renglon_id' => $detalle['renglon_id'],
                        'monto' => $detalle['monto'],
                        'observaciones' => $detalle['observaciones'] ?? null,
                        'estado' => $detalle['estado']
                    ]);
                }
            }

            // Calcular monto total
            $presupuesto->calcularMontoTotal();
            $presupuesto->load(['detalles.renglon']);

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'presupuesto_cab',
                    $presupuesto->id,
                    'creado',
                    session('usuario_id'),
                    "Presupuesto {$presupuesto->anio} creado"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Presupuesto creado exitosamente',
                'data' => $presupuesto
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear presupuesto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un presupuesto específico
     */
    public function show($id)
    {
        $presupuesto = PresupuestoCab::with(['usuario', 'detalles.renglon'])->find($id);

        if (!$presupuesto) {
            return response()->json([
                'success' => false,
                'message' => 'Presupuesto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $presupuesto
        ], 200);
    }

    /**
     * Actualizar presupuesto
     */
    public function update(PresupuestoRequest $request, $id)
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

            $data = $request->validated();
            
            // Actualizar encabezado
            $presupuesto->update([
                'anio' => $data['anio'],
                'descripcion' => $data['descripcion'],
                'fecha_aprobacion' => $data['fecha_aprobacion'],
                'usuario_id' => $data['usuario_id'],
                'estado' => $data['estado']
            ]);

            // Actualizar detalles si se enviaron
            if (isset($data['detalles'])) {
                // Eliminar detalles actuales
                $presupuesto->detalles()->delete();

                // Crear nuevos detalles
                foreach ($data['detalles'] as $detalle) {
                    PresupuestoDet::create([
                        'presupuesto_cab_id' => $presupuesto->id,
                        'renglon_id' => $detalle['renglon_id'],
                        'monto' => $detalle['monto'],
                        'observaciones' => $detalle['observaciones'] ?? null,
                        'estado' => $detalle['estado']
                    ]);
                }
            }

            // Recalcular monto total
            $presupuesto->calcularMontoTotal();
            $presupuesto->load(['detalles.renglon']);

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'presupuesto_cab',
                    $presupuesto->id,
                    'modificado',
                    session('usuario_id'),
                    "Presupuesto {$presupuesto->anio} actualizado"
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
                    "Presupuesto {$presupuesto->anio} eliminado"
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
}
