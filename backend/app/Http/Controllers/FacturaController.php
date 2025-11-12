<?php

namespace App\Http\Controllers;

use App\Models\FacturaCab;
use App\Models\FacturaDet;
use App\Models\Bitacora;
use App\Http\Requests\FacturaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    /**
     * Listar todas las facturas activas
     */
    public function index()
    {
        $facturas = FacturaCab::with(['proveedor', 'detalles.renglon'])
            ->activos()
            ->orderBy('fecha_factura', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $facturas
        ], 200);
    }

    /**
     * Crear nueva factura con sus detalles
     */
    public function store(FacturaRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $data = $request->validated();
            
            // Crear encabezado de factura
            $facturaCab = FacturaCab::create([
                'proveedor_id' => $data['proveedor_id'],
                'numero_factura' => $data['numero_factura'],
                'serie_factura' => $data['serie_factura'] ?? null,
                'fecha_factura' => $data['fecha_factura'],
                'fecha_recepcion' => $data['fecha_recepcion'] ?? now(),
                'descripcion' => $data['descripcion'] ?? null,
                'total' => $data['total'],
                'estado' => $data['estado'] ?? 1
            ]);

            // Crear detalles de factura
            if (isset($data['detalles']) && is_array($data['detalles'])) {
                foreach ($data['detalles'] as $detalle) {
                    FacturaDet::create([
                        'factura_cab_id' => $facturaCab->id,
                        'renglon_id' => $detalle['renglon_id'],
                        'descripcion' => $detalle['descripcion'],
                        'cantidad' => $detalle['cantidad'] ?? 1,
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $detalle['subtotal'],
                        'estado' => $detalle['estado'] ?? 1
                    ]);
                }
            }

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'factura_cab',
                    $facturaCab->id,
                    'creado',
                    session('usuario_id'),
                    "Factura {$facturaCab->numero_factura} creada"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura creada exitosamente',
                'data' => $facturaCab->load(['proveedor', 'detalles.renglon'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una factura específica con sus detalles
     */
    public function show($id)
    {
        $factura = FacturaCab::with(['proveedor', 'detalles.renglon'])->find($id);

        if (!$factura) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $factura
        ], 200);
    }

    /**
     * Actualizar factura
     */
    public function update(FacturaRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $facturaCab = FacturaCab::find($id);

            if (!$facturaCab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            $data = $request->validated();
            
            // Actualizar encabezado
            $facturaCab->update([
                'proveedor_id' => $data['proveedor_id'],
                'numero_factura' => $data['numero_factura'],
                'serie_factura' => $data['serie_factura'] ?? null,
                'fecha_factura' => $data['fecha_factura'],
                'fecha_recepcion' => $data['fecha_recepcion'] ?? $facturaCab->fecha_recepcion,
                'descripcion' => $data['descripcion'] ?? null,
                'total' => $data['total'],
                'estado' => $data['estado'] ?? $facturaCab->estado
            ]);

            // Si se envían detalles, eliminar los anteriores y crear nuevos
            if (isset($data['detalles']) && is_array($data['detalles'])) {
                // Eliminar detalles anteriores
                FacturaDet::where('factura_cab_id', $facturaCab->id)->delete();
                
                // Crear nuevos detalles
                foreach ($data['detalles'] as $detalle) {
                    FacturaDet::create([
                        'factura_cab_id' => $facturaCab->id,
                        'renglon_id' => $detalle['renglon_id'],
                        'descripcion' => $detalle['descripcion'],
                        'cantidad' => $detalle['cantidad'] ?? 1,
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => $detalle['subtotal'],
                        'estado' => $detalle['estado'] ?? 1
                    ]);
                }
            }

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'factura_cab',
                    $facturaCab->id,
                    'modificado',
                    session('usuario_id'),
                    "Factura {$facturaCab->numero_factura} actualizada"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura actualizada exitosamente',
                'data' => $facturaCab->load(['proveedor', 'detalles.renglon'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar factura (soft delete)
     */
    public function destroy($id)
    {
        $facturaCab = FacturaCab::find($id);

        if (!$facturaCab) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        }

        $facturaCab->estado = 0;
        $facturaCab->save();
        $facturaCab->delete();

        // También marcar detalles como eliminados
        FacturaDet::where('factura_cab_id', $facturaCab->id)
            ->update(['estado' => 0]);

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'factura_cab',
                $facturaCab->id,
                'eliminado',
                session('usuario_id'),
                "Factura {$facturaCab->numero_factura} eliminada"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Factura eliminada exitosamente'
        ], 200);
    }

    /**
     * Restaurar factura eliminada
     */
    public function restore($id)
    {
        $facturaCab = FacturaCab::withTrashed()->find($id);

        if (!$facturaCab) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        }

        $facturaCab->restore();
        $facturaCab->estado = 1;
        $facturaCab->save();

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'factura_cab',
                $facturaCab->id,
                'restaurado',
                session('usuario_id'),
                "Factura {$facturaCab->numero_factura} restaurada"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Factura restaurada exitosamente',
            'data' => $facturaCab->load(['proveedor', 'detalles.renglon'])
        ], 200);
    }

    /**
     * Listar facturas eliminadas
     */
    public function deleted()
    {
        $facturas = FacturaCab::onlyTrashed()
            ->with(['proveedor', 'detalles.renglon'])
            ->orderBy('fecha_factura', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $facturas
        ], 200);
    }
}
